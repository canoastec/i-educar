<?php

use App\Models\RegistrationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('relatorio.situacao_matricula')->insert([
            'cod_situacao' => RegistrationStatus::FICAI,
            'descricao' => 'FICAI',
        ]);
    }

    public function down(): void
    {
        DB::table('relatorio.situacao_matricula')
            ->where('cod_situacao', RegistrationStatus::FICAI)
            ->delete();
    }
};
