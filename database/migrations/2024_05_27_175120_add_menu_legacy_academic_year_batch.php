<?php

declare(strict_types=1);

use App\Menu;
use App\Process;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $menu = Menu::query()->create([
            'parent_id' => Menu::query()->where('process', Process::MENU_CONFIGURATIONS)->value('id'),
            'process' => Process::ON_BOARDING,
            'title' => 'On-Boarding',
            'type' => 2,
            'order' => 4,
            'parent_old' => Process::MENU_CONFIGURATIONS,
        ]);

        Menu::query()->create([
            'parent_id' => $menu->getKey(),
            'process' => 1051,
            'title' => 'Ano Letivo em Lote',
            'link' => '/intranet/educar_ano_letivo_modulo_lote_cad.php',
        ]);
    }

    public function down(): void
    {
        Menu::query()->where('process', Process::ON_BOARDING)->delete();
        Menu::query()->where('process', 1051)->delete();
    }
};
