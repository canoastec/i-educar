<?php

use Illuminate\Support\Facades\Session;

/**
 * Select de escola exclusivo do relatório Mapa quantitativo de matrículas.
 * Não carrega o Escola.js global — o JS do relatório filtra por característica escolar.
 */
class Portabilis_View_Helper_DynamicInput_EscolaMapaQuantitativo extends Portabilis_View_Helper_DynamicInput_Escola
{
    public function escolaMapaQuantitativo($options = [])
    {
        $nivelUsuario = Session::get('nivel');

        if ($nivelUsuario == App_Model_NivelTipoUsuario::ESCOLA) {
            $options['options']['required'] = true;
        }

        $this->select($options);
        Portabilis_View_Helper_Application::loadChosenLib($this->viewInstance);
    }
}
