<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\Billing\Models\BillingSetting;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Company;
use Modules\Core\Models\SystemSetting;
use Modules\Core\Models\User;
use Modules\Core\Models\UserGroup;
use Modules\Core\Services\TenantContext;
use Modules\CRM\Models\Plan;
use Tests\TestCase;

class CompanySettingsScopeTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        TenantContext::forget();
        session()->forget(['current_company_id', 'current_branch_id']);
    }

    private function rootCompany(): Company
    {
        return Company::whereNull('parent_id')->orderBy('id')->first();
    }

    private function franchise(): Company
    {
        $root = $this->rootCompany();

        $franchise = Company::create([
            'parent_id' => $root->id,
            'name' => 'Franquia Settings',
            'slug' => 'franquia-settings',
            'is_franchise' => true,
            'is_active' => true,
        ]);

        Branch::create([
            'company_id' => $franchise->id,
            'name' => 'Matriz',
            'is_active' => true,
        ]);

        return $franchise->fresh();
    }

    private function operatorUser(Company $company): User
    {
        $group = UserGroup::firstOrCreate(
            ['slug' => 'operador-settings'],
            ['name' => 'Operador Settings', 'is_active' => true]
        );

        // as rotas de configuracoes exigem a permissao `settings` (Fase 3)
        DB::table('group_permissions')->updateOrInsert(
            ['group_id' => $group->id, 'permission_key' => 'settings'],
            ['granted' => true, 'updated_at' => now()]
        );

        $user = User::create([
            'name' => 'Operador Settings',
            'email' => 'operador-settings-'.uniqid().'@teste.com.br',
            'password' => 'password',
            'user_group_id' => $group->id,
            'is_active' => true,
        ]);

        $user->companies()->attach($company->id);
        $user->branches()->attach(Branch::where('company_id', $company->id)->orderBy('id')->value('id'));

        return $user->fresh();
    }

    public function test_setting_without_override_is_inherited_from_the_root_template(): void
    {
        SystemSetting::set('landing_test_key', 'valor-da-raiz', 'text', 'landing');

        $this->assertSame('valor-da-raiz', SystemSetting::get('landing_test_key'));
        $this->assertNull(
            DB::table('system_settings')->where('key', 'landing_test_key')->value('company_id'),
            'A franqueadora grava no template compartilhado (company_id NULL).'
        );

        $franchise = $this->franchise();
        $this->actingAs($this->operatorUser($franchise));

        $this->assertSame($franchise->id, TenantContext::companyId());
        $this->assertSame('valor-da-raiz', SystemSetting::get('landing_test_key'));
    }

    public function test_franchise_override_does_not_leak_to_the_root(): void
    {
        SystemSetting::set('landing_test_key', 'valor-da-raiz', 'text', 'landing');

        $franchise = $this->franchise();
        $this->actingAs($this->operatorUser($franchise));

        SystemSetting::set('landing_test_key', 'valor-da-franquia', 'text', 'landing');

        $this->assertSame('valor-da-franquia', SystemSetting::get('landing_test_key'));
        $this->assertSame(
            $franchise->id,
            DB::table('system_settings')->where('key', 'landing_test_key')->whereNotNull('company_id')->value('company_id')
        );

        TenantContext::forget();
        session()->forget(['current_company_id', 'current_branch_id']);
        $this->actingAs($this->operatorUser($this->rootCompany()));

        $this->assertSame('valor-da-raiz', SystemSetting::get('landing_test_key'));
    }

    public function test_get_group_merges_template_with_the_company_override(): void
    {
        SystemSetting::set('landing_group_a', 'raiz-a', 'text', 'landing');
        SystemSetting::set('landing_group_b', 'raiz-b', 'text', 'landing');

        $franchise = $this->franchise();
        $this->actingAs($this->operatorUser($franchise));

        $group = SystemSetting::getGroup('landing');

        $this->assertSame('raiz-a', $group['landing_group_a']);
        $this->assertSame('raiz-b', $group['landing_group_b']);

        SystemSetting::set('landing_group_a', 'franquia-a', 'text', 'landing');
        $group = SystemSetting::getGroup('landing');

        $this->assertSame('franquia-a', $group['landing_group_a'], 'Override da franquia tem prioridade.');
        $this->assertSame('raiz-b', $group['landing_group_b'], 'Chave sem override continua herdada.');
    }

    public function test_put_value_creates_the_override_keeping_type_and_group(): void
    {
        SystemSetting::set('landing_enabled', '1', 'text', 'landing');

        $franchise = $this->franchise();
        $this->actingAs($this->operatorUser($franchise));

        SystemSetting::putValue('landing_enabled', '0');

        $row = DB::table('system_settings')
            ->where('key', 'landing_enabled')
            ->where('company_id', $franchise->id)
            ->first();

        $this->assertNotNull($row);
        $this->assertSame('0', $row->value);
        $this->assertSame('landing', $row->group);
        $this->assertSame('0', SystemSetting::get('landing_enabled'));
    }

    public function test_effective_lists_each_key_once_with_the_override_flag(): void
    {
        SystemSetting::set('landing_test_key', 'raiz', 'text', 'landing');

        $root = $this->rootCompany();
        $this->actingAs($this->operatorUser($root));
        $this->assertFalse(SystemSetting::effective('landing')->firstWhere('key', 'landing_test_key')->is_overridden);

        $franchise = $this->franchise();
        $this->actingAs($this->operatorUser($franchise));
        SystemSetting::set('landing_test_key', 'franquia', 'text', 'landing');

        $rows = SystemSetting::effective('landing');
        $this->assertCount(1, $rows->where('key', 'landing_test_key'));
        $this->assertSame('franquia', $rows->firstWhere('key', 'landing_test_key')->value);
        $this->assertTrue($rows->firstWhere('key', 'landing_test_key')->is_overridden);
        $this->assertTrue(SystemSetting::isOverridden('landing_test_key'));
    }

    public function test_billing_settings_fall_back_to_the_root_template(): void
    {
        $root = $this->rootCompany();

        // sem nenhuma linha, cria na companhia do contexto
        $created = BillingSetting::get();
        $this->assertSame($root->id, $created->company_id);

        $created->update(['dias_geracao_fatura' => 7]);
        $created->company_id = null;
        $created->save();

        $franchise = $this->franchise();
        $this->actingAs($this->operatorUser($franchise));

        $this->assertSame(7, BillingSetting::get()->dias_geracao_fatura, 'Franquia sem config cai no template da raiz.');

        BillingSetting::create([
            'company_id' => $franchise->id,
            'dias_bloqueio' => 3,
            'dias_geracao_fatura' => 15,
            'bloqueio_automatico' => true,
            'plano_minimo_habilitado' => true,
            'plano_minimo_kbps' => 512,
            'plano_minimo_upload_kbps' => 128,
        ]);

        $this->assertSame(15, BillingSetting::get()->dias_geracao_fatura);
        $this->assertSame(
            (int) SystemSetting::get('block_grace_days', 3),
            BillingSetting::get()->dias_bloqueio,
            'Precedencia existente: o grupo block sobrepoe dias_bloqueio.'
        );
    }

    public function test_company_fiscal_data_inherits_from_the_parent_company(): void
    {
        $root = $this->rootCompany();
        $root->update([
            'legal_name' => 'Itamidia Telecomunicacoes LTDA',
            'fantasy_name' => 'Itamidia Telecom',
            'document' => '08.470.613/0001-02',
            'phone' => '(98) 3000-0000',
        ]);

        $franchise = $this->franchise();

        $this->assertSame('08.470.613/0001-02', $franchise->fiscal('document'));
        $this->assertSame('Itamidia Telecom', $franchise->displayName());
        $this->assertSame('Itamidia Telecomunicacoes LTDA', $franchise->legalName());

        $franchise->update(['document' => '99.999.999/0001-99', 'fantasy_name' => 'Itamidia Dom Pedro']);

        $this->assertSame('99.999.999/0001-99', $franchise->fiscal('document'));
        $this->assertSame('Itamidia Dom Pedro', $franchise->displayName());
        $this->assertSame(
            '(98) 3000-0000',
            $franchise->fiscal('phone'),
            'Telefone continua herdado da franqueadora.'
        );
    }

    public function test_settings_page_saves_company_fields_and_tenant_overrides(): void
    {
        SystemSetting::set('landing_hero_title', 'Titulo da Raiz', 'text', 'landing');

        $franchise = $this->franchise();
        $this->actingAs($this->operatorUser($franchise));

        $this->get(route('core.settings.index'))
            ->assertOk()
            ->assertSee('herdado da franqueadora')
            ->assertSee('Titulo da Raiz');

        $this->put(route('core.settings.update'), [
            'settings' => [
                'company_name' => 'Franquia Telecom LTDA',
                'company_fantasy' => 'Franquia Telecom',
                'company_document' => '11.222.333/0001-44',
                'company_city' => 'Dom Pedro',
                'landing_hero_title' => 'Titulo da Franquia',
            ],
        ])->assertRedirect();

        $franchise->refresh();

        $this->assertSame('Franquia Telecom LTDA', $franchise->legal_name);
        $this->assertSame('Franquia Telecom', $franchise->displayName());
        $this->assertSame('11.222.333/0001-44', $franchise->document);
        $this->assertSame('Dom Pedro', $franchise->city);

        $this->assertSame('Titulo da Franquia', SystemSetting::get('landing_hero_title'));

        TenantContext::forget();
        session()->forget(['current_company_id', 'current_branch_id']);
        $this->actingAs($this->operatorUser($this->rootCompany()));

        $this->assertSame('Titulo da Raiz', SystemSetting::get('landing_hero_title'));
    }

    public function test_public_pages_use_the_company_of_the_context(): void
    {
        $root = $this->rootCompany();
        $root->update(['fantasy_name' => 'Marca da Raiz']);

        SystemSetting::set('landing_enabled', '1', 'text', 'landing');
        SystemSetting::set('block_page_html', '<p>{{provider_name}}</p>', 'textarea', 'block');

        $this->get('/')->assertOk()->assertSee('Marca da Raiz');

        $this->get(route('block.page', ['preview' => 1]))
            ->assertOk()
            ->assertSee('Marca da Raiz');
    }

    public function test_landing_splits_plans_by_segment_and_exposes_the_investors_page(): void
    {
        $residential = Plan::create([
            'name' => 'Residencial 300M',
            'slug' => 'residencial-300m',
            'download_speed' => 300_000_000,
            'upload_speed' => 150_000_000,
            'price' => 99.9,
            'billing_cycle' => 'monthly',
            'is_active' => true,
            'segment' => 'residencial',
        ]);

        $business = Plan::create([
            'name' => 'Corporativo Dedicado 1G',
            'slug' => 'corporativo-dedicado-1g',
            'download_speed' => 1_000_000_000,
            'upload_speed' => 500_000_000,
            'price' => 899.9,
            'billing_cycle' => 'monthly',
            'is_active' => true,
            'segment' => 'empresarial',
        ]);

        SystemSetting::set('landing_enabled', '1', 'text', 'landing');
        SystemSetting::set('landing_investors_enabled', '1', 'text', 'landing');
        SystemSetting::set('landing_business_enabled', '1', 'text', 'landing');
        SystemSetting::set('landing_investors_title', 'Invista na Marca da Raiz', 'text', 'landing');
        SystemSetting::set('landing_investors_numbers', '100% | Fibra optica', 'textarea', 'landing');
        SystemSetting::set('landing_investors_steps', '1. Conversa inicial - fale com o time comercial', 'textarea', 'landing');

        $this->get('/')
            ->assertOk()
            ->assertSee($residential->name)
            ->assertSee($business->name)
            ->assertSee('Para Sua Empresa')
            ->assertSee('Para Voce')
            ->assertSee(route('landing.investors'));

        $this->get(route('landing.investors'))
            ->assertOk()
            ->assertSee('Invista na Marca da Raiz')
            ->assertSee('Fibra optica')
            ->assertSee('fale com o time comercial');

        SystemSetting::set('landing_investors_enabled', '0', 'text', 'landing');

        $this->get(route('landing.investors'))->assertRedirect(route('landing.index'));
        $this->get('/')->assertOk()->assertDontSee(route('landing.investors'));
    }
}
