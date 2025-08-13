<?php

class ProvaController extends ApiCoreController
{
    protected function canGetProvas()
    {
        return $this->validatesId('curso') &&
            $this->validatesId('serie');
    }

    protected function getProvas()
    {
        if ($this->canGetProvas()) {
            $cursoId = $this->getRequest()->curso_id;
            $serieId = $this->getRequest()->serie_id;

            $resources = $this->getProvasFromDatabase($cursoId, $serieId);

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
    protected function getProvasFromDatabase($cursoId, $serieId)
    {
        return \Canoastec\Canoastec\Models\Exam::query()
            ->where('grade_id', $serieId)
            ->where('discipline_id', $cursoId)
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
