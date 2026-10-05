<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Company;
use Modules\Core\Models\UserGroup;
use Modules\Core\Services\TenantContext;
use Modules\PortalInfra\Models\CaixaEmenda;
use Modules\PortalInfra\Models\Cto;
use Modules\PortalInfra\Models\FtthProject;
use Tests\TestCase;

/**
 * O portal do tecnico usa o guard `technician`, que nao passa pelo guard `web`.
 * Se o `TenantContext` nao enxergar esse guard, todo `scoped()` do portal cai no
 * ramo anonimo e resolve para a empresa raiz — ou seja, nao corta nada. Estes
 * testes existem para travar esse comportamento.
 */
class TechnicianPortalScopeTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        TenantContext::forget();
        session()->forget(['current_company_id', 'current_branch_id']);
    }

    public function test_tecnico_da_loja_nao_acessa_a_rede_da_matriz(): void
    {
        $root = $this->rootCompany();
        $matriz = $this->matrixBranch($root);
        $loja = $this->branch('Filial Dom Pedro MA', $root);

        $redeMatriz = $this->project($root, $matriz, 'Rede da Sede', 'SED-001');
        $redeLoja = $this->project($root, $loja, 'Rede do Dom Pedro', 'DOM-001');

        $tecnico = $this->technician($root, $loja, ['ftth']);

        // O contexto precisa enxergar o guard `technician`.
        $this->actingAs($tecnico, 'technician');
        TenantContext::forget();
        $this->assertSame($root->id, TenantContext::companyId());
        $this->assertSame($loja->id, TenantContext::branchId());

        // A listagem traz a rede dele e esconde a da matriz.
        $this->get(route('technician.portal.ftth'))
            ->assertOk()
            ->assertSee('Rede do Dom Pedro')
            ->assertDontSee('Rede da Sede');

        // Ler por id da matriz responde 404, e nao 200 com o desenho alheio.
        $this->get(route('technician.portal.ftth.ctos.show', $redeMatriz->cto->id))->assertNotFound();
        $this->get(route('technician.portal.ftth.ctos.show', $redeLoja->cto->id))->assertOk();

        // E o mesmo vale para escrever: nada de ativar a CTO de outra filial.
        $this->post(route('technician.portal.ftth.ctos.activate', $redeMatriz->cto->id))->assertNotFound();
        $this->post(route('technician.portal.ftth.ctos.activate', $redeLoja->cto->id))
            ->assertRedirect(route('technician.portal.ftth.ctos.show', $redeLoja->cto->id));

        $this->assertSame('planned', $redeMatriz->cto->fresh()->status);
        $this->assertSame('active', $redeLoja->cto->fresh()->status);
    }

    public function test_tecnico_da_loja_nao_altera_caixa_nem_fusao_da_matriz(): void
    {
        $root = $this->rootCompany();
        $matriz = $this->matrixBranch($root);
        $loja = $this->branch('Filial Centro', $root);

        $redeMatriz = $this->project($root, $matriz, 'Rede Sede', 'SED-002');
        $redeLoja = $this->project($root, $loja, 'Rede Centro', 'CEN-002');

        $tecnico = $this->technician($root, $loja, ['ftth']);
        $this->actingAs($tecnico, 'technician');

        $this->get(route('technician.portal.ftth.caixas.show', $redeMatriz->caixa->id))->assertNotFound();
        $this->get(route('technician.portal.ftth.caixas.show', $redeLoja->caixa->id))->assertOk();

        $fusaoAlheia = $redeMatriz->caixa->fusions()->firstOrFail();
        $fusaoDele = $redeLoja->caixa->fusions()->firstOrFail();

        $this->put(route('technician.portal.ftth.fusions.update', $fusaoAlheia), [
            'olt_port' => '0/0/1/99',
        ])->assertNotFound();

        $this->assertNotSame('0/0/1/99', $fusaoAlheia->fresh()->olt_port);

        // E o processo normal continua funcionando na rede dele.
        $this->put(route('technician.portal.ftth.fusions.update', $fusaoDele), [
            'olt_port' => '0/0/1/1',
        ])->assertRedirect();

        $this->assertSame('0/0/1/1', $fusaoDele->fresh()->olt_port);
    }

    public function test_tecnico_da_loja_nao_conclui_fusao_da_matriz(): void
    {
        $root = $this->rootCompany();
        $matriz = $this->matrixBranch($root);
        $loja = $this->branch('Filial Norte', $root);

        $redeMatriz = $this->project($root, $matriz, 'Rede Sede', 'SED-003');
        $redeLoja = $this->project($root, $loja, 'Rede Norte', 'NOR-003');

        $tecnico = $this->technician($root, $loja, ['ftth']);
        $this->actingAs($tecnico, 'technician');

        $fusaoAlheia = $redeMatriz->caixa->fusions()->firstOrFail();
        $fusaoDele = $redeLoja->caixa->fusions()->firstOrFail();

        $this->post(route('technician.portal.ftth.fusions.done', $fusaoAlheia))->assertNotFound();
        $this->assertNotSame('done', $fusaoAlheia->fresh()->status);

        $this->post(route('technician.portal.ftth.fusions.done', $fusaoDele))
            ->assertRedirect(route('technician.portal.ftth.caixas.show', $redeLoja->caixa->id));
        $this->assertSame('done', $fusaoDele->fresh()->status);
    }

    public function test_notas_da_matriz_nao_sao_gravaveis_pelo_tecnico_da_loja(): void
    {
        $root = $this->rootCompany();
        $matriz = $this->matrixBranch($root);
        $loja = $this->branch('Filial Sul', $root);

        $redeMatriz = $this->project($root, $matriz, 'Rede Sede', 'SED-004');
        $redeLoja = $this->project($root, $loja, 'Rede Sul', 'SUL-004');

        $tecnico = $this->technician($root, $loja, ['ftth']);
        $this->actingAs($tecnico, 'technician');

        $this->post(route('technician.portal.ftth.ctos.notes', $redeMatriz->cto->id), [
            'technician_notes' => 'invadido',
        ])->assertNotFound();

        $this->assertNotSame('invadido', $redeMatriz->cto->fresh()->technician_notes);

        $this->post(route('technician.portal.ftth.ctos.notes', $redeLoja->cto->id), [
            'technician_notes' => 'fusao 12 concluida com sucesso',
        ])->assertRedirect();

        $this->assertSame('fusao 12 concluida com sucesso', $redeLoja->cto->fresh()->technician_notes);
    }

    public function test_crud_de_tecnico_recusa_usuario_de_outro_grupo(): void
    {
        $root = $this->rootCompany();
        $tecnico = $this->technician($root, $this->matrixBranch($root), ['technicians']);

        $superadmin = $this->userWithGroup('superadmin', 'admin-super');
        $comum = $this->userWithGroup('operador', 'operador-comum');

        // Quem tem `group.permission:technicians` chega no formulario, mas nao
        // consegue redigir, desativar nem apagar ninguem de outro grupo: antes
        // bastava trocar o id na URL.
        $this->actingAs($tecnico)->get(route('crm.technicians.edit', $superadmin->id))->assertNotFound();
        $this->actingAs($tecnico)->get(route('crm.technicians.edit', $comum->id))->assertNotFound();

        $this->actingAs($tecnico)->put(route('crm.technicians.update', $superadmin->id), [
            'name' => 'Superadmin Hackeado',
            'email' => $superadmin->email,
        ])->assertNotFound();

        $this->assertSame('Admin-super', $superadmin->fresh()->name);

        $this->actingAs($tecnico)->delete(route('crm.technicians.destroy', $superadmin->id))->assertNotFound();
        $this->assertNull($superadmin->fresh()->deleted_at);

        // E um tecnico de verdade continua editavel.
        $this->actingAs($tecnico)->get(route('crm.technicians.edit', $tecnico->id))->assertOk();
    }

    public function test_tecnico_nao_remove_a_propria_conta(): void
    {
        $root = $this->rootCompany();
        $tecnico = $this->technician($root, $this->matrixBranch($root), ['technicians']);

        $this->actingAs($tecnico)
            ->delete(route('crm.technicians.destroy', $tecnico->id))
            ->assertRedirect();

        $this->assertNull($tecnico->fresh()->deleted_at);
    }

    public function test_rota_show_de_tecnico_nao_existe_mais(): void
    {
        // O resource gerava `crm.technicians.show` para um metodo que nunca
        // existiu, entao clicar em "ver" dava 500 em vez de uma pagina.
        $this->assertFalse(Route::has('crm.technicians.show'));

        $root = $this->rootCompany();
        $tecnico = $this->technician($root, $this->matrixBranch($root), ['technicians']);

        $this->actingAs($tecnico)->get(route('crm.technicians.index'))->assertOk();
    }

    private function rootCompany(): Company
    {
        return Company::whereNull('parent_id')->orderBy('id')->firstOrFail();
    }

    private function matrixBranch(Company $company): Branch
    {
        return Branch::where('company_id', $company->id)->orderBy('id')->firstOrFail();
    }

    private function branch(string $name, Company $company): Branch
    {
        return Branch::create([
            'company_id' => $company->id,
            'name' => $name,
            'is_active' => true,
        ]);
    }

    private function technician(Company $company, Branch $branch, array $permissions = []): User
    {
        $group = UserGroup::firstOrCreate(
            ['slug' => 'tecnico'],
            ['name' => 'Tecnico', 'is_active' => true]
        );

        foreach ($permissions as $permission) {
            DB::table('group_permissions')->updateOrInsert(
                ['group_id' => $group->id, 'permission_key' => $permission],
                ['granted' => true, 'updated_at' => now()]
            );
        }

        $user = User::create([
            'name' => 'Tecnico '.$branch->name,
            'email' => 'tec-'.uniqid().'@teste.local',
            'password' => bcrypt('secret123'),
            'user_group_id' => $group->id,
            'is_active' => true,
        ]);

        $user->companies()->attach($company->id);
        $user->branches()->attach($branch->id);

        return $user->fresh();
    }

    private function userWithGroup(string $slug, string $label): User
    {
        $group = UserGroup::firstOrCreate(
            ['slug' => $slug],
            ['name' => ucfirst($slug), 'is_active' => true]
        );

        return User::create([
            'name' => ucfirst($label),
            'email' => $label.'-'.uniqid().'@teste.local',
            'password' => bcrypt('secret123'),
            'user_group_id' => $group->id,
            'is_active' => true,
        ]);
    }

    /**
     * Rede minima de uma filial: projeto + CTO + caixa de emenda + uma fusao
     * pendente. Devolve tudo junto para o teste poder atacar cada id.
     */
    public function test_mapa_do_tecnico_mostra_so_a_rede_da_filial_dele(): void
    {
        $root = $this->rootCompany();
        $matriz = $this->matrixBranch($root);
        $loja = $this->branch('Filial Mangueirinha', $root);

        $redeMatriz = $this->project($root, $matriz, 'Rede da Sede', 'SED-MAP');
        $redeLoja = $this->project($root, $loja, 'Rede da Mangueirinha', 'MAN-MAP');

        $tecnico = $this->technician($root, $loja, ['ftth']);
        $this->actingAs($tecnico, 'technician');

        // A tela do mapa existe e so oferece os projetos da propria filial.
        $this->get(route('technician.portal.ftth.map'))
            ->assertOk()
            ->assertSee('Rede da Mangueirinha')
            ->assertDontSee('Rede da Sede')
            ->assertDontSee($redeMatriz->cto->code, false);

        // O JSON que o Leaflet consome tambem vem cortado: e ele que pinta os
        // marcadores, entao escopar so a view nao adiantaria nada.
        $json = $this->getJson(route('technician.portal.ftth.map-data'))
            ->assertOk()
            ->json();

        $ctos = collect($json['ctos'])->pluck('id');
        $caixas = collect($json['caixas'])->pluck('id');

        $this->assertTrue($ctos->contains($redeLoja->cto->id));
        $this->assertTrue($caixas->contains($redeLoja->caixa->id));
        $this->assertFalse($ctos->contains($redeMatriz->cto->id));
        $this->assertFalse($caixas->contains($redeMatriz->caixa->id));

        // O popup leva para o detalhe do tecnico, nao para o admin: o mapa nao
        // pode ser uma porta de entrada para uma tela fora do escopo dele.
        $this->assertStringContainsString('/tecnico/ftth/ctos/', $this->get(route('technician.portal.ftth.map'))->getContent());

        // Filtrar por projeto tambem respeita o escopo: pedir o projeto da
        // matriz direto na query nao devolve nada.
        $this->getJson(route('technician.portal.ftth.map-data', ['project' => $redeMatriz->project->id]))
            ->assertOk()
            ->assertJsonPath('ctos', [])
            ->assertJsonPath('caixas', []);
    }

    private function project(Company $company, Branch $branch, string $name, string $prefix): object
    {
        $project = FtthProject::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => $name,
            'city' => $branch->name,
            'status' => 'active',
        ]);

        $caixa = CaixaEmenda::create([
            'name' => 'CEO '.$name,
            'code' => $prefix.'-CEO',
            'city' => $branch->name,
            'latitude' => -2.5,
            'longitude' => -44.5,
            'ftth_project_id' => $project->id,
        ]);

        $cto = Cto::create([
            'name' => 'CTO '.$name,
            'code' => $prefix.'-CTO',
            'city' => $branch->name,
            'latitude' => -2.5,
            'longitude' => -44.5,
            'status' => 'planned',
            'ftth_project_id' => $project->id,
        ]);

        $caixa->fusions()->create([
            'fiber_number' => 1,
            'status' => 'pending',
            // O escopo de FTTH filtra por `ftth_project_id`; fusao sem projeto
            // fica invisivel para nao-superadmin por design.
            'ftth_project_id' => $project->id,
        ]);

        return (object) [
            'project' => $project,
            'cto' => $cto,
            'caixa' => $caixa,
        ];
    }
}
