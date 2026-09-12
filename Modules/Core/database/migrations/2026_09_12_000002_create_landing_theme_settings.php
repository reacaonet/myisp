<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $items = [
            // Cores
            ['key' => 'landing_color_primary', 'value' => '#1d4ed8', 'type' => 'text', 'group' => 'landing'],
            ['key' => 'landing_color_primary_dark', 'value' => '#1e40af', 'type' => 'text', 'group' => 'landing'],
            ['key' => 'landing_color_secondary', 'value' => '#4f46e5', 'type' => 'text', 'group' => 'landing'],
            ['key' => 'landing_color_hero_start', 'value' => '#0b1222', 'type' => 'text', 'group' => 'landing'],
            ['key' => 'landing_color_hero_mid', 'value' => '#0f172a', 'type' => 'text', 'group' => 'landing'],
            ['key' => 'landing_color_hero_end', 'value' => '#1e3a8a', 'type' => 'text', 'group' => 'landing'],
            ['key' => 'landing_color_dark', 'value' => '#0b1222', 'type' => 'text', 'group' => 'landing'],
            ['key' => 'landing_color_footer', 'value' => '#070b16', 'type' => 'text', 'group' => 'landing'],
            // Textos de secao
            ['key' => 'landing_section_plans_title', 'value' => 'Nossos Planos', 'type' => 'text', 'group' => 'landing'],
            ['key' => 'landing_section_plans_subtitle', 'value' => 'Escolha o plano ideal para voce', 'type' => 'text', 'group' => 'landing'],
            ['key' => 'landing_section_coverage_title', 'value' => 'Cobertura', 'type' => 'text', 'group' => 'landing'],
            ['key' => 'landing_section_coverage_subtitle', 'value' => 'Atendemos com fibra optica nas seguintes cidades', 'type' => 'text', 'group' => 'landing'],
            ['key' => 'landing_section_features_title', 'value' => 'Por que escolher', 'type' => 'text', 'group' => 'landing'],
            ['key' => 'landing_section_features_subtitle', 'value' => 'A tecnologia que voce merece, com atendimento de verdade', 'type' => 'text', 'group' => 'landing'],
            ['key' => 'landing_section_faq_title', 'value' => 'Perguntas Frequentes', 'type' => 'text', 'group' => 'landing'],
            ['key' => 'landing_section_faq_subtitle', 'value' => 'Tire suas duvidas antes de contratar', 'type' => 'text', 'group' => 'landing'],
            ['key' => 'landing_section_contact_title', 'value' => 'Fale Conosco', 'type' => 'text', 'group' => 'landing'],
            ['key' => 'landing_section_contact_subtitle', 'value' => 'Estamos prontos para atender voce por qualquer canal', 'type' => 'text', 'group' => 'landing'],
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
            ->whereIn('key', [
                'landing_color_primary', 'landing_color_primary_dark', 'landing_color_secondary',
                'landing_color_hero_start', 'landing_color_hero_mid', 'landing_color_hero_end',
                'landing_color_dark', 'landing_color_footer',
                'landing_section_plans_title', 'landing_section_plans_subtitle',
                'landing_section_coverage_title', 'landing_section_coverage_subtitle',
                'landing_section_features_title', 'landing_section_features_subtitle',
                'landing_section_faq_title', 'landing_section_faq_subtitle',
                'landing_section_contact_title', 'landing_section_contact_subtitle',
            ])->delete();
    }
};