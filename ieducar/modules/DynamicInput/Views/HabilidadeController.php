<?php

class HabilidadeController extends ApiCoreController
{
    protected function getEscolas()
    {
        try {
            $resources = [];

            $escolas = App_Model_IedFinder::getEscolas();

            foreach ($escolas as $id => $name) {
                $resources['__' . $id] = $this->toUtf8($name);
            }

            return ['options' => $resources];
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
                $skills = Canoastec\Provas\Models\Skill::query()
                    ->orderBy('acronym')
                    ->get(['id', 'acronym', 'name']);

                foreach ($skills as $skill) {
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
                $skills = Canoastec\Provas\Models\Skill::query()
                    ->orderBy('acronym')
                    ->get(['id', 'acronym', 'name']);

                foreach ($skills as $skill) {
                    $label = trim($skill->acronym . ' - ' . $skill->name);
                    $resources['__' . $skill->id] = $this->toUtf8($label);
                }
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
        } else {
            $this->notImplementedOperationError();
        }
    }
}
