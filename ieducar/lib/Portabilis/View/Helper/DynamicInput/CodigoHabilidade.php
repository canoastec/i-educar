<?php

class Portabilis_View_Helper_DynamicInput_CodigoHabilidade extends Portabilis_View_Helper_DynamicInput_CoreSelect
{
    protected function inputName()
    {
        return 'codigo_habilidade';
    }

    protected function inputOptions($options)
    {
        $resources = $options['resources'] ?? [];

        return $this->insertOption(null, 'Todos os códigos', $resources);
    }

    protected function defaultOptions()
    {
        return ['options' => ['label' => 'Código da Habilidade']];
    }

    public function codigoHabilidade($options = [])
    {
        parent::select($options);
        Portabilis_View_Helper_Application::loadChosenLib($this->viewInstance);
        Portabilis_View_Helper_Application::loadJavascript($this->viewInstance, '/vendor/legacy/DynamicInput/Assets/Javascripts/CodigoHabilidade.js');
    }
}
