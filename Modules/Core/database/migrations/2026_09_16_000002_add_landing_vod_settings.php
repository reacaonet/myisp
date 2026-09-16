<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $items = [
            ['key' => 'landing_section_vod_title', 'value' => 'VOD Stream', 'type' => 'text', 'group' => 'landing'],
            ['key' => 'landing_section_vod_subtitle', 'value' => 'Assista onde e quando quiser, incluido no seu plano', 'type' => 'text', 'group' => 'landing'],
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
            ->whereIn('key', ['landing_section_vod_title', 'landing_section_vod_subtitle'])
            ->delete();
    }
};