<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Company;
use Modules\Core\Models\UserGroup;
use Modules\CRM\Models\Client;
use Tests\TestCase;

/**
 * Portal do franqueado.
 *
 * O grupo `franqueados` e global; o que separa uma franquia da outra e o
 * vinculo do usuario com a empresa. Estes testes travam justamente essa frase:
 * dois usuarios no mesmo grupo, em franquias diferentes, nunca se enxergam.
 */
class FranchiseePortalTest extends TestCase
{
    public function test_migration_cria_o_grupo_global_franqueados_com_permissao_do_painel(): void
    {
        $group = UserGroup::where('slug', 'franqueados')->first();

        $this->assertNotNull($group, 'O grupo franqueados deve existir apos as migrations.');
        $this->assertTrue($group->is_active);

        $granted = DB::table('group_permissions')
            ->where('group_id', $group->id)
            ->where('granted', true)
            ->pluck('permission_key')
            ->all();

        $this->assertContains('franchisees', $granted);
        $this->assertNotContains('settings', $granted, 'Franqueado nao pode administrar a plataforma.');
    }

    public function test_franqueado_entra_no_painel_e_ve_a_propria_franquia(): void
    {
        $franquia = $this->franchise('Franquia Alpha', 'alpha');
        $user = $this->franchiseeUser($franquia);

        $this->actingAs($user)
            ->get(route('core.franchisee.index'))
            ->assertOk()
            ->assertSee('Painel da Franquia')
            ->assertSee('Franquia Alpha');
    }

    public function test_painel_do_franqueado_nao_traz_clientes_de_outra_franquia(): void
    {
        $minha = $this->franchise('Franquia Alpha', 'alpha');
        $outra = $this->franchise('Franquia Beta', 'beta');

        $this->makeClient($minha, 'Cliente Alpha', '11111111111');
        $this->makeClient($outra, 'Cliente Beta', '22222222222');

        $response = $this->actingAs($this->franchiseeUser($minha))
            ->get(route('core.franchisee.clients'));

        $response->assertOk()
            ->assertSee('Cliente Alpha')
            ->assertDontSee('Cliente Beta');
    }

    public function test_franqueados_de_franquias_diferentes_nao_se_enxergam(): void
    {
        // Mesmo grupo, empresas distintas: o grupo nao pode ser o que isola.
        $group = $this->franqueadosGroup();

        $alpha = $this->franchise('Franquia Alpha', 'alpha');
        $beta = $this->franchise('Franquia Beta', 'beta');

        $this->makeClient($alpha, 'Cliente Alpha', '33333333333');
        $this->makeClient($beta, 'Cliente Beta', '44444444444');

        $userAlpha = $this->userInGroup($group, $alpha, 'alpha@teste.local');
        $userBeta = $this->userInGroup($group, $beta, 'beta@teste.local');

        $this->actingAs($userAlpha)->get(route('core.franchisee.clients'))
            ->assertSee('Cliente Alpha')->assertDontSee('Cliente Beta');

        $this->actingAs($userBeta)->get(route('core.franchisee.clients'))
            ->assertSee('Cliente Beta')->assertDontSee('Cliente Alpha');
    }

    public function test_franqueado_sem_empresa_vinculada_ve_lista_vazia_e_nao_derruba_a_tela(): void
    {
        $franquia = $this->franchise('Franquia Alpha', 'alpha');
        $this->makeClient($franquia, 'Cliente Alpha', '55555555555');

        // Grupo franqueados, mas sem linha em company_user.
        $orphan = $this->userInGroup($this->franqueadosGroup(), null, 'sem-empresa@teste.local');

        $this->actingAs($orphan)
            ->get(route('core.franchisee.clients'))
            ->assertOk()
            ->assertDontSee('Cliente Alpha');
    }

    public function test_busca_e_filtro_de_status_na_pagina_do_franqueado(): void
    {
        $franquia = $this->franchise('Franquia Alpha', 'alpha');
        $this->makeClient($franquia, 'Cliente Buscavel', '66666666666');
        $this->makeClient($franquia, 'Cliente Inativo', '77777777777', 'inactive');

        $user = $this->franchiseeUser($franquia);

        $this->actingAs($user)->get(route('core.franchisee.clients', ['search' => 'Buscavel']))
            ->assertSee('Cliente Buscavel')
            ->assertDontSee('Cliente Inativo');

        $this->actingAs($user)->get(route('core.franchisee.clients', ['status' => 'inactive']))
            ->assertSee('Cliente Inativo')
            ->assertDontSee('Cliente Buscavel');
    }

    public function test_quem_nao_tem_a_permissao_franchisees_recebe_403(): void
    {
        $franquia = $this->franchise('Franquia Alpha', 'alpha');

        $grupoSemPainel = UserGroup::create([
            'name' => 'Sem Painel',
            'slug' => 'sem-painel',
            'is_active' => true,
        ]);

        $user = $this->userInGroup($grupoSemPainel, $franquia, 'sem-painel@teste.local');

        $this->actingAs($user)->get(route('core.franchisee.index'))->assertForbidden();
        $this->actingAs($user)->get(route('core.franchisee.clients'))->assertForbidden();
    }

    public function test_visitante_e_redirecionado_para_o_login(): void
    {
        $this->get(route('core.franchisee.index'))->assertRedirect('/login');
        $this->get(route('core.franchisee.clients'))->assertRedirect('/login');
    }

    public function test_menu_lateral_mostra_o_painel_somente_com_a_permissao(): void
    {
        $franquia = $this->franchise('Franquia Alpha', 'alpha');

        $this->actingAs($this->franchiseeUser($franquia))
            ->get(route('core.franchisee.index'))
            ->assertOk()
            ->assertSee('Painel da Franquia');
    }

    public function test_franqueado_faz_login_e_cai_no_dashboard(): void
    {
        $franquia = $this->franchise('Franquia Alpha', 'alpha');
        $user = $this->userInGroup($this->franqueadosGroup(), $franquia, 'login@teste.local');
        $user->forceFill(['password' => bcrypt('senha12345')])->save();

        $response = $this->post(route('login'), [
            'email' => 'login@teste.local',
            'password' => 'senha12345',
        ]);

        // O destino e o dashboard de todo mundo. Ele so funciona porque o
        // grupo tem `dashboard`: sem essa chave o login daria certo e a
        // pagina seguinte responderia 403.
        $response->assertRedirect(route('crm.dashboard'));

        $this->actingAs($user)->get(route('crm.dashboard'))->assertOk();
    }

    public function test_primeiro_acesso_vai_para_definir_a_senha(): void
    {
        $franquia = $this->franchise('Franquia Alpha', 'alpha');
        $user = $this->userInGroup($this->franqueadosGroup(), $franquia, 'convite@teste.local');
        $user->forceFill([
            'password' => bcrypt('senha12345'),
            'must_change_password' => true,
        ])->save();

        $this->post(route('login'), [
            'email' => 'convite@teste.local',
            'password' => 'senha12345',
        ])->assertRedirect(route('password.edit'));
    }

    public function test_login_invalido_nao_autentica(): void
    {
        $this->post(route('login'), [
            'email' => 'ninguem@teste.local',
            'password' => 'errada',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    /* ------------------------------------------------------------------ */

    private function franqueadosGroup(): UserGroup
    {
        return UserGroup::where('slug', 'franqueados')->firstOrFail();
    }

    private function rootCompany(): Company
    {
        return Company::whereNull('parent_id')->orderBy('id')->firstOrFail();
    }

    private function franchise(string $name, string $slug): Company
    {
        $franchise = Company::create([
            'parent_id' => $this->rootCompany()->id,
            'name' => $name,
            'slug' => $slug,
            'is_franchise' => true,
            'is_active' => true,
        ]);

        Branch::create([
            'company_id' => $franchise->id,
            'name' => 'Matriz',
            'is_active' => true,
        ]);

        return $franchise;
    }

    private function franchiseeUser(Company $company): User
    {
        return $this->userInGroup($this->franqueadosGroup(), $company, 'franqueado-'.uniqid().'@teste.local');
    }

    private function userInGroup(UserGroup $group, ?Company $company, string $email): User
    {
        $user = User::create([
            'name' => 'Franqueado Teste',
            'email' => $email,
            'password' => 'password',
            'user_group_id' => $group->id,
            'role' => $group->slug,
            'is_active' => true,
        ]);

        if ($company) {
            $user->companies()->attach($company->id);
            $branch = Branch::where('company_id', $company->id)->orderBy('id')->first();
            $user->branches()->attach($branch->id);
        }

        return $user->fresh();
    }

    private function makeClient(Company $company, string $name, string $document, string $status = 'active'): Client
    {
        return Client::create([
            'company_id' => $company->id,
            'branch_id' => Branch::where('company_id', $company->id)->orderBy('id')->value('id'),
            'name' => $name,
            'document' => $document,
            'type' => 'individual',
            'status' => $status,
        ]);
    }
}
