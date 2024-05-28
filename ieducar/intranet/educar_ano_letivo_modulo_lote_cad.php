<?php

use App\Models\LegacyAcademicYearStage;
use App\Models\LegacySchoolAcademicYear;
use App\Models\LegacyStageType;

return new class extends clsCadastro
{
    public $pessoa_logada;

    public $ref_cod_instituicao;

    public $ref_ano;

    public $ref_ref_cod_escola;

    public $sequencial;

    public $ref_cod_modulo;

    public $data_inicio;

    public $data_fim;

    public $ano_letivo_modulo;

    public $modulos = [];

    public $etapas = [];

    public function Inicializar()
    {
        $retorno = 'Novo';

        $obj_permissoes = new clsPermissoes();

        $obj_permissoes->permissao_cadastra(
            int_processo_ap: 1051,
            int_idpes_usuario: $this->pessoa_logada,
            int_soma_nivel_acesso: 7,
            str_pagina_redirecionar: 'educar_configuracoes_index.php'
        );

        $this->url_cancelar = '/intranet/educar_configuracoes_index.php';
        $this->nome_url_cancelar = 'Voltar';

        $this->breadcrumb('Abertura Ano Letivo em Lote', [
            url('intranet/educar_configuracoes_index.php') => 'Configurações',
            null => 'On-Boarding',
        ]);

        return $retorno;
    }

    public function Gerar()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao']);
        $this->inputsHelper()->multipleSearchEscola(null, ['label' => 'Escola(s)']);

        $opcoesCampoModulo = [];

        $modulos = LegacyStageType::query()
            ->where('ativo', 1)
            ->orderBy('nm_tipo')
            ->get()
            ->map(function ($modulo) {
                $modulo->label = sprintf('%s - %d etapa(s)', $modulo->nm_tipo, $modulo->num_etapas);

                return $modulo;
            })
            ->pluck('label', 'cod_modulo')
            ->prepend('Selecione', '');

        $this->campoLista(
            nome: 'ref_cod_modulo',
            campo: 'Etapa',
            valor: $modulos
        );

        $this->campoQuebra();

        $this->campoTabelaInicio(
            nome: 'modulos_ano_letivo',
            titulo: 'Etapas do ano letivo',
            arr_campos: ['Data inicial', 'Data final', 'Dias Letivos'],
            arr_valores: $this->ano_letivo_modulo
        );

        $this->campoData(
            nome: 'data_inicio',
            campo: 'Hora',
            valor: $this->data_inicio,
            obrigatorio: true
        );
        $this->campoData(
            nome: 'data_fim',
            campo: 'Hora',
            valor: $this->data_fim,
            obrigatorio: true
        );
        $this->campoNumero(
            nome: 'dias_letivos',
            campo: 'Dias Letivos',
            valor: $this->dias_letivos,
            tamanhovisivel: 6,
            tamanhomaximo: 3,
        );

        $this->campoTabelaFim();

        $this->campoQuebra();
    }

    public function Novo()
    {
        $obj_permissoes = new clsPermissoes();

        $obj_permissoes->permissao_cadastra(
            int_processo_ap: 1051,
            int_idpes_usuario: $this->pessoa_logada,
            int_soma_nivel_acesso: 7,
            str_pagina_redirecionar: 'educar_configuracoes_index.php'
        );

        $escolas = request('escola');
        $year = request('ano');
        $data_inicio = request('data_inicio');
        $data_fim = request('data_fim');
        $dias_letivos = request('dias_letivos');
        $ref_cod_modulo = request('ref_cod_modulo');

        $modulo = LegacyStageType::query()
            ->find($ref_cod_modulo);

        if (count($data_inicio) !== $modulo->num_etapas) {
            $this->mensagem = 'Quantidade de etapas informadas não confere com a quantidade de etapas da etapa selecionada.';

            return false;
        }

        foreach ($escolas as $escola) {
            $doesntExist = LegacySchoolAcademicYear::query()
                ->active()
                ->whereSchool($escola)
                ->whereYearEq($year)
                ->doesntExist();

            if ($doesntExist) {
                $academicYear = LegacySchoolAcademicYear::create([
                    'ref_cod_escola' => $escola,
                    'ano' => $year,
                    'ref_usuario_cad' => $this->pessoa_logada,
                    'andamento' => 1,
                    'ativo' => 1,
                    'turmas_por_ano' => 1,
                ]);

                foreach ($data_inicio as $key => $valor) {
                    LegacyAcademicYearStage::create([
                        'escola_ano_letivo_id' => $academicYear->getKey(),
                        'ref_ref_cod_escola' => $escola,
                        'ref_ano' => $year,
                        'sequencial' => $key + 1,
                        'data_inicio' => dataToBanco($data_inicio[$key]),
                        'data_fim' => dataToBanco($data_fim[$key]),
                        'dias_letivos' => $dias_letivos[$key],
                        'ref_cod_modulo' => $ref_cod_modulo,
                    ]);
                }
            }
        }

        $this->mensagem = 'Cadastro efetuado com sucesso.<br />';
        $this->simpleRedirect(url: 'educar_ano_letivo_modulo_lote_cad.php');
    }

    public function Formular()
    {
        $this->title = 'Abertura Ano Letivo em Lote';
        $this->processoAp = 1051;
    }
};
