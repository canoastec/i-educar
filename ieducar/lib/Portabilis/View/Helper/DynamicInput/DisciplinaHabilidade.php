<?php

class Portabilis_View_Helper_DynamicInput_DisciplinaHabilidade extends Portabilis_View_Helper_DynamicInput_CoreSelect
{
    protected function inputName()
    {
        return 'disciplina_habilidade';
    }

    protected function inputOptions($options)
    {
        $resources = $options['resources'] ?? [];

        return $this->insertOption(null, 'Todas as disciplinas', $resources);
    }

    protected function defaultOptions()
    {
        return ['options' => ['label' => 'Disciplina']];
    }

    public function disciplinaHabilidade($options = [])
    {
        parent::select($options);
        Portabilis_View_Helper_Application::loadChosenLib($this->viewInstance);
        Portabilis_View_Helper_Application::loadJavascript($this->viewInstance, '/vendor/legacy/DynamicInput/Assets/Javascripts/DisciplinaHabilidade.js');
    }
}
