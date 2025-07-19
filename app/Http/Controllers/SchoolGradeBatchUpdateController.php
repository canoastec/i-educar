<?php

namespace App\Http\Controllers;

use App\Models\LegacyGrade;
use App\Models\LegacySchool;
use App\Process;
use App\Services\SchoolGradeImportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchoolGradeBatchUpdateController extends Controller
{
    protected SchoolGradeImportService $service;

    public function __construct(SchoolGradeImportService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): View
    {
        $this->breadcrumb('Atualização de séries da escola em lote', [
            url('intranet/educar_configuracoes_index.php') => 'Configurações',
        ]);

        $this->menu(Process::SCHOOL_GRADE);

        return view('school-grade.batch-update.index', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request)
    {
        try {
            $this->cleanRequestData($request);
            $this->validateRequest($request);

            $year = $request->get('ano');
            $schools = $request->get('escola');
            $grades = $request->get('ref_cod_serie');
            $bloquearEnturmacao = $request->get('bloquear_enturmacao_sem_vagas', 0);
            $bloquearCadastro = $request->get('bloquear_cadastro_turma_para_serie_com_vagas', 0);

            $schoolsData = LegacySchool::whereIn('cod_escola', $schools)
                ->where('ativo', 1)
                ->with(['person', 'organization'])
                ->orderBy('cod_escola')
                ->get()
                ->sortBy('name');

            $gradesData = LegacyGrade::whereIn('cod_serie', $grades)
                ->where('ativo', 1)
                ->with(['course' => function ($query) {
                    $query->orderBy('nm_curso');
                }])
                ->orderBy('nm_serie')
                ->orderBy('ref_cod_curso')
                ->get(['cod_serie', 'nm_serie', 'ref_cod_curso']);

            $gradesByCourse = $gradesData->groupBy('ref_cod_curso')->map(function ($grades) {
                return $grades->sortBy('nm_serie');
            })->sortKeys();

            $tableData = collect();
            foreach ($schoolsData as $school) {
                $schoolRow = [
                    'school' => [
                        'id' => $school->cod_escola,
                        'name' => strtoupper($school->name ?? 'Escola não encontrada'),
                    ],
                    'courses' => [],
                ];

                foreach ($gradesByCourse as $courseId => $grades) {
                    $course = $grades->first()->course;
                    $seriesNames = $grades->pluck('nm_serie')->implode('<br>');

                    $schoolRow['courses'][] = [
                        'course_name' => $course->nm_curso ?? 'Curso não encontrado',
                        'series' => $seriesNames,
                    ];
                }

                $tableData->push($schoolRow);
            }

            $totalCombinations = count($schools) * count($grades);

            $previewData = [
                'year' => $year,
                'total_combinations' => $totalCombinations,
                'table_data' => $tableData->toArray(),
                'blocking_params' => [
                    'bloquear_enturmacao_sem_vagas' => $bloquearEnturmacao,
                    'bloquear_cadastro_turma_para_serie_com_vagas' => $bloquearCadastro,
                ],
            ];

            return response()->json([
                'status' => 'success',
                'preview' => $previewData,
            ]);

        } catch (\Exception $e) {
            $errorMessage = 'Erro ao processar atualização: ' . $e->getMessage();
            session()->flash('error', $errorMessage);

            return response()->json([
                'status' => 'error',
                'message' => $errorMessage,
                'errors' => [['error' => $errorMessage]],
            ]);
        }
    }

    public function process(Request $request)
    {
        try {
            $this->cleanRequestData($request);
            $this->validateRequest($request);

            $params = [
                'year' => $request->get('ano'),
                'schools' => $request->get('escola'),
                'grades' => $request->get('ref_cod_serie'),
                'user' => $request->user(),
                'bloquear_enturmacao_sem_vagas' => $request->get('bloquear_enturmacao_sem_vagas', 0),
                'bloquear_cadastro_turma_para_serie_com_vagas' => $request->get('bloquear_cadastro_turma_para_serie_com_vagas', 0),
            ];

            $result = $this->service->processBatchUpdate($params);

            $errors = collect($result['errors'] ?? [])->unique('error')->values()->all();
            $details = $result['details'] ?? [];

            if ($result['status'] === 'failed') {
                return response()->json([
                    'status' => 'error',
                    'message' => $result['message'],
                    'errors' => $errors,
                    'details' => $details,
                ]);
            } else {
                session()->flash('batch_update_result', $result);

                return response()->json([
                    'status' => 'success',
                    'message' => $result['message'],
                    'redirect' => route('school-grade.batch-update.status'),
                ]);
            }
        } catch (\Exception $e) {
            error_log('Erro ao processar atualização em lote de séries: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao processar atualização. Por favor, tente novamente.',
                'errors' => [['error' => 'Erro ao processar atualização. Por favor, tente novamente.']],
            ]);
        }
    }

    public function status(Request $request)
    {
        $result = session('batch_update_result', []);

        session()->forget('batch_update_result');

        $this->breadcrumb('Resultado da Atualização em Lote', [
            url('intranet/educar_configuracoes_index.php') => 'Configurações',
            route('school-grade.batch-update.index') => 'Atualização de séries da escola em lote',
        ]);

        $this->menu(Process::SCHOOL_GRADE);

        return view('school-grade.batch-update.status', ['result' => $result]);
    }

    private function cleanRequestData(Request $request): void
    {
        $cleanedData = collect($request->all())->map(function ($value, $key) {
            if (in_array($key, ['escola', 'ref_cod_serie'])) {
                return collect($value)
                    ->flatten(1)
                    ->filter()
                    ->map(fn ($item) => is_array($item) ? (int) $item[0] : (int) $item)
                    ->values()
                    ->toArray();
            }

            return $value;
        });

        $request->merge($cleanedData->toArray());
    }

    private function validateRequest(Request $request): void
    {
        $request->validate([
            'ano' => 'required|integer|min:2000|max:2100',
            'escola' => 'required|array|min:1',
            'escola.*' => 'integer|exists:escola,cod_escola',
            'ref_cod_serie' => 'required|array|min:1',
            'ref_cod_serie.*' => 'integer|exists:serie,cod_serie',
            'bloquear_enturmacao_sem_vagas' => 'nullable|boolean',
            'bloquear_cadastro_turma_para_serie_com_vagas' => 'nullable|boolean',
        ]);
    }
}
