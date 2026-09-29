<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Company;
use Modules\Core\Models\GroupPermission;
use Modules\Core\Models\LandingBanner;
use Modules\Core\Models\UserGroup;
use Modules\Core\Services\PublicTenantResolver;
use Modules\Core\Services\TenantContext;
use Modules\CRM\Models\Plan;
use Tests\TestCase;

class PublicTenantResolutionTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        TenantContext::forget();
        PublicTenantResolver::forget();
        session()->forget(['current_company_id', 'current_branch_id']);
    }

    public function test_host_sem_empresa_cadastrada_cai_na_matriz(): void
    {
        $root = $this->rootCompany();

        $response = $this->get('http://desconhecido.example.com/');

        $response->assertOk();
        $this->assertNull(PublicTenantResolver::companyId());
        $this->assertSame($root->id, TenantContext::companyId());
    }

    public function test_host_cadastrado_aponta_a_landing_para_a_franquia(): void
    {
        $franchise = $this->franchise('campinas.exemplo.com.br');

        $this->get('http://campinas.exemplo.com.br/')->assertOk();

        $this->assertSame($franchise->id, PublicTenantResolver::companyId());
        $this->assertSame($franchise->id, TenantContext::companyId());
    }

    public function test_host_com_www_e_caixa_alta_tambem_resolve(): void
    {
        $franchise = $this->franchise('Sorocaba.Exemplo.com.br');

        $this->get('http://www.sorocaba.exemplo.com.br/sac')->assertOk();

        $this->assertSame($franchise->id, TenantContext::companyId());
    }

    public function test_localhost_nao_e_tratado_como_dominio_de_franquia(): void
    {
        $root = $this->rootCompany();

        $this->get('http://localhost/')->assertOk();

        $this->assertNull(PublicTenantResolver::companyId());
        $this->assertSame($root->id, TenantContext::companyId());
    }

    public function test_empresa_inativa_nao_e_servida_pelo_host(): void
    {
        $franchise = $this->franchise('inativa.exemplo.com.br');
        $franchise->update(['is_active' => false]);

        $this->get('http://inativa.exemplo.com.br/')->assertOk();

        $this->assertNull(PublicTenantResolver::companyId());
    }

    public function test_banners_da_franquia_substituem_os_da_matriz(): void
    {
        $root = $this->rootCompany();
        $franchise = $this->franchise('banners.exemplo.com.br');

        LandingBanner::create(['company_id' => $root->id, 'title' => 'Banner da Matriz', 'is_active' => true]);
        LandingBanner::create(['company_id' => $franchise->id, 'title' => 'Banner da Franquia', 'is_active' => true]);

        $response = $this->get('http://banners.exemplo.com.br/');

        $response->assertOk();
        $response->assertSee('Banner da Franquia');
        $response->assertDontSee('Banner da Matriz');
    }

    public function test_franquia_sem_banners_usa_a_marca_da_matriz(): void
    {
        $root = $this->rootCompany();
        $this->franchise('sem-banner.exemplo.com.br');

        LandingBanner::create(['company_id' => $root->id, 'title' => 'Banner da Matriz', 'is_active' => true]);

        $response = $this->get('http://sem-banner.exemplo.com.br/');

        $response->assertOk();
        $response->assertSee('Banner da Matriz');
    }

    public function test_banner_inativo_da_franquia_nao_aparece(): void
    {
        $root = $this->rootCompany();
        $franchise = $this->franchise('inativo.exemplo.com.br');

        LandingBanner::create(['company_id' => $root->id, 'title' => 'Banner da Matriz', 'is_active' => true]);
        LandingBanner::create(['company_id' => $franchise->id, 'title' => 'Banner Desligado', 'is_active' => false]);

        $response = $this->get('http://inativo.exemplo.com.br/');

        $response->assertOk();
        $response->assertDontSee('Banner Desligado');
    }

    public function test_banner_de_outra_franquia_nao_vaza(): void
    {
        $root = $this->rootCompany();
        $this->franchise('banners-b.exemplo.com.br');
        $outra = $this->franchise('banners-c.exemplo.com.br');

        LandingBanner::create(['company_id' => $root->id, 'title' => 'Banner da Matriz', 'is_active' => true]);
        LandingBanner::create(['company_id' => $outra->id, 'title' => 'Banner Concorrente', 'is_active' => true]);

        $response = $this->get('http://banners-b.exemplo.com.br/');

        $response->assertOk();
        $response->assertDontSee('Banner Concorrente');
    }

    public function test_usuario_autenticado_ignora_o_host_e_mantem_o_tenant_escolhido(): void
    {
        $this->franchise('autenticado.exemplo.com.br');
        $user = $this->operator();

        $this->actingAs($user)->get('http://autenticado.exemplo.com.br/')->assertOk();

        $this->assertNull(PublicTenantResolver::companyId());
        $this->assertSame($this->rootCompany()->id, TenantContext::companyId());
    }

    public function test_gestao_de_banners_lista_apenas_o_tenant_atual(): void
    {
        $root = $this->rootCompany();
        $franchise = $this->franchise('gestao.exemplo.com.br');

        LandingBanner::create(['company_id' => $root->id, 'title' => 'Banner da Matriz', 'is_active' => true]);
        LandingBanner::create(['company_id' => $franchise->id, 'title' => 'Banner da Franquia', 'is_active' => true]);

        $response = $this->actingAs($this->operator($franchise))->get(route('core.banners.index'));

        $response->assertOk();
        $response->assertSee('Banner da Franquia');
        $response->assertDontSee('Banner da Matriz');
    }

    public function test_gestao_de_banners_bloqueia_edicao_de_outro_tenant(): void
    {
        $root = $this->rootCompany();
        $rootBanner = LandingBanner::create([
            'company_id' => $root->id,
            'title' => 'Banner da Matriz',
            'is_active' => true,
        ]);

        $operator = $this->operator($this->franchise('intrusa.exemplo.com.br'));

        $this->actingAs($operator)
            ->get(route('core.banners.edit', $rootBanner->id))
            ->assertNotFound();

        $this->actingAs($operator)
            ->put(route('core.banners.update', $rootBanner->id), ['title' => 'Sequestrado'])
            ->assertNotFound();

        $this->actingAs($operator)
            ->delete(route('core.banners.destroy', $rootBanner->id))
            ->assertNotFound();

        $this->assertSame('Banner da Matriz', $rootBanner->fresh()->title);
    }

    public function test_banner_criado_pela_gestao_pertence_ao_tenant_atual(): void
    {
        $franchise = $this->franchise('criacao.exemplo.com.br');

        $this->actingAs($this->operator($franchise))
            ->post(route('core.banners.store'), [
                'title' => 'Banner Novo',
                'is_active' => true,
            ])
            ->assertRedirect(route('core.banners.index'));

        $this->assertSame($franchise->id, LandingBanner::where('title', 'Banner Novo')->value('company_id'));
    }

    public function test_ordenacao_troca_apenas_entre_banners_do_mesmo_tenant(): void
    {
        $root = $this->rootCompany();
        $franchise = $this->franchise('ordem.exemplo.com.br');
        $operator = $this->operator($franchise);

        $first = LandingBanner::create([
            'company_id' => $root->id, 'title' => 'Primeiro da Matriz', 'sort_order' => 1, 'is_active' => true,
        ]);
        $second = LandingBanner::create([
            'company_id' => $franchise->id, 'title' => 'Primeiro da Franquia', 'sort_order' => 1, 'is_active' => true,
        ]);

        $this->actingAs($operator)
            ->post(route('core.banners.move', [$second->id, 'down']))
            ->assertSessionHasNoErrors();

        $this->assertSame(1, $first->fresh()->sort_order);
        $this->assertSame(1, $second->fresh()->sort_order);
    }

    public function test_planos_de_outra_franquia_nao_aparecem_na_landing(): void
    {
        $root = $this->rootCompany();
        $this->franchise('planos.exemplo.com.br');
        $outra = $this->franchise('concorrente.exemplo.com.br');

        $this->plan($root, 'Plano da Matriz', 'residencial');
        $this->plan($outra, 'Plano Concorrente', 'residencial');

        $response = $this->get('http://planos.exemplo.com.br/');

        $response->assertOk();
        $response->assertSee('Plano da Matriz');
        $response->assertDontSee('Plano Concorrente');
    }

    public function test_todas_as_filiais_exibem_o_catalogo_unico_da_matriz(): void
    {
        $root = $this->rootCompany();
        $franchise = $this->franchise('catalogo.exemplo.com.br');

        $this->plan($root, 'Plano da Matriz', 'residencial');
        $this->plan($franchise, 'Plano da Franquia', 'residencial');

        // catalogo unico: nem a propria franquia usa o plano proprio
        $response = $this->get('http://catalogo.exemplo.com.br/');

        $response->assertOk();
        $response->assertSee('Plano da Matriz');
        $response->assertDontSee('Plano da Franquia');
    }

    public function test_gestao_de_planos_bloqueia_acesso_a_plano_de_outro_tenant(): void
    {
        $rootPlan = $this->plan($this->rootCompany(), 'Plano da Matriz', 'residencial');
        $franchise = $this->franchise('plano-indisponivel.exemplo.com.br');
        $operator = $this->operator($franchise);

        $this->actingAs($operator)
            ->get(route('crm.plans.edit', $rootPlan->id))
            ->assertNotFound();

        $this->actingAs($operator)
            ->put(route('crm.plans.update', $rootPlan->id), ['name' => 'Sequestrado'])
            ->assertNotFound();

        $this->actingAs($operator)
            ->delete(route('crm.plans.destroy', $rootPlan->id))
            ->assertNotFound();

        $this->assertSame('Plano da Matriz', $rootPlan->fresh()->name);
        $this->assertNull($rootPlan->fresh()->deleted_at);
    }

    public function test_gestao_de_planos_lista_apenas_o_tenant_atual(): void
    {
        $root = $this->rootCompany();
        $franchise = $this->franchise('lista-planos.exemplo.com.br');

        $this->plan($root, 'Plano da Matriz', 'residencial');
        $this->plan($franchise, 'Plano da Franquia', 'residencial');

        $response = $this->actingAs($this->operator($franchise))->get(route('crm.plans.index'));

        $response->assertOk();
        $response->assertSee('Plano da Franquia');
        $response->assertDontSee('Plano da Matriz');
    }

    public function test_dominio_duplicado_e_rejeitado(): void
    {
        $this->franchise('ocupado.exemplo.com.br');

        $this->actingAs($this->operator())->post(route('core.companies.store'), [
            'name' => 'Conflitante',
            'slug' => 'conflitante-'.uniqid(),
            'domain' => 'ocupado.exemplo.com.br',
        ])->assertSessionHasErrors('domain');
    }

    public function test_dominio_com_formato_invalido_e_rejeitado(): void
    {
        $this->actingAs($this->operator())->post(route('core.companies.store'), [
            'name' => 'Dominio Ruim',
            'slug' => 'dominio-ruim-'.uniqid(),
            'domain' => 'http://nao-e-dominio.com/rota',
        ])->assertSessionHasErrors('domain');
    }

    private function rootCompany(): Company
    {
        return Company::whereNull('parent_id')->orderBy('id')->firstOrFail();
    }

    private function franchise(string $domain): Company
    {
        $franchise = Company::create([
            'parent_id' => $this->rootCompany()->id,
            'name' => 'Franquia '.uniqid(),
            'slug' => 'franquia-'.uniqid(),
            'domain' => $domain,
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

    private function plan(Company $company, string $name, string $segment): Plan
    {
        return Plan::create([
            'company_id' => $company->id,
            'name' => $name,
            'slug' => Str::slug($name).'-'.uniqid(),
            'segment' => $segment,
            'download_speed' => 100,
            'upload_speed' => 100,
            'price' => 99.90,
            'billing_cycle' => 'monthly',
            'is_active' => true,
        ]);
    }

    private function operator(?Company $company = null): User
    {
        $company ??= $this->rootCompany();

        $group = UserGroup::create([
            'name' => 'Operador Dominios',
            'slug' => 'operador-dominios-'.uniqid(),
            'is_active' => true,
        ]);

        foreach (array_keys(GroupPermission::MENU_PERMISSIONS()) as $key) {
            DB::table('group_permissions')->updateOrInsert(
                ['group_id' => $group->id, 'permission_key' => $key],
                ['granted' => true, 'updated_at' => now()]
            );
        }

        $user = User::create([
            'name' => 'Operador Dominios',
            'email' => 'operador-dominios-'.uniqid().'@teste.local',
            'password' => bcrypt('secret123'),
            'user_group_id' => $group->id,
            'is_active' => true,
        ]);

        $user->companies()->attach($company->id);
        $user->branches()->attach(
            Branch::where('company_id', $company->id)->orderBy('id')->value('id')
        );

        return $user->fresh();
    }
}
