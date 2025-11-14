<?php

class Portabilis_View_Helper_DynamicInput_SerieProva extends Portabilis_View_Helper_DynamicInput_CoreSelect
{
    protected function inputName()
    {
        return 'serie_prova';
    }

    protected function inputOptions($options)
    {
        $resources = $options['resources'] ?? [];
        return $this->insertOption(null, 'Selecione uma série', $resources);
    }

    protected function defaultOptions()
    {
        return ['options' => ['label' => 'Série da Prova']];
    }

    public function serieProva($options = [])
    {
        parent::select($options);
        Portabilis_View_Helper_Application::loadChosenLib($this->viewInstance);
        Portabilis_View_Helper_Application::loadJavascript($this->viewInstance, '/vendor/legacy/DynamicInput/Assets/Javascripts/SerieProva.js');
    }
}




