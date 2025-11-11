<?php

class Portabilis_View_Helper_DynamicInput_DisciplinaProva extends Portabilis_View_Helper_DynamicInput_CoreSelect
{
    protected function inputName()
    {
        return 'disciplina_prova';
    }

    protected function inputOptions($options)
    {
        $resources = $options['resources'] ?? [];
        return $this->insertOption(null, 'Selecione uma disciplina', $resources);
    }

    protected function defaultOptions()
    {
        return ['options' => ['label' => 'Disciplina da Prova']];
    }

    public function disciplinaProva($options = [])
    {
        parent::select($options);
        Portabilis_View_Helper_Application::loadChosenLib($this->viewInstance);
        Portabilis_View_Helper_Application::loadJavascript($this->viewInstance, '/vendor/legacy/DynamicInput/Assets/Javascripts/DisciplinaProva.js');
    }
}




