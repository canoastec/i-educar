<?php

class Portabilis_View_Helper_DynamicInput_EixoConhecimento extends Portabilis_View_Helper_DynamicInput_CoreSelect
{
    protected function inputName()
    {
        return 'eixo_conhecimento';
    }

    protected function inputOptions($options)
    {
        $resources = $options['resources'] ?? [];

        return $this->insertOption(null, 'Todos os eixos', $resources);
    }

    protected function defaultOptions()
    {
        return ['options' => ['label' => 'Eixo de Conhecimento']];
    }

    public function eixoConhecimento($options = [])
    {
        parent::select($options);
        Portabilis_View_Helper_Application::loadChosenLib($this->viewInstance);
        Portabilis_View_Helper_Application::loadJavascript($this->viewInstance, '/vendor/legacy/DynamicInput/Assets/Javascripts/EixoConhecimento.js');
    }
}
