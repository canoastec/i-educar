<?php

class Portabilis_View_Helper_DynamicInput_EscolaHabilidade extends Portabilis_View_Helper_DynamicInput_CoreSelect
{
    protected function inputName()
    {
        return 'escola_habilidade';
    }

    protected function inputOptions($options)
    {
        $resources = $options['resources'] ?? [];

        return $this->insertOption(null, 'Toda a rede', $resources);
    }

    protected function defaultOptions()
    {
        return ['options' => ['label' => 'Escola']];
    }

    public function escolaHabilidade($options = [])
    {
        parent::select($options);
        Portabilis_View_Helper_Application::loadChosenLib($this->viewInstance);
        Portabilis_View_Helper_Application::loadJavascript($this->viewInstance, '/vendor/legacy/DynamicInput/Assets/Javascripts/EscolaHabilidade.js');
    }
}
