<?php

class Portabilis_View_Helper_DynamicInput_EscolaProva extends Portabilis_View_Helper_DynamicInput_CoreSelect
{
    protected function inputName()
    {
        return 'escola_prova';
    }

    protected function inputOptions($options)
    {
        $resources = $options['resources'] ?? [];
        return $this->insertOption(null, 'Selecione uma escola', $resources);
    }

    protected function defaultOptions()
    {
        return ['options' => ['label' => 'Escola da Prova']];
    }

    public function escolaProva($options = [])
    {
        parent::select($options);
        Portabilis_View_Helper_Application::loadChosenLib($this->viewInstance);
        Portabilis_View_Helper_Application::loadJavascript($this->viewInstance, '/vendor/legacy/DynamicInput/Assets/Javascripts/EscolaProva.js');
    }
}




