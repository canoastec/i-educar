<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::update("
            UPDATE portal.funcionario AS f
            SET force_reset_password = false
            FROM pmieducar.usuario AS u
            INNER JOIN pmieducar.tipo_usuario AS tu
                ON tu.cod_tipo_usuario = u.ref_cod_tipo_usuario
            WHERE f.ref_cod_pessoa_fj = u.cod_usuario
              AND tu.nm_tipo = 'Aluno'
        ");
    }

    public function down(): void
    {
        DB::update("
            UPDATE portal.funcionario AS f
            SET force_reset_password = true
            FROM pmieducar.usuario AS u
            INNER JOIN pmieducar.tipo_usuario AS tu
                ON tu.cod_tipo_usuario = u.ref_cod_tipo_usuario
            WHERE f.ref_cod_pessoa_fj = u.cod_usuario
              AND tu.nm_tipo = 'Aluno'
        ");
    }
};
