<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Company;
use Modules\Core\Models\GroupPermission;
use Modules\Core\Models\UserGroup;
use Modules\Core\Services\TenantContext;
use Modules\CRM\Models\Client;
use Modules\CRM\Models\Contract;
use Modules\CRM\Models\HotspotCoupon;
use Modules\CRM\Models\MikrotikBackup;
use Modules\CRM\Models\MikrotikServer;
use Modules\CRM\Models\Olt;
use Modules\CRM\Models\Plan;
use Modules\CRM\Models\ProvisioningRecord;
use Modules\CRM\Models\UptimeMonitor;
use Modules\PortalInfra\Models\Cto;
use Modules\PortalInfra\Models\FtthProject;
use Tests\TestCase;

class InfraTenantScopeTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        TenantContext::forget();
        session()->forget(['current_company_id', 'current_branch_id']);
    }

    public function test_a_tabela_servers_legada_nao_existe_mais(): void
    {
        $this->assertFalse(
            Schema::hasTable('servers'),
            'A tabela legada servers deveria ter sido removida na Fase 4.'
        );
    }

    public function test_relacoes_de_servidor_apontam_para_mikrotik_servers(): void
    {
        $company = $this->rootCompany();
        $server = $this->server($company, 'Servidor Relacoes');

        $backup = MikrotikBackup::create([
            'company_id' => $company->id,
            'server_id' => $server->id,
            'filename' => 'backup_teste.rsc',
            'content' => '{}',
            'type' => 'manual',
        ]);

        $coupon = HotspotCoupon::create([
            'company_id' => $company->id,
            'code' => 'REL-'.uniqid(),
            'server_id' => $server->id,
            'duration_hours' => 24,
            'price' => 10,
        ]);

        $monitor = UptimeMonitor::create([
            'company_id' => $company->id,
            'name' => 'Monitor Relacoes',
            'host' => '10.0.0.1',
            'port' => 8728,
            'type' => 'tcp',
            'interval_seconds' => 60,
            'server_id' => $server->id,
        ]);

        $this->assertSame($server->id, $backup->server->id);
        $this->assertSame($server->id, $coupon->server->id);
        $this->assertSame($server->id, $monitor->server->id);
    }

    public function test_equipamentos_de_outra_empresa_nao_aparecem_na_lista(): void
    {
        $root = $this->rootCompany();
        $franchise = $this->franchise();

        $rootServer = $this->server($root, 'Servidor da Raiz');
        $franchiseServer = $this->server($franchise, 'Servidor da Franquia');

        $this->actingAs($this->operator($franchise, ['mikrotik_servers']));

        $visible = MikrotikServer::scoped()->pluck('id');

        $this->assertTrue($visible->contains($franchiseServer->id));
        $this->assertFalse($visible->contains($rootServer->id));
        $this->assertNull(MikrotikServer::findScoped($rootServer->id));
    }

    public function test_equipamento_de_outra_empresa_responde_404_na_edicao(): void
    {
        $root = $this->rootCompany();
        $franchise = $this->franchise();

        $rootServer = $this->server($root, 'Servidor Privado da Raiz');

        $this->actingAs($this->operator($franchise, ['mikrotik_servers']));

        $this->get(route('infra.mikrotik-servers.edit', $rootServer->id))->assertNotFound();
    }

    public function test_superadmin_enxerga_todos_os_equipamentos(): void
    {
        $root = $this->rootCompany();
        $franchise = $this->franchise();

        $this->server($root, 'Servidor Raiz Visivel');
        $this->server($franchise, 'Servidor Franquia Visivel');

        $this->actingAs($this->superAdmin());

        $this->assertTrue(TenantContext::isCrossTenant());
        $this->assertSame(2, MikrotikServer::scoped()->whereIn('name', ['Servidor Raiz Visivel', 'Servidor Franquia Visivel'])->count());
    }

    public function test_ip_repetido_e_bloqueado_dentro_da_mesma_empresa_e_liberado_na_outra(): void
    {
        $root = $this->rootCompany();
        $franchise = $this->franchise();

        $this->server($root, 'Servidor IP Duplicado', '10.0.0.1');

        $payload = [
            'branch_id' => $this->matrixBranch($root)->id,
            'name' => 'Outro Servidor',
            'ip' => '10.0.0.1',
            'port' => 8728,
            'login' => 'admin',
            'senha' => 'segredo123',
            'type' => 'both',
        ];

        $this->actingAs($this->operator($root, ['mikrotik_servers']));

        $this->post(route('infra.mikrotik-servers.store'), $payload)
            ->assertSessionHasErrors('ip');

        $this->assertDatabaseMissing('mikrotik_servers', ['ip' => '10.0.0.1', 'name' => 'Outro Servidor']);

        $this->actingAs($this->operator($franchise, ['mikrotik_servers']));

        $this->post(route('infra.mikrotik-servers.store'), [
            'branch_id' => $this->matrixBranch($franchise)->id,
            'name' => 'Outro Servidor',
            'ip' => '10.0.0.1',
            'port' => 8728,
            'login' => 'admin',
            'senha' => 'segredo123',
            'type' => 'both',
        ])->assertRedirect(route('infra.mikrotik-servers.index'));

        $this->assertDatabaseHas('mikrotik_servers', [
            'company_id' => $franchise->id,
            'ip' => '10.0.0.1',
        ]);
    }

    public function test_cupom_de_outra_empresa_nao_e_visivel_nem_editavel(): void
    {
        $root = $this->rootCompany();
        $franchise = $this->franchise();

        $rootCoupon = HotspotCoupon::create([
            'company_id' => $root->id,
            'code' => 'ROOT-'.uniqid(),
            'duration_hours' => 24,
            'price' => 15,
        ]);

        $this->actingAs($this->operator($franchise, ['hotspot_coupons']));

        $this->get(route('infra.hotspot-coupons.edit', $rootCoupon->id))->assertNotFound();
        $this->put(route('infra.hotspot-coupons.update', $rootCoupon->id), ['code' => 'INVADIDO'])->assertNotFound();
        $this->delete(route('infra.hotspot-coupons.destroy', $rootCoupon->id))->assertNotFound();

        $this->assertDatabaseHas('hotspot_coupons', ['id' => $rootCoupon->id, 'code' => $rootCoupon->code]);
    }

    public function test_mesmo_codigo_de_cupom_pode_existir_em_empresas_diferentes(): void
    {
        $root = $this->rootCompany();
        $franchise = $this->franchise();

        $code = 'COMUM-'.uniqid();

        HotspotCoupon::create(['company_id' => $root->id, 'code' => $code, 'duration_hours' => 24, 'price' => 5]);
        HotspotCoupon::create(['company_id' => $franchise->id, 'code' => $code, 'duration_hours' => 24, 'price' => 5]);

        $this->actingAs($this->operator($franchise, ['hotspot_coupons']));

        $response = $this->post(route('infra.hotspot-coupons.store'), [
            'code' => $code,
            'duration_hours' => 24,
            'price' => 5,
        ]);

        $response->assertSessionHasErrors('code');

        $this->post(route('infra.hotspot-coupons.store'), [
            'code' => 'NOVO-'.uniqid(),
            'duration_hours' => 24,
            'price' => 5,
        ])->assertRedirect(route('infra.hotspot-coupons.index'));

        $this->assertSame(1, HotspotCoupon::forCompany($franchise->id)->where('code', $code)->count());
    }

    public function test_monitor_de_uptime_usa_o_escopo_da_empresa(): void
    {
        $root = $this->rootCompany();
        $franchise = $this->franchise();

        $rootMonitor = UptimeMonitor::create([
            'company_id' => $root->id,
            'name' => 'Monitor da Raiz',
            'host' => '10.0.0.1',
            'port' => 80,
            'type' => 'tcp',
            'interval_seconds' => 60,
        ]);

        UptimeMonitor::create([
            'company_id' => $franchise->id,
            'name' => 'Monitor da Franquia',
            'host' => '10.0.0.2',
            'port' => 80,
            'type' => 'tcp',
            'interval_seconds' => 60,
        ]);

        $this->actingAs($this->operator($franchise, ['uptime']));

        $this->get(route('infra.uptime.index'))->assertOk()->assertDontSee('Monitor da Raiz');

        $this->get(route('infra.uptime.show', $rootMonitor->id))->assertNotFound();
    }

    public function test_registro_de_provisionamento_guarda_o_contrato_do_cliente(): void
    {
        $company = $this->rootCompany();

        $client = Client::create([
            'company_id' => $company->id,
            'branch_id' => $this->matrixBranch($company)->id,
            'name' => 'Cliente Provisionado',
            'document' => '123.456.789-00',
            'type' => 'individual',
            'status' => 'active',
        ]);

        $plan = Plan::create([
            'company_id' => $company->id,
            'name' => 'Plano Provisionamento',
            'slug' => 'plano-'.uniqid(),
            'download_speed' => 10000,
            'upload_speed' => 5000,
            'price' => 99.9,
            'billing_cycle' => 'monthly',
        ]);

        $contract = Contract::create([
            'company_id' => $company->id,
            'client_id' => $client->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'start_date' => now()->toDateString(),
            'activation_date' => now()->toDateString(),
        ]);

        $server = $this->server($company, 'Servidor Provisionamento');

        $record = ProvisioningRecord::create([
            'mikrotik_server_id' => $server->id,
            'client_id' => $client->id,
            'contract_id' => $contract->id,
            'type' => 'pppoe',
            'action' => 'add',
            'login' => 'cliente-'.uniqid(),
            'success' => true,
        ]);

        $this->assertSame($contract->id, $record->fresh()->contract->id);
        $this->assertTrue(Schema::hasColumn('provisioning_records', 'contract_id'));
    }

    public function test_equipamento_herda_empresa_e_filial_do_contexto(): void
    {
        $franchise = $this->franchise();
        $this->actingAs($this->operator($franchise, ['mikrotik_servers']));

        $server = MikrotikServer::create([
            'name' => 'Servidor Herdado',
            'ip' => '172.16.0.1',
            'port' => 8728,
            'login' => 'admin',
            'senha' => 'segredo123',
            'type' => 'both',
        ]);

        $this->assertSame($franchise->id, $server->company_id);
        $this->assertSame($this->matrixBranch($franchise)->id, $server->branch_id);
    }

    public function test_equipamento_e_cadastrado_na_filial_escolhida(): void
    {
        $root = $this->rootCompany();

        $domPedro = $this->branch('Dom Pedro', $root);
        $this->branch('Pio XII', $root);

        $this->actingAs($this->superAdmin());

        $this->post(route('infra.mikrotik-servers.store'), $this->serverPayload($root->id, $domPedro->id))
            ->assertRedirect(route('infra.mikrotik-servers.index'));

        $server = MikrotikServer::where('name', 'RB Dom Pedro')->firstOrFail();

        $this->assertSame($domPedro->id, $server->branch_id);
        $this->assertSame($root->id, $server->company_id);
    }

    public function test_equipamento_recusa_filial_de_outra_empresa(): void
    {
        $root = $this->rootCompany();
        $filialAlheia = $this->branch('Dom Pedro', $this->franchise());

        $this->actingAs($this->superAdmin());

        $this->post(route('infra.mikrotik-servers.store'), $this->serverPayload($root->id, $filialAlheia->id))
            ->assertSessionHasErrors('branch_id');
    }

    public function test_equipamento_exige_filial(): void
    {
        $this->actingAs($this->superAdmin());

        $this->post(route('infra.mikrotik-servers.store'), $this->serverPayload($this->rootCompany()->id, null))
            ->assertSessionHasErrors('branch_id');
    }

    public function test_formulario_de_equipamento_oferece_as_filiaes(): void
    {
        $root = $this->rootCompany();
        $this->branch('Dom Pedro', $root);
        $this->branch('Pio XII', $root);

        $response = $this->actingAs($this->superAdmin())->get(route('infra.mikrotik-servers.create'));

        $response->assertOk();
        $response->assertSee('Dom Pedro');
        $response->assertSee('Pio XII');
    }

    public function test_filial_pode_ser_trocada_na_edicao_do_equipamento(): void
    {
        $root = $this->rootCompany();
        $domPedro = $this->branch('Dom Pedro', $root);
        $pioXii = $this->branch('Pio XII', $root);

        $server = MikrotikServer::create([
            'company_id' => $root->id,
            'branch_id' => $domPedro->id,
            'name' => 'RB Para Mover',
            'ip' => '10.90.0.1',
            'port' => 8728,
            'login' => 'admin',
            'senha' => 'segredo123',
            'type' => 'pppoe',
            'is_active' => true,
        ]);

        $this->actingAs($this->superAdmin());

        $this->put(route('infra.mikrotik-servers.update', $server), $this->serverPayload($root->id, $pioXii->id, 'RB Para Mover'))
            ->assertRedirect(route('infra.mikrotik-servers.index'));

        $this->assertSame($pioXii->id, $server->fresh()->branch_id);
    }

    private function serverPayload(int $companyId, ?int $branchId, string $name = 'RB Dom Pedro'): array
    {
        return [
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'name' => $name,
            'ip' => '10.'.random_int(1, 250).'.0.'.random_int(2, 250),
            'port' => 8728,
            'login' => 'admin',
            'senha' => 'segredo123',
            'type' => 'pppoe',
        ];
    }

    private function rootCompany(): Company
    {
        return Company::whereNull('parent_id')->orderBy('id')->firstOrFail();
    }

    private function branch(string $name, Company $company): Branch
    {
        return Branch::create([
            'company_id' => $company->id,
            'name' => $name,
            'is_active' => true,
        ]);
    }

    private function franchise(): Company
    {
        $franchise = Company::create([
            'parent_id' => $this->rootCompany()->id,
            'name' => 'Franquia Infra',
            'slug' => 'franquia-infra-'.uniqid(),
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

    private function matrixBranch(Company $company): Branch
    {
        return Branch::where('company_id', $company->id)->orderBy('id')->first();
    }

    private function server(Company $company, string $name, ?string $ip = null): MikrotikServer
    {
        return MikrotikServer::create([
            'company_id' => $company->id,
            // A filial e o dono do equipamento: sem ela o servidor fica fora do
            // alcance de quem so opera uma loja.
            'branch_id' => $this->matrixBranch($company)?->id,
            'name' => $name,
            'ip' => $ip ?? '10.'.$company->id.'.0.'.random_int(2, 250),
            'port' => 8728,
            'login' => 'admin',
            'senha' => 'segredo123',
            'type' => 'both',
        ]);
    }

    private function operator(Company $company, array $permissions): User
    {
        $group = UserGroup::create([
            'name' => 'Operador Infra',
            'slug' => 'operador-infra-'.uniqid(),
            'is_active' => true,
        ]);

        foreach ($permissions as $permission) {
            DB::table('group_permissions')->updateOrInsert(
                ['group_id' => $group->id, 'permission_key' => $permission],
                ['granted' => true, 'updated_at' => now()]
            );
        }

        $user = User::create([
            'name' => 'Operador Infra',
            'email' => 'operador-infra-'.uniqid().'@teste.local',
            'password' => bcrypt('secret123'),
            'user_group_id' => $group->id,
            'is_active' => true,
        ]);

        $user->companies()->attach($company->id);
        $user->branches()->attach($this->matrixBranch($company)->id);

        return $user->fresh();
    }

    private function superAdmin(): User
    {
        $group = UserGroup::create(['name' => 'Super Admin', 'slug' => 'superadmin', 'is_active' => true]);

        foreach (array_keys(GroupPermission::MENU_PERMISSIONS()) as $key) {
            DB::table('group_permissions')->updateOrInsert(
                ['group_id' => $group->id, 'permission_key' => $key],
                ['granted' => true, 'updated_at' => now()]
            );
        }

        $user = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin-infra-'.uniqid().'@teste.local',
            'password' => bcrypt('secret123'),
            'user_group_id' => $group->id,
            'is_active' => true,
        ]);

        $user->companies()->attach($this->rootCompany()->id);
        $user->branches()->attach($this->matrixBranch($this->rootCompany())->id);

        return $user->fresh();
    }

    public function test_olt_de_outra_empresa_responde_404_em_todas_as_acoes(): void
    {
        $minha = $this->franchise();
        $outra = $this->franchise();

        // OLT fica na matriz da OUTRA loja: e exatamente o alvo que o grupo
        // com `olts` tentaria alcançar so pelo id.
        $oltAlheia = Olt::create([
            'company_id' => $outra->id,
            'branch_id' => $this->matrixBranch($outra)->id,
            'name' => 'OLT Secreta',
            'ip' => '10.99.0.1',
        ]);

        $user = $this->operator($minha, ['dashboard', 'olts']);

        $this->actingAs($user)->get(route('infra.olts.edit', [$oltAlheia->id]))
            ->assertNotFound();

        $this->actingAs($user)->put(route('infra.olts.update', [$oltAlheia->id]), [
            'name' => 'Sequestrada',
            'ip' => '10.99.0.2',
        ])->assertNotFound();

        $this->actingAs($user)->delete(route('infra.olts.destroy', [$oltAlheia->id]))
            ->assertNotFound();

        // Nada foi alterado nem apagado.
        $this->assertDatabaseHas('olts', [
            'id' => $oltAlheia->id,
            'name' => 'OLT Secreta',
            'ip' => '10.99.0.1',
        ]);

        // E a propria OLT continua acessivel.
        $minhaOlt = Olt::create([
            'company_id' => $minha->id,
            'branch_id' => $this->matrixBranch($minha)->id,
            'name' => 'OLT Da Casa',
            'ip' => '10.98.0.1',
        ]);

        $this->actingAs($user)->get(route('infra.olts.edit', [$minhaOlt->id]))->assertOk();
    }

    public function test_infra_da_matriz_fica_invisivel_para_o_usuario_da_filial(): void
    {
        // Mesmo caso do CRM e do financeiro: uma empresa so, com matriz e loja.
        // O filtro antigo era so por empresa, entao o usuario da loja enxergava
        // os equipamentos e as redes da matriz.
        $root = $this->rootCompany();
        $matriz = $this->matrixBranch($root);
        $loja = $this->branch('Filial Dom Pedro MA', $root);

        $servidorDaMatriz = $this->server($root, 'Mikrotik Matriz');
        $servidorDaMatriz->update(['branch_id' => $matriz->id]);

        $servidorDaLoja = $this->server($root, 'Mikrotik Dom Pedro');
        $servidorDaLoja->update(['branch_id' => $loja->id, 'ip' => '10.77.0.1']);

        $oltDaMatriz = Olt::create([
            'company_id' => $root->id, 'branch_id' => $matriz->id,
            'name' => 'OLT da Sede', 'ip' => '10.98.0.1',
        ]);
        $oltDaLoja = Olt::create([
            'company_id' => $root->id, 'branch_id' => $loja->id,
            'name' => 'OLT do Dom Pedro', 'ip' => '10.77.9.1',
        ]);

        $projectDaMatriz = FtthProject::create([
            'company_id' => $root->id, 'branch_id' => $matriz->id,
            'name' => 'Rede da Sede', 'city' => 'Cidade da Sede', 'status' => 'active',
        ]);
        $projectDaLoja = FtthProject::create([
            'company_id' => $root->id, 'branch_id' => $loja->id,
            'name' => 'Rede do Dom Pedro', 'city' => 'Dom Pedro', 'status' => 'active',
        ]);

        $ctoDaMatriz = Cto::create(['name' => 'CTO Sede', 'code' => 'SED-001', 'city' => 'Cidade da Sede', 'latitude' => -2.5, 'longitude' => -44.5, 'ftth_project_id' => $projectDaMatriz->id]);
        $ctoDaLoja = Cto::create(['name' => 'CTO Dom Pedro', 'code' => 'DOM-001', 'city' => 'Dom Pedro', 'latitude' => -2.49, 'longitude' => -44.49, 'ftth_project_id' => $projectDaLoja->id]);

        $registroDaMatriz = ProvisioningRecord::create([
            'company_id' => $root->id, 'mikrotik_server_id' => $servidorDaMatriz->id,
            'login' => 'pppoe-sede', 'type' => 'pppoe', 'action' => 'add',
        ]);

        $user = $this->operator($root, ['mikrotik_servers', 'olts', 'ftth', 'provisioning']);
        $user->branches()->sync([$loja->id]);

        // Equipamentos: so o da filial dele.
        $this->actingAs($user)->get(route('infra.mikrotik-servers.index'))
            ->assertOk()
            ->assertSee('Mikrotik Dom Pedro')
            ->assertDontSee('Mikrotik Matriz');

        $this->actingAs($user)->get(route('infra.mikrotik-servers.edit', [$servidorDaMatriz->id]))->assertNotFound();
        $this->actingAs($user)->get(route('infra.mikrotik-servers.edit', [$servidorDaLoja->id]))->assertOk();

        // OLT: mesma regra.
        $this->actingAs($user)->get(route('infra.olts.index'))
            ->assertOk()
            ->assertSee('OLT do Dom Pedro')
            ->assertDontSee('OLT da Sede');
        $this->actingAs($user)->get(route('infra.olts.edit', [$oltDaMatriz->id]))->assertNotFound();
        $this->actingAs($user)->get(route('infra.olts.edit', [$oltDaLoja->id]))->assertOk();

        // FTTH: o projeto e o que define o dono da rede.
        $this->actingAs($user)->get(route('infra.ftth.projects.index'))
            ->assertOk()
            ->assertSee('Rede do Dom Pedro')
            ->assertDontSee('Rede da Sede');
        $this->actingAs($user)->get(route('infra.ftth.projects.show', [$projectDaMatriz->id]))->assertNotFound();
        $this->actingAs($user)->get(route('infra.ftth.projects.show', [$projectDaLoja->id]))->assertOk();

        $this->actingAs($user)->get(route('infra.ftth.ctos.index'))
            ->assertOk()
            ->assertSee('CTO Dom Pedro')
            ->assertDontSee('CTO Sede');
        $this->actingAs($user)->get(route('infra.ftth.ctos.show', [$ctoDaMatriz->id]))->assertNotFound();

        // O editor de rede e o ponto mais critico: ele trabalhava por `city`, que
        // e global, entao abria e alterava o desenho da outra filial.
        $this->actingAs($user)->get(route('infra.ftth.editor.data', ['cidade' => 'Cidade da Sede']))
            ->assertOk()
            ->assertJsonPath('ctos', []);

        $this->actingAs($user)->get(route('infra.ftth.editor.data', ['cidade' => 'Dom Pedro']))
            ->assertOk()
            ->assertJsonCount(1, 'ctos')
            ->assertJsonPath('ctos.0.code', 'DOM-001');

        // E nenhuma acao que muda estado aceita id da outra filial.
        $this->actingAs($user)
            ->putJson(route('infra.ftth.editor.elements.update', ['type' => 'cto', 'id' => $ctoDaMatriz->id]), [
                'name' => 'Invadida',
            ])->assertNotFound();

        $this->assertDatabaseHas('ctos', ['id' => $ctoDaMatriz->id, 'name' => 'CTO Sede']);

        // Provisionamento tambem herda o servidor.
        $this->actingAs($user)->get(route('infra.provisioning.edit', [$registroDaMatriz->id]))->assertNotFound();
    }
}
