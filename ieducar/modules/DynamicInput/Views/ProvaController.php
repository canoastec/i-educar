<?php

class ProvaController extends ApiCoreController
{
    protected function canGetProvas()
    {
        return $this->validatesId('turma');
    }

    protected function getProvas()
    {
        if ($this->canGetProvas()) {
            $turmaId = $this->getRequest()->turma_id;

            $resources = $this->getProvasFromDatabase($turmaId);

            $options = [];
            foreach ($resources as $provaId => $prova) {
                $options['__' . $provaId] = $this->toUtf8($prova);
            }

            return ['options' => $options];
        }
    }

    /**
     * Busca provas do banco de dados
     */
    protected function getProvasFromDatabase($turmaId)
    {
        return \Canoastec\Canoastec\Models\AppliedExam::query()
            ->where('school_class_id', $turmaId)
            ->pluck('description', 'id')->toArray();
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
