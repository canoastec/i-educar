<?php

class Portabilis_View_Helper_DynamicInput_Habilidade extends Portabilis_View_Helper_DynamicInput_CoreSelect
{
    protected function inputName()
    {
        return 'habilidade';
    }

    protected function inputOptions($options)
    {
        $resources = $options['resources'] ?? [];

        return $this->insertOption(null, 'Todas as habilidades', $resources);
    }

    protected function defaultOptions()
    {
        return ['options' => ['label' => 'Habilidade']];
    }

    public function habilidade($options = [])
    {
        parent::select($options);
        Portabilis_View_Helper_Application::loadChosenLib($this->viewInstance);
        Portabilis_View_Helper_Application::loadJavascript($this->viewInstance, '/vendor/legacy/DynamicInput/Assets/Javascripts/Habilidade.js');
    }
}
