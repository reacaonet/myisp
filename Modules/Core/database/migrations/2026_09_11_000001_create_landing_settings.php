<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            ['key' => 'landing_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'landing'],
            ['key' => 'landing_logo', 'value' => '', 'type' => 'file', 'group' => 'landing'],
            ['key' => 'landing_hero_title', 'value' => 'Internet Fibra Optica de Alta Velocidade', 'type' => 'text', 'group' => 'landing'],
            ['key' => 'landing_hero_subtitle', 'value' => 'A melhor conectividade para sua casa ou empresa. Suporte rapido, estabilidade e planos que cabem no seu bolso.', 'type' => 'textarea', 'group' => 'landing'],
            ['key' => 'landing_about', 'value' => '', 'type' => 'textarea', 'group' => 'landing'],
            ['key' => 'landing_whatsapp', 'value' => '', 'type' => 'text', 'group' => 'landing'],
            ['key' => 'landing_hours', 'value' => 'Seg a Sab, das 8h as 18h', 'type' => 'text', 'group' => 'landing'],
            ['key' => 'landing_facebook', 'value' => '', 'type' => 'text', 'group' => 'landing'],
            ['key' => 'landing_instagram', 'value' => '', 'type' => 'text', 'group' => 'landing'],
        ];

        foreach ($settings as $setting) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    public function down(): void
    {
        DB::table('system_settings')->where('group', 'landing')->delete();
    }
};