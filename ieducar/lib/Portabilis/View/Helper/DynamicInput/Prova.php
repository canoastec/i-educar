<?php

class Portabilis_View_Helper_DynamicInput_Prova extends Portabilis_View_Helper_DynamicInput_CoreSelect
{
    protected function inputName()
    {
        return 'prova';
    }

    protected function inputOptions($options)
    {
        $resources = $options['resources'] ?? [];
        return $this->insertOption(null, 'Selecione uma prova', $resources);
    }

    protected function defaultOptions()
    {
        return ['options' => ['label' => 'Prova']];
    }

    public function prova($options = [])
    {
        parent::select($options);
        Portabilis_View_Helper_Application::loadChosenLib($this->viewInstance);
        Portabilis_View_Helper_Application::loadJavascript($this->viewInstance, '/vendor/legacy/DynamicInput/Assets/Javascripts/Prova.js');
    }
}


