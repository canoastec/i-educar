<?php

class Portabilis_View_Helper_DynamicInput_TurmaProva extends Portabilis_View_Helper_DynamicInput_CoreSelect
{
    protected function inputName()
    {
        return 'turma_prova';
    }

    protected function inputOptions($options)
    {
        $resources = $options['resources'] ?? [];
        return $this->insertOption(null, 'Selecione uma turma', $resources);
    }

    protected function defaultOptions()
    {
        return ['options' => ['label' => 'Turma da Prova']];
    }

    public function turmaProva($options = [])
    {
        parent::select($options);
        Portabilis_View_Helper_Application::loadChosenLib($this->viewInstance);
        Portabilis_View_Helper_Application::loadJavascript($this->viewInstance, '/vendor/legacy/DynamicInput/Assets/Javascripts/TurmaProva.js');
    }
}




