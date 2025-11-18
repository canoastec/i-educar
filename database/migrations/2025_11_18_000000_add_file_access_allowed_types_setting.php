<?php

use App\Setting;
use App\SettingCategory;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $category = SettingCategory::query()->firstOrCreate([
            'name' => 'Validações de sistema',
        ]);

        Setting::query()->updateOrCreate([
            'key' => 'file_access.allowed_types',
        ], [
            'setting_category_id' => $category->getKey(),
            'value' => 'Administrador Portabilis,Âncora(s),Direção e Secretaria Escolar Credenciadas,Direção e Secretaria Escolar Municipal,Secretaria Municipal de Educação (SME)',
            'type' => 'string',
            'description' => 'Tipos de usuário permitidos para acessar arquivos (separados por vírgula)',
            'hint' => 'Lista os tipos de usuário que têm permissão para acessar arquivos. Separe múltiplos tipos por vírgula.',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Setting::query()->where('key', 'file_access.allowed_types')->delete();
    }
};

