<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $items = [
            ['key' => 'landing_faq', 'value' => '', 'type' => 'textarea', 'group' => 'landing'],
            ['key' => 'landing_map_embed', 'value' => '', 'type' => 'textarea', 'group' => 'landing'],
            ['key' => 'landing_linkedin', 'value' => '', 'type' => 'text', 'group' => 'landing'],
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
            ->whereIn('key', ['landing_faq', 'landing_map_embed', 'landing_linkedin'])
            ->delete();
    }
};