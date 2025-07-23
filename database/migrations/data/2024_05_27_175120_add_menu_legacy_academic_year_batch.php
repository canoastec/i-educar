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
            'process' => Process::ACADEMIC_YEAR_IMPORT,
            'title' => 'Ano Letivo em Lote',
            'link' => '/ano-letivo-em-lote',
        ]);
    }

    public function down(): void
    {
        Menu::query()->where('process', Process::ACADEMIC_YEAR_IMPORT)->delete();
        Menu::query()->where('process', Process::ON_BOARDING)->delete();
    }
};
