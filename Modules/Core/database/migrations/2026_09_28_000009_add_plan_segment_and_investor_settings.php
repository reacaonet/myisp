<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addSegmentToPlans();
        $this->seedSegmentSettings();
    }

    protected function addSegmentToPlans(): void
    {
        if (! Schema::hasColumn('plans', 'segment')) {
            Schema::table('plans', function (Blueprint $t) {
                $t->string('segment')->nullable()->default('residencial')->after('name');
            });
        }

        // planos legados que nao possuem segmento de negocio sao residenciais,
        // exceto os-evidentes (dedicado, corporativo, empresarial, business...)
        DB::table('plans')->whereNull('segment')->update(['segment' => 'residencial']);

        DB::table('plans')
            ->where('segment', 'residencial')
            ->whereRaw("name ~* '(dedicad|corporativ|empresarial|business|enterprise)'")
            ->update(['segment' => 'empresarial']);

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX IF NOT EXISTS plans_segment_index ON plans (segment)');
        } else {
            Schema::table('plans', function (Blueprint $t) {
                $t->index('segment');
            });
        }
    }

    protected function seedSegmentSettings(): void
    {
        $now = now();

        $settings = [
            [
                'key' => 'landing_business_enabled',
                'value' => '1',
                'type' => 'text',
                'group' => 'landing',
                'label' => 'Exibir secao "Para Sua Empresa"',
            ],
            [
                'key' => 'landing_business_title',
                'value' => 'Internet para Empresas',
                'type' => 'text',
                'group' => 'landing',
                'label' => 'Titulo da secao empresarial',
            ],
            [
                'key' => 'landing_business_subtitle',
                'value' => 'Link dedicado, IP fixo e suporte prioritario para o seu negocio não parar.',
                'type' => 'textarea',
                'group' => 'landing',
                'label' => 'Subtitulo da secao empresarial',
            ],
            [
                'key' => 'landing_business_features',
                'value' => "Internet dedicada com 99,9% de disponibilidade\nIP fixo e suporte dedicado\nSLA de atendimento e cobertura monitorada\nWi-Fi corporativo e redes guest",
                'type' => 'textarea',
                'group' => 'landing',
                'label' => 'Diferenciais empresariais (um por linha)',
            ],
            [
                'key' => 'landing_business_cta_label',
                'value' => 'Falar com especialista',
                'type' => 'text',
                'group' => 'landing',
                'label' => 'Texto do botao empresarial',
            ],
            [
                'key' => 'landing_investors_enabled',
                'value' => '1',
                'type' => 'text',
                'group' => 'landing',
                'label' => 'Exibir menu/pagina de investidores',
            ],
            [
                'key' => 'landing_investors_title',
                'value' => 'Invista em uma franquia de internet',
                'type' => 'text',
                'group' => 'landing',
                'label' => 'Titulo da pagina de investidores',
            ],
            [
                'key' => 'landing_investors_subtitle',
                'value' => 'Alta margem recorrente, baixa operação e uma marca já validada. Seja um investidor {{company_fantasy}}.',
                'type' => 'textarea',
                'group' => 'landing',
                'label' => 'Subtitulo da pagina de investidores',
            ],
            [
                'key' => 'landing_investors_benefits',
                'value' => "Receita recorrente mensal de assinaturas\nExpansão rápida com infraestructura e suporte centralizados\nMarca pronta: site, portal do cliente e materiais de venda\nSuporte comercial, técnico e financeiro ao investidor",
                'type' => 'textarea',
                'group' => 'landing',
                'label' => 'Vantagens de ser investidor (uma por linha)',
            ],
            [
                'key' => 'landing_investors_numbers',
                'value' => "Fibra 100% optica\nChurn baixo\nSuporte 24h",
                'type' => 'textarea',
                'group' => 'landing',
                'label' => 'Indicadores exibidos (um por linha, formato: valor | rotulo)',
            ],
            [
                'key' => 'landing_investors_steps',
                'value' => "1. Conversa inicial - um bate-papo com nosso time comercial\n2. Estudo de viabilidade - analise da sua cidade e do investimento\n3. Contrato de franquia - assinatura com a franqueadora\n4. Onboarding - treinamentos, marca e primeiro turno de vendas",
                'type' => 'textarea',
                'group' => 'landing',
                'label' => 'Como virar investidor (um passo por linha)',
            ],
            [
                'key' => 'landing_investors_cta_title',
                'value' => 'Quer saber mais?',
                'type' => 'text',
                'group' => 'landing',
                'label' => 'Titulo do chamado para investimento',
            ],
            [
                'key' => 'landing_investors_cta_text',
                'value' => 'Fale com nosso time comercial e receba a apresentacao completa.',
                'type' => 'text',
                'group' => 'landing',
                'label' => 'Texto do chamado para investimento',
            ],
        ];

        foreach ($settings as $setting) {
            $exists = DB::table('system_settings')->whereNull('company_id')->where('key', $setting['key'])->exists();

            if ($exists) {
                continue;
            }

            DB::table('system_settings')->insert([
                'company_id' => null,
                'key' => $setting['key'],
                'value' => $setting['value'],
                'type' => $setting['type'],
                'group' => $setting['group'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('system_settings')->whereNull('company_id')->whereIn('key', [
            'landing_business_enabled',
            'landing_business_title',
            'landing_business_subtitle',
            'landing_business_features',
            'landing_business_cta_label',
            'landing_investors_enabled',
            'landing_investors_title',
            'landing_investors_subtitle',
            'landing_investors_benefits',
            'landing_investors_numbers',
            'landing_investors_steps',
            'landing_investors_cta_title',
            'landing_investors_cta_text',
        ])->delete();

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS plans_segment_index');
        }

        if (Schema::hasColumn('plans', 'segment')) {
            Schema::table('plans', function (Blueprint $t) {
                $t->dropColumn('segment');
            });
        }
    }
};
