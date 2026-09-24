<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $items = [
            ['key' => 'landing_sac_title', 'value' => 'Central de Atendimento (SAC)', 'type' => 'text', 'group' => 'landing'],
            ['key' => 'landing_sac_subtitle', 'value' => 'Estamos aqui para ajudar, resolver seu problema e deixar voce conectado.', 'type' => 'text', 'group' => 'landing'],
        ];

        foreach ($items as $item) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $item['key']],
                $item
            );
        }
    }

    public function down(): void
    {
        DB::table('system_settings')
            ->whereIn('key', ['landing_sac_title', 'landing_sac_subtitle'])
            ->delete();
    }
};