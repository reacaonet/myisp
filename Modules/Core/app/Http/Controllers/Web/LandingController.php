<?php

namespace Modules\Core\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;
use Modules\Core\Models\LandingBanner;
use Modules\Core\Models\SystemSetting;
use Modules\Core\Services\TenantContext;
use Modules\CRM\Models\Plan;

class LandingController extends Controller
{
    public function index()
    {
        if (! $this->landingEnabled()) {
            return redirect()->route('crm.dashboard');
        }

        $data = $this->landingData();

        $data['banners'] = LandingBanner::active()->ordered()->get();
        $data['hero_title'] = $data['settings']['landing_hero_title'] ?? 'Internet Fibra Optica de Alta Velocidade';
        $data['hero_subtitle'] = $data['settings']['landing_hero_subtitle'] ?? '';

        return view('core::landing.index', $data);
    }

    public function sac()
    {
        if (! $this->landingEnabled()) {
            return redirect()->route('crm.dashboard');
        }

        $data = $this->landingData([
            'landing_sac_title' => 'Central de Atendimento (SAC)',
            'landing_sac_subtitle' => 'Estamos aqui para ajudar, resolver seu problema e deixar voce conectado.',
        ]);

        return view('core::landing.sac', $data);
    }

    public function investors()
    {
        if (! $this->landingEnabled() || ! ($this->landingData()['investors_enabled'] ?? false)) {
            return redirect()->route('landing.index');
        }

        $data = $this->landingData();

        return view('core::landing.investors', $data);
    }

    protected function landingEnabled(): bool
    {
        return SystemSetting::get('landing_enabled', '1') === '1';
    }

    protected function lines(string $value): Collection
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $value))
            ->map(fn ($item) => trim($item))
            ->filter(fn ($item) => $item !== '')
            ->values();
    }

    protected function landingData(array $defaults = []): array
    {
        $settings = SystemSetting::getGroup('landing');
        $company = TenantContext::company();

        $settings = array_merge($defaults, $settings);

        $logo = $settings['landing_logo'] ?? '';

        $name = $company?->displayName() ?: 'MyISP';

        $activePlans = Plan::where('is_active', true)->orderBy('price')->get();

        $residentialPlans = $activePlans->where(fn ($plan) => $plan->segment !== 'empresarial')->values();
        $businessPlans = $activePlans->where(fn ($plan) => $plan->segment === 'empresarial')->values();

        $cities = $this->lines($settings['landing_cities'] ?? '');

        $businessFeatures = $this->lines($settings['landing_business_features'] ?? '');

        $investorBenefits = $this->lines($settings['landing_investors_benefits'] ?? '');

        $investorSteps = $this->lines($settings['landing_investors_steps'] ?? '');

        $investorNumbers = $this->lines($settings['landing_investors_numbers'] ?? '')
            ->map(function ($line) {
                [$value, $label] = array_pad(array_map('trim', explode('|', $line, 2)), 2, null);

                return ['value' => $value, 'label' => $label ?: $value];
            })
            ->filter(fn ($item) => $item['value'] !== '')
            ->values();

        $faq = collect(preg_split('/\r?\n\s*\r?\n/', trim($settings['landing_faq'] ?? '')))
            ->filter(fn ($block) => trim($block) !== '')
            ->map(function ($block) {
                $lines = array_values(array_filter(array_map('trim', explode("\n", $block)), fn ($l) => $l !== ''));
                if (count($lines) < 2) {
                    return null;
                }

                return ['question' => $lines[0], 'answer' => implode(' ', array_slice($lines, 1))];
            })
            ->filter()
            ->values();

        $colors = [
            'primary' => $settings['landing_color_primary'] ?? '#1d4ed8',
            'primary_dark' => $settings['landing_color_primary_dark'] ?? '#1e40af',
            'secondary' => $settings['landing_color_secondary'] ?? '#4f46e5',
            'hero_start' => $settings['landing_color_hero_start'] ?? '#0b1222',
            'hero_mid' => $settings['landing_color_hero_mid'] ?? '#0f172a',
            'hero_end' => $settings['landing_color_hero_end'] ?? '#1e3a8a',
            'dark' => $settings['landing_color_dark'] ?? '#0b1222',
            'footer' => $settings['landing_color_footer'] ?? '#070b16',
        ];

        $section_titles = [
            'plans_title' => $settings['landing_section_plans_title'] ?? 'Nossos Planos',
            'plans_subtitle' => $settings['landing_section_plans_subtitle'] ?? 'Escolha o plano ideal para voce',
            'coverage_title' => $settings['landing_section_coverage_title'] ?? 'Cobertura',
            'coverage_subtitle' => $settings['landing_section_coverage_subtitle'] ?? 'Atendemos com fibra optica nas seguintes cidades',
            'features_title' => $settings['landing_section_features_title'] ?? 'Por que escolher',
            'features_subtitle' => $settings['landing_section_features_subtitle'] ?? 'A tecnologia que voce merece, com atendimento de verdade',
            'faq_title' => $settings['landing_section_faq_title'] ?? 'Perguntas Frequentes',
            'faq_subtitle' => $settings['landing_section_faq_subtitle'] ?? 'Tire suas duvidas antes de contratar',
            'contact_title' => $settings['landing_section_contact_title'] ?? 'Fale Conosco',
            'contact_subtitle' => $settings['landing_section_contact_subtitle'] ?? 'Estamos prontos para atender voce por qualquer canal',
            'vod_title' => $settings['landing_section_vod_title'] ?? 'VOD Stream',
            'vod_subtitle' => $settings['landing_section_vod_subtitle'] ?? 'Assista onde e quando quiser, incluido no seu plano',
        ];

        return [
            'name' => $name,
            'logo' => $logo,
            'settings' => $settings,
            'about' => $settings['landing_about'] ?? '',
            'cities' => $cities,
            'phone' => $company?->fiscal('phone') ?? '',
            'cellphone' => $company?->fiscal('cellphone') ?? '',
            'email' => $company?->fiscal('email') ?? '',
            'address' => $company?->fiscal('address') ?? '',
            'city' => $company?->fiscal('city') ?? '',
            'state' => $company?->fiscal('state') ?? '',
            'hours' => $settings['landing_hours'] ?? '',
            'whatsapp' => $settings['landing_whatsapp'] ?? '',
            'facebook' => $settings['landing_facebook'] ?? '',
            'instagram' => $settings['landing_instagram'] ?? '',
            'linkedin' => $settings['landing_linkedin'] ?? '',
            'map_embed' => $settings['landing_map_embed'] ?? '',
            'faq' => $faq,
            'plans' => $residentialPlans,
            'business_plans' => $businessPlans,
            'business_enabled' => ($settings['landing_business_enabled'] ?? '1') === '1',
            'business_title' => $settings['landing_business_title'] ?? 'Internet para Empresas',
            'business_subtitle' => $settings['landing_business_subtitle'] ?? '',
            'business_features' => $businessFeatures,
            'business_cta_label' => $settings['landing_business_cta_label'] ?? 'Falar com especialista',
            'investors_enabled' => ($settings['landing_investors_enabled'] ?? '1') === '1',
            'investor' => [
                'title' => str_replace('{{company_fantasy}}', $name, $settings['landing_investors_title'] ?? 'Invista em uma franquia de internet'),
                'subtitle' => str_replace('{{company_fantasy}}', $name, $settings['landing_investors_subtitle'] ?? ''),
                'benefits' => $investorBenefits,
                'numbers' => $investorNumbers,
                'steps' => $investorSteps,
                'cta_title' => $settings['landing_investors_cta_title'] ?? 'Quer saber mais?',
                'cta_text' => $settings['landing_investors_cta_text'] ?? '',
            ],
            'colors' => $colors,
            'titles' => $section_titles,
            'sac_title' => $settings['landing_sac_title'] ?? 'Central de Atendimento (SAC)',
            'sac_subtitle' => $settings['landing_sac_subtitle'] ?? 'Estamos aqui para ajudar, resolver seu problema e deixar voce conectado.',
        ];
    }
}
