<?php

class ProvaController extends ApiCoreController
{
    protected function canGetProvas()
    {
        return $this->validatesPresenceOf('ano');
    }

    protected function getProvas()
    {
        if ($this->canGetProvas()) {
            $ano = $this->getRequest()->ano;

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

    public function Gerar()
    {
        if ($this->isRequestFor('get', 'provas')) {
            $this->appendResponse($this->getProvas());
        } else {
            $this->notImplementedOperationError();
        }
    }
}


