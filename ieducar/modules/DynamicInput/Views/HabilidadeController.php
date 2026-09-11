<?php

class HabilidadeController extends ApiCoreController
{
    protected function getEscolas()
    {
        try {
            $resources = [];

            $escolas = App\Models\LegacySchool::query()
                ->where('ativo', 1)
                ->where('caracteristica_escolar', App\Models\Enums\SchoolCharacteristic::ELEMENTARY->value)
                ->with(['person', 'organization'])
                ->orderBy('cod_escola')
                ->get()
                ->sortBy(fn ($escola) => mb_strtoupper($escola->name ?? ''))
                ->mapWithKeys(fn ($escola) => [
                    $escola->cod_escola => mb_strtoupper($escola->name ?? ('Escola #' . $escola->cod_escola)),
                ]);

            foreach ($escolas as $id => $name) {
                $resources['__' . $id] = $this->toUtf8($name);
            }

            return ['options' => app(\Canoastec\Provas\Services\ReportSchoolScopeService::class)
                ->filterSchoolOptions($resources)];
        } catch (\Throwable $e) {
            return ['options' => []];
        }
    }

    protected function canGetTurmas()
    {
        return $this->validatesPresenceOf('escola_habilidade');
    }

    protected function getTurmas()
    {
        if (!$this->canGetTurmas()) {
            return ['options' => []];
        }

        try {
            $escola = $this->getRequest()->escola_habilidade;

            if (! app(\Canoastec\Provas\Services\ReportSchoolScopeService::class)->allowsSchool($escola)) {
                return ['options' => []];
            }

            $turmas = App_Model_IedFinder::getTurmas($escola);

            $resources = [];

            foreach ($turmas as $id => $name) {
                $resources['__' . $id] = $this->toUtf8($name);
            }

            return ['options' => $resources];
        } catch (\Throwable $e) {
            return ['options' => []];
        }
    }

    protected function getCodigos()
    {
        try {
            $resources = [];

            if (class_exists('Canoastec\\Provas\\Models\\Skill')) {
                foreach ($this->getSkillsFilteredByEixo() as $skill) {
                    $resources['__' . $skill->id] = $this->toUtf8($skill->acronym);
                }
            }

            return ['options' => $resources];
        } catch (\Throwable $e) {
            return ['options' => []];
        }
    }

    protected function getHabilidades()
    {
        try {
            $resources = [];

            if (class_exists('Canoastec\\Provas\\Models\\Skill')) {
                foreach ($this->getSkillsFilteredByEixo() as $skill) {
                    $label = trim($skill->acronym . ' - ' . $skill->name);
                    $resources['__' . $skill->id] = $this->toUtf8($label);
                }
            }

            return ['options' => $resources];
        } catch (\Throwable $e) {
            return ['options' => []];
        }
    }

    protected function getSkillsFilteredByEixo()
    {
        $eixo = $this->getRequest()->eixo_conhecimento ?? null;

        $query = Canoastec\Provas\Models\Skill::query()->orderBy('acronym');

        if ($eixo !== null && $eixo !== '') {
            $query->where('knowledge_axis_id', (int) $eixo);
        }

        return $query->get(['id', 'acronym', 'name', 'knowledge_axis_id']);
    }

    protected function getDisciplinas()
    {
        try {
            $resources = [];

            if (! class_exists('Canoastec\\Provas\\Models\\Exam') || ! class_exists('App\\Models\\LegacyDiscipline')) {
                return ['options' => $resources];
            }

            $disciplineIds = Canoastec\Provas\Models\Exam::query()
                ->whereNotNull('discipline_id')
                ->distinct()
                ->pluck('discipline_id')
                ->all();

            if (empty($disciplineIds)) {
                return ['options' => $resources];
            }

            $disciplines = App\Models\LegacyDiscipline::query()
                ->whereIn('id', $disciplineIds)
                ->orderBy('nome')
                ->get(['id', 'nome']);

            foreach ($disciplines as $discipline) {
                $resources['__' . $discipline->id] = $this->toUtf8($discipline->nome);
            }

            return ['options' => $resources];
        } catch (\Throwable $e) {
            return ['options' => []];
        }
    }

    public function Gerar()
    {
        if ($this->isRequestFor('get', 'escolas')) {
            $this->appendResponse($this->getEscolas());
        } elseif ($this->isRequestFor('get', 'turmas')) {
            $this->appendResponse($this->getTurmas());
        } elseif ($this->isRequestFor('get', 'codigos')) {
            $this->appendResponse($this->getCodigos());
        } elseif ($this->isRequestFor('get', 'habilidades')) {
            $this->appendResponse($this->getHabilidades());
        } elseif ($this->isRequestFor('get', 'disciplinas')) {
            $this->appendResponse($this->getDisciplinas());
        } else {
            $this->notImplementedOperationError();
        }
    }
}
