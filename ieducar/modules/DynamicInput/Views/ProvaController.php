<?php

class ProvaController extends ApiCoreController
{
    protected function canGetProvas()
    {
        return $this->validatesPresenceOf(['ano', 'serie_prova', 'disciplina_prova']);
    }

    protected function getProvas()
    {
        if ($this->canGetProvas()) {
            $ano = $this->getRequest()->ano;
            $serie = $this->getRequest()->serie_prova ?? null;
            $disciplina = $this->getRequest()->disciplina_prova ?? null;

            try {
                $now = \Carbon\Carbon::now();
                $resources = [];

                if (class_exists('Canoastec\\Provas\\Models\\Exam') && class_exists('Canoastec\\Provas\\Models\\StudentExam')) {
                    $examIdsWithStudent = Canoastec\Provas\Models\StudentExam::query()
                        ->distinct()
                        ->pluck('exam_id')
                        ->all();

                    if (! empty($examIdsWithStudent)) {
                        $query = Canoastec\Provas\Models\Exam::query()
                            ->whereIn('id', $examIdsWithStudent)
                            ->whereYear('end_date', '=', $ano)
                            ->where('end_date', '<', $now)
                            ->orderBy('description');

                        if (! empty($serie)) {
                            $query->where('grade_id', $serie);
                        }
                        if (! empty($disciplina)) {
                            $query->where('discipline_id', $disciplina);
                        }

                        foreach ($query->get(['id', 'description']) as $exam) {
                            $resources['__' . $exam->id] = $this->toUtf8($exam->description);
                        }
                    }
                }

                return ['options' => $resources];
            } catch (\Throwable $e) {
                return ['options' => []];
            }
        }
    }

    // Séries disponíveis por ano (a partir das provas existentes no ano)
    protected function canGetSeries()
    {
        return $this->validatesPresenceOf('ano');
    }

    protected function getSeries()
    {
        if ($this->canGetSeries()) {
            $ano = $this->getRequest()->ano;

            try {
                $resources = [];
                if (class_exists('Canoastec\\Provas\\Models\\Exam')) {
                    $exams = Canoastec\Provas\Models\Exam::query()
                        ->with(['grade'])
                        ->whereYear('end_date', '=', $ano)
                        ->get();

                    $series = collect();
                    foreach ($exams as $exam) {
                        $grade = $exam->grade;
                        $serieId = $exam->grade_id ?? ($grade->cod_serie ?? $grade->id ?? null);
                        $serieName = $grade->name ?? $grade->nm_serie ?? (isset($serieId) ? ('Série #' . $serieId) : null);
                        if ($serieId && $serieName) {
                            $series->put($serieId, $this->toUtf8($serieName));
                        }
                    }

                    foreach ($series->sort() as $id => $name) {
                        $resources['__' . $id] = $name;
                    }
                }

                return ['options' => $resources];
            } catch (\Throwable $e) {
                return ['options' => []];
            }
        }
    }

    // Disciplinas disponíveis por ano + série (a partir das provas existentes)
    protected function canGetDisciplinas()
    {
        return $this->validatesPresenceOf(['ano', 'serie_prova']);
    }

    protected function getDisciplinas()
    {
        if ($this->canGetDisciplinas()) {
            $ano = $this->getRequest()->ano;
            $serie = $this->getRequest()->serie_prova;

            try {
                $resources = [];
                if (class_exists('Canoastec\\Provas\\Models\\Exam')) {
                    $exams = Canoastec\Provas\Models\Exam::query()
                        ->with(['discipline'])
                        ->whereYear('end_date', '=', $ano)
                        ->where('grade_id', $serie)
                        ->get();

                    $disciplines = collect();
                    foreach ($exams as $exam) {
                        $discipline = $exam->discipline;
                        $id = $exam->discipline_id ?? ($discipline->id ?? null);
                        $name = $discipline->name ?? $discipline->nm_disciplina ?? (isset($id) ? ('Disciplina #' . $id) : null);
                        if ($id && $name) {
                            $disciplines->put($id, $this->toUtf8($name));
                        }
                    }

                    foreach ($disciplines->sort() as $id => $name) {
                        $resources['__' . $id] = $name;
                    }
                }

                return ['options' => $resources];
            } catch (\Throwable $e) {
                return ['options' => []];
            }
        }
    }

    // Escolas da prova (filtra por ano e prova)
    protected function canGetEscolas()
    {
        return $this->validatesPresenceOf(['ano', 'prova']);
    }

    protected function getEscolas()
    {
        if ($this->canGetEscolas()) {
            $ano = $this->getRequest()->ano;
            $prova = $this->getRequest()->prova;

            try {
                $resources = [];

                if (class_exists('Canoastec\\Provas\\Models\\StudentExam') && class_exists('App\\Models\\LegacyRegistration') && class_exists('App\\Models\\LegacyEnrollment')) {
                    $studentExams = Canoastec\Provas\Models\StudentExam::query()
                        ->where('exam_id', $prova)
                        ->get();

                    $schools = collect();

                    foreach ($studentExams as $se) {
                        $student = $se->student ?? null;
                        if (! $student) continue;

                        $registration = App\Models\LegacyRegistration::where('ref_cod_aluno', $student->cod_aluno)
                            ->where('ativo', 1)
                            ->where('ano', $ano)
                            ->first();
                        if (! $registration) continue;

                        $enrollment = App\Models\LegacyEnrollment::where('ref_cod_matricula', $registration->cod_matricula)
                            ->where('ativo', 1)
                            ->with(['schoolClass.school'])
                            ->orderByDesc('sequencial')
                            ->first();
                        if (! $enrollment) continue;

                        $school = optional(optional($enrollment->schoolClass)->school);
                        if ($school && ($school->cod_escola || $school->id)) {
                            $id = $school->cod_escola ?? $school->id;
                            $name = $school->name ?? $school->nome ?? ('Escola #' . $id);
                            $schools->put($id, $this->toUtf8($name));
                        }
                    }

                    foreach ($schools->sort() as $id => $name) {
                        $resources['__' . $id] = $name;
                    }
                }

                return ['options' => $resources];
            } catch (\Throwable $e) {
                return ['options' => []];
            }
        }
    }


    // Turmas da prova (filtra por ano, prova e escola)
    protected function canGetTurmas()
    {
        return $this->validatesPresenceOf(['ano', 'prova', 'escola_prova']);
    }

    protected function getTurmas()
    {
        if ($this->canGetTurmas()) {
            $ano = $this->getRequest()->ano;
            $prova = $this->getRequest()->prova;
            $escola = $this->getRequest()->escola_prova;

            try {
                $resources = [];

                if (class_exists('Canoastec\\Provas\\Models\\StudentExam') && class_exists('App\\Models\\LegacyRegistration') && class_exists('App\\Models\\LegacyEnrollment')) {
                    $studentExams = Canoastec\Provas\Models\StudentExam::query()
                        ->where('exam_id', $prova)
                        ->get();

                    $classes = collect();

                    foreach ($studentExams as $se) {
                        $student = $se->student ?? null;
                        if (! $student) continue;

                        $registration = App\Models\LegacyRegistration::where('ref_cod_aluno', $student->cod_aluno)
                            ->where('ativo', 1)
                            ->where('ano', $ano)
                            ->first();
                        if (! $registration) continue;

                        $enrollment = App\Models\LegacyEnrollment::where('ref_cod_matricula', $registration->cod_matricula)
                            ->where('ativo', 1)
                            ->with(['schoolClass'])
                            ->orderByDesc('sequencial')
                            ->first();
                        if (! $enrollment) continue;

                        $class = optional($enrollment->schoolClass);
                        // Filtra pela escola selecionada
                        if ($escola) {
                            $classSchoolId = $class->ref_ref_cod_escola ?? $class->ref_cod_escola ?? null;
                            if (! $classSchoolId || (string)$classSchoolId !== (string)$escola) {
                                continue;
                            }
                        }
                        if ($class && ($class->cod_turma || $class->id)) {
                            $id = $class->cod_turma ?? $class->id;
                            $name = $class->nm_turma ?? ('Turma #' . $id);
                            $classes->put($id, $this->toUtf8($name));
                        }
                    }

                    foreach ($classes->sort() as $id => $name) {
                        $resources['__' . $id] = $name;
                    }
                }

                return ['options' => $resources];
            } catch (\Throwable $e) {
                return ['options' => []];
            }
        }
    }

    public function Gerar()
    {
        if ($this->isRequestFor('get', 'provas')) {
            $this->appendResponse($this->getProvas());
        } elseif ($this->isRequestFor('get', 'series')) {
            $this->appendResponse($this->getSeries());
        } elseif ($this->isRequestFor('get', 'disciplinas')) {
            $this->appendResponse($this->getDisciplinas());
        } elseif ($this->isRequestFor('get', 'escolas')) {
            $this->appendResponse($this->getEscolas());
        } elseif ($this->isRequestFor('get', 'turmas')) {
            $this->appendResponse($this->getTurmas());
        } else {
            $this->notImplementedOperationError();
        }
    }
}


