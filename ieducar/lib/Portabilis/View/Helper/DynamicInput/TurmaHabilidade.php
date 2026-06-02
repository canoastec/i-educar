<?php

class Portabilis_View_Helper_DynamicInput_TurmaHabilidade extends Portabilis_View_Helper_DynamicInput_CoreSelect
{
    protected function inputName()
    {
        return 'turma_habilidade';
    }

    protected function inputOptions($options)
    {
        $resources = $options['resources'] ?? [];

        return $this->insertOption(null, 'Todas as turmas', $resources);
    }

    protected function defaultOptions()
    {
        return ['options' => ['label' => 'Turma']];
    }

    public function turmaHabilidade($options = [])
    {
        parent::select($options);
        Portabilis_View_Helper_Application::loadChosenLib($this->viewInstance);
        Portabilis_View_Helper_Application::loadJavascript($this->viewInstance, '/vendor/legacy/DynamicInput/Assets/Javascripts/TurmaHabilidade.js');
    }
}
