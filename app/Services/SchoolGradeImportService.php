<?php

namespace App\Services;

use App\Models\LegacyDisciplineAcademicYear;
use App\Models\LegacyGrade;
use App\Models\LegacySchool;
use App\Models\LegacySchoolAcademicYear;
use App\Models\LegacySchoolCourse;
use App\Models\LegacySchoolGrade;
use App\Models\LegacySchoolGradeDiscipline;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SchoolGradeImportService
{
    private const STATUS_COMPLETED = 'completed';

    private const STATUS_FAILED = 'failed';

    private Collection $schools;

    private Collection $grades;

    private Collection $schoolAcademicYears;

    private Collection $schoolCourses;

    public function processBatchUpdate(array $params): array
    {
        $validationResult = $this->validateParams($params);
        if (!$validationResult['valid']) {
            return [
                'status' => self::STATUS_FAILED,
                'total' => 0,
                'processed' => 0,
                'errors' => [['error' => $validationResult['message']]],
                'details' => [],
                'message' => $validationResult['message'],
            ];
        }

        $total = count($params['schools']) * count($params['grades']);

        $this->loadCollections($params);

        $validationResult = $this->validateAll($params);
        if (!empty($validationResult['errors'])) {

            return [
                'status' => self::STATUS_FAILED,
                'total' => $total,
                'processed' => 0,
                'errors' => $validationResult['errors'],
                'details' => [],
                'message' => 'Atualização falhou devido a erros encontrados.',
            ];
        }

        return $this->processValidData($validationResult['validatedData'], $params, $total);
    }

    private function validateParams(array $params): array
    {
        if (empty($params['schools']) || empty($params['grades']) || empty($params['year'])) {
            return [
                'valid' => false,
                'message' => 'Parâmetros de escola, série e ano letivo são obrigatórios.',
            ];
        }

        if (!is_array($params['schools']) || !is_array($params['grades'])) {
            return [
                'valid' => false,
                'message' => 'Parâmetros de escola e série devem ser arrays.',
            ];
        }

        if (!is_numeric($params['year']) || (int) $params['year'] != $params['year']) {
            return [
                'valid' => false,
                'message' => 'Ano letivo deve ser um número inteiro.',
            ];
        }

        return [
            'valid' => true,
            'message' => 'Parâmetros válidos.',
        ];
    }

    private function loadCollections(array $params): void
    {
        $this->schools = LegacySchool::whereIn('cod_escola', $params['schools'])
            ->where('ativo', 1)
            ->get()
            ->keyBy('cod_escola');

        $this->grades = LegacyGrade::whereIn('cod_serie', $params['grades'])
            ->where('ativo', 1)
            ->select('cod_serie', 'nm_serie')
            ->get()
            ->keyBy('cod_serie');

        $this->schoolAcademicYears = LegacySchoolAcademicYear::whereIn('ref_cod_escola', $params['schools'])
            ->where('ano', $params['year'])
            ->where('ativo', 1)
            ->select('ref_cod_escola', 'andamento')
            ->get()
            ->keyBy('ref_cod_escola');

        $this->schoolCourses = LegacySchoolCourse::whereIn('ref_cod_escola', $params['schools'])
            ->whereRaw('? = ANY(anos_letivos)', [$params['year']])
            ->where('ativo', 1)
            ->select('ref_cod_escola')
            ->get()
            ->keyBy('ref_cod_escola');
    }

    private function validateAll(array $params): array
    {
        $validatedData = [];
        $errors = [];

        foreach ($params['schools'] as $escolaId) {
            foreach ($params['grades'] as $serieId) {
                $validationResult = $this->validateSchoolGradeCombinationFromCollections(
                    $escolaId,
                    $serieId,
                    $params['year']
                );

                if ($validationResult['success']) {
                    $validatedData[] = $validationResult['data'];
                } else {
                    $errors[] = [
                        'school_id' => $escolaId,
                        'grade_id' => $serieId,
                        'error' => $validationResult['error'],
                    ];
                }
            }
        }

        return [
            'validatedData' => $validatedData,
            'errors' => $errors,
        ];
    }

    private function processValidData(array $validatedData, array $params, int $total): array
    {
        $processed = 0;
        $errors = [];
        $details = [];

        DB::beginTransaction();

        try {
            foreach ($validatedData as $data) {
                $this->processSchoolGrade($data['school'], $data['grade'], $params['year'], $params['user'], $params);
                $processed++;
                $details[] = [
                    'type' => 'success',
                    'message' => "Escola '{$data['school']->name}' e série '{$data['grade']->nm_serie}' processadas com sucesso.",
                    'school_id' => $data['school']->cod_escola,
                    'grade_id' => $data['grade']->cod_serie,
                ];
            }

            DB::commit();

            $message = "Processadas {$processed} escola/série com sucesso.";

            return [
                'status' => self::STATUS_COMPLETED,
                'total' => $total,
                'processed' => $processed,
                'errors' => $errors,
                'details' => $details,
                'message' => $message,
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'status' => self::STATUS_FAILED,
                'total' => $total,
                'processed' => $processed,
                'errors' => [
                    [
                        'school_id' => 0,
                        'grade_id' => 0,
                        'error' => 'Erro crítico no processamento: ' . $e->getMessage(),
                    ],
                ],
                'details' => $details,
                'message' => 'Erro crítico durante o processamento.',
            ];
        }
    }

    private function validateSchoolGradeCombinationFromCollections(
        int $escolaId,
        int $serieId,
        int $year
    ): array {
        try {
            $school = $this->schools->get($escolaId);

            if (!$school) {
                return [
                    'success' => false,
                    'error' => "Escola ID {$escolaId} não encontrada ou inativa.",
                ];
            }

            $grade = $this->grades->get($serieId);

            if (!$grade) {
                return [
                    'success' => false,
                    'error' => "Série ID {$serieId} não encontrada ou inativa.",
                ];
            }

            $schoolAcademicYear = $this->schoolAcademicYears->get($school->cod_escola);

            if (!$schoolAcademicYear) {
                return [
                    'success' => false,
                    'error' => "Escola '{$school->name}' não possui ano letivo {$year} cadastrado.",
                ];
            }

            if ($schoolAcademicYear->andamento != LegacySchoolAcademicYear::IN_PROGRESS) {
                $statusText = match ($schoolAcademicYear->andamento) {
                    LegacySchoolAcademicYear::NOT_INITIALIZED => 'não iniciado',
                    LegacySchoolAcademicYear::FINALIZED => 'finalizado',
                    default => 'desconhecido'
                };

                return [
                    'success' => false,
                    'error' => "Escola '{$school->name}' possui ano letivo {$year} {$statusText}. O ano letivo deve estar em andamento.",
                ];
            }

            $schoolCourse = $this->schoolCourses->get($school->cod_escola);

            if (!$schoolCourse) {
                return [
                    'success' => false,
                    'error' => "Escola '{$school->name}' não possui o ano {$year} cadastrado em nenhum curso.",
                ];
            }

            return [
                'success' => true,
                'data' => [
                    'school' => $school,
                    'grade' => $grade,
                    'schoolAcademicYear' => $schoolAcademicYear,
                ],
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => "Erro ao validar escola ID {$escolaId} e série ID {$serieId}: " . $e->getMessage(),
            ];
        }
    }

    private function processSchoolGrade($school, $grade, $academicYear, $user, $params): void
    {
        $existingSchoolGrade = LegacySchoolGrade::where('ref_cod_escola', $school->cod_escola)
            ->where('ref_cod_serie', $grade->cod_serie)
            ->where('ativo', 1)
            ->first();

        $blockingParams = [
            'bloquear_enturmacao_sem_vagas' => $params['bloquear_enturmacao_sem_vagas'] ?? 0,
            'bloquear_cadastro_turma_para_serie_com_vagas' => $params['bloquear_cadastro_turma_para_serie_com_vagas'] ?? 0,
        ];

        if (!$existingSchoolGrade) {
            $schoolGrade = new LegacySchoolGrade;
            $schoolGrade->ref_cod_escola = $school->cod_escola;
            $schoolGrade->ref_cod_serie = $grade->cod_serie;
            $schoolGrade->ref_usuario_cad = $user->id;
            $schoolGrade->ativo = 1;
            $schoolGrade->anos_letivos = transformDBArrayInString([$academicYear]);
            $schoolGrade->data_cadastro = now();
            $schoolGrade->bloquear_enturmacao_sem_vagas = $blockingParams['bloquear_enturmacao_sem_vagas'];
            $schoolGrade->bloquear_cadastro_turma_para_serie_com_vagas = $blockingParams['bloquear_cadastro_turma_para_serie_com_vagas'];
            $schoolGrade->save();
        } else {
            $anosLetivos = $existingSchoolGrade->anos_letivos ?? [];

            if (is_string($anosLetivos)) {
                $anosLetivos = transformStringFromDBInArray($anosLetivos) ?? [];
            }

            if (!in_array($academicYear, $anosLetivos)) {
                $anosLetivos[] = $academicYear;
                $existingSchoolGrade->anos_letivos = transformDBArrayInString($anosLetivos);
            }

            $existingSchoolGrade->bloquear_enturmacao_sem_vagas = $blockingParams['bloquear_enturmacao_sem_vagas'];
            $existingSchoolGrade->bloquear_cadastro_turma_para_serie_com_vagas = $blockingParams['bloquear_cadastro_turma_para_serie_com_vagas'];
            $existingSchoolGrade->save();
        }

        $existingDisciplines = LegacySchoolGradeDiscipline::where('ref_ref_cod_serie', $grade->cod_serie)
            ->where('ref_ref_cod_escola', $school->cod_escola)
            ->where('ativo', 1)
            ->get()
            ->keyBy('ref_cod_disciplina');

        $disciplines = LegacyDisciplineAcademicYear::query()
            ->whereGrade($grade->cod_serie)
            ->whereYearEq($academicYear)
            ->with('discipline')
            ->get();

        foreach ($disciplines as $discipline) {
            $existingDiscipline = $existingDisciplines->get($discipline->componente_curricular_id);

            if (!$existingDiscipline) {
                $schoolGradeDiscipline = new LegacySchoolGradeDiscipline;
                $schoolGradeDiscipline->ref_ref_cod_serie = $grade->cod_serie;
                $schoolGradeDiscipline->ref_ref_cod_escola = $school->cod_escola;
                $schoolGradeDiscipline->ref_cod_disciplina = $discipline->componente_curricular_id;
                $schoolGradeDiscipline->ativo = 1;
                $schoolGradeDiscipline->anos_letivos = transformDBArrayInString([$academicYear]);
                $schoolGradeDiscipline->save();
            } else {
                $anosLetivos = $existingDiscipline->anos_letivos ?? [];

                if (is_string($anosLetivos)) {
                    $anosLetivos = transformStringFromDBInArray($anosLetivos) ?? [];
                }

                if (!in_array($academicYear, $anosLetivos)) {
                    $anosLetivos[] = $academicYear;
                    $existingDiscipline->anos_letivos = transformDBArrayInString($anosLetivos);
                    $existingDiscipline->save();
                }
            }
        }
    }
}
