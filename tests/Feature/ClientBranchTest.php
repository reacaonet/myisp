<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Company;
use Modules\Core\Models\UserGroup;
use Modules\Core\Services\TenantContext;
use Modules\CRM\Models\Client;
use Modules\CRM\Models\MikrotikServer;
use Tests\TestCase;

class ClientBranchTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        TenantContext::forget();
        session()->forget(['current_company_id', 'current_branch_id']);
    }

    public function test_cadastro_de_cliente_exige_filial(): void
    {
        $this->branch('Dom Pedro');

        $this->actingAs($this->superAdmin())
            ->post(route('crm.clients.store'), $this->clientData(['branch_id' => '']))
            ->assertSessionHasErrors('branch_id');
    }

    public function test_cliente_e_gravado_na_filial_escolhida(): void
    {
        $domPedro = $this->branch('Dom Pedro');

        $this->actingAs($this->superAdmin())
            ->post(route('crm.clients.store'), $this->clientData(['branch_id' => $domPedro->id]))
            ->assertRedirect(route('crm.clients.index'));

        $client = Client::where('name', 'Cliente Teste')->firstOrFail();

        $this->assertSame($domPedro->id, $client->branch_id);
        $this->assertSame($this->rootCompany()->id, $client->company_id);
    }

    public function test_clientes_de_filiais_diferentes_nao_se_misturam(): void
    {
        $domPedro = $this->branch('Dom Pedro');
        $pioXii = $this->branch('Pio XII');

        $this->actingAs($this->superAdmin())->post(route('crm.clients.store'), $this->clientData([
            'name' => 'Cliente Pedro',
            'branch_id' => $domPedro->id,
        ]));

        $this->actingAs($this->superAdmin())->post(route('crm.clients.store'), $this->clientData([
            'name' => 'Cliente Pio',
            'branch_id' => $pioXii->id,
        ]));

        $this->assertSame($domPedro->id, Client::where('name', 'Cliente Pedro')->value('branch_id'));
        $this->assertSame($pioXii->id, Client::where('name', 'Cliente Pio')->value('branch_id'));
    }

    public function test_filial_de_outra_empresa_e_rejeitada(): void
    {
        $filialAlheia = $this->branch('Dom Pedro', $this->franchise());

        $this->actingAs($this->superAdmin())
            ->post(route('crm.clients.store'), $this->clientData(['branch_id' => $filialAlheia->id]))
            ->assertSessionHasErrors('branch_id');
    }

    public function test_formulario_de_cadastro_oferece_as_filiaes_da_empresa(): void
    {
        $this->branch('Dom Pedro');
        $this->branch('Pio XII');

        $response = $this->actingAs($this->superAdmin())->get(route('crm.clients.create'));

        $response->assertOk();
        $response->assertSee('Dom Pedro');
        $response->assertSee('Pio XII');
    }

    public function test_lista_mostra_clientes_de_todas_as_filiaes(): void
    {
        $domPedro = $this->branch('Dom Pedro');
        $pioXii = $this->branch('Pio XII');

        $this->client($domPedro);
        $this->client($pioXii);

        $response = $this->actingAs($this->superAdmin())->get(route('crm.clients.index'));

        $response->assertOk();
        $response->assertSee('Cliente Dom Pedro');
        $response->assertSee('Cliente Pio XII');
    }

    public function test_lista_filtra_por_filial_escolhida(): void
    {
        $domPedro = $this->branch('Dom Pedro');
        $pioXii = $this->branch('Pio XII');

        $this->client($domPedro);
        $this->client($pioXii);

        $response = $this->actingAs($this->superAdmin())
            ->get(route('crm.clients.index', ['branch_id' => $pioXii->id]));

        $response->assertOk();
        $response->assertSee('Cliente Pio XII');
        $response->assertDontSee('Cliente Dom Pedro');
    }

    public function test_lista_nao_mostra_cliente_de_outra_empresa(): void
    {
        $this->client($this->branch('Matriz Alheia', $this->franchise()));

        $response = $this->actingAs($this->superAdmin())->get(route('crm.clients.index'));

        $response->assertOk();
        $response->assertDontSee('Cliente Matriz Alheia');
    }

    public function test_provisionamento_recusa_servidor_de_outra_filial(): void
    {
        $domPedro = $this->branch('Dom Pedro');
        $pioXii = $this->branch('Pio XII');

        $client = $this->client($domPedro);
        $this->server($domPedro, 'RB Dom Pedro');
        $serverPio = $this->server($pioXii, 'RB Pio XII');

        $this->actingAs($this->superAdmin())
            ->post(route('infra.provisioning.store'), [
                'mikrotik_server_id' => $serverPio->id,
                'client_id' => $client->id,
                'type' => 'pppoe',
                'login' => 'cliente.pedro',
                'password' => 'segredo123',
            ])
            ->assertSessionHasErrors('mikrotik_server_id');

        $this->assertDatabaseMissing('provisioning_records', ['client_id' => $client->id]);
    }

    public function test_provisionamento_recusa_cliente_de_outra_empresa(): void
    {
        $domPedro = $this->branch('Dom Pedro');
        $server = $this->server($domPedro, 'RB Dom Pedro');

        $client = $this->client($this->branch('Matriz Pio', $this->franchise()));

        $this->actingAs($this->superAdmin())
            ->post(route('infra.provisioning.store'), [
                'mikrotik_server_id' => $server->id,
                'client_id' => $client->id,
                'type' => 'pppoe',
                'login' => 'cliente.alheio',
                'password' => 'segredo123',
            ])
            ->assertSessionHasErrors('client_id');
    }

    public function test_provisionamento_aceita_o_servidor_da_filial_do_cliente(): void
    {
        $domPedro = $this->branch('Dom Pedro');
        $this->branch('Pio XII');

        $client = $this->client($domPedro);
        $server = $this->server($domPedro, 'RB Dom Pedro', '127.0.0.1');

        $response = $this->actingAs($this->superAdmin())
            ->post(route('infra.provisioning.store'), [
                'mikrotik_server_id' => $server->id,
                'client_id' => $client->id,
                'type' => 'pppoe',
                'login' => 'cliente.pedro',
                'password' => 'segredo123',
            ]);

        // o vinculo cliente/filial/servidor passou: o erro so pode vir do equipamento
        $response->assertSessionDoesntHaveErrors(['mikrotik_server_id', 'client_id']);
        $this->assertSame($domPedro->id, $client->fresh()->branch_id);
    }

    public function test_formulario_de_provisionamento_marca_a_filial_de_cliente_e_servidor(): void
    {
        $domPedro = $this->branch('Dom Pedro');

        $this->client($domPedro);
        $this->server($domPedro, 'RB Dom Pedro');

        $response = $this->actingAs($this->superAdmin())->get(route('infra.provisioning.create'));

        $response->assertOk();
        $response->assertSee('data-branch="'.$domPedro->id.'"', false);
    }

    private function clientData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Cliente Teste',
            'document' => (string) random_int(10000000000, 99999999999),
            'type' => 'individual',
            'status' => 'active',
        ], $overrides);
    }

    private function rootCompany(): Company
    {
        return Company::whereNull('parent_id')->orderBy('id')->firstOrFail();
    }

    private function franchise(): Company
    {
        $franchise = Company::create([
            'parent_id' => $this->rootCompany()->id,
            'name' => 'Franquia Filiais',
            'slug' => 'franquia-filiais-'.uniqid(),
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

    private function branch(string $name, ?Company $company = null): Branch
    {
        $company ??= $this->rootCompany();

        return Branch::create([
            'company_id' => $company->id,
            'name' => $name,
            'is_active' => true,
        ]);
    }

    private function client(Branch $branch): Client
    {
        return Client::create([
            'company_id' => $branch->company_id,
            'branch_id' => $branch->id,
            'name' => 'Cliente '.$branch->name,
            'document' => (string) random_int(10000000000, 99999999999),
            'type' => 'individual',
            'status' => 'active',
        ]);
    }

    private function server(Branch $branch, string $name, ?string $ip = null): MikrotikServer
    {
        return MikrotikServer::create([
            'company_id' => $branch->company_id,
            'branch_id' => $branch->id,
            'name' => $name,
            'ip' => $ip ?? '10.'.random_int(1, 250).'.0.'.random_int(2, 250),
            'port' => 8728,
            'login' => 'admin',
            'senha' => 'segredo123',
            'type' => 'pppoe',
            'is_active' => true,
        ]);
    }

    private function superAdmin(): User
    {
        $group = UserGroup::create([
            'name' => 'Super Admin Filiais',
            'slug' => 'superadmin-filiais-'.uniqid(),
            'is_active' => true,
        ]);

        foreach (['clients', 'provisioning', 'mikrotik_servers'] as $permission) {
            DB::table('group_permissions')->updateOrInsert(
                ['group_id' => $group->id, 'permission_key' => $permission],
                ['granted' => true, 'updated_at' => now()]
            );
        }

        $user = User::create([
            'name' => 'Super Admin Filiais',
            'email' => 'superadmin-filiais-'.uniqid().'@teste.local',
            'password' => bcrypt('secret123'),
            'user_group_id' => $group->id,
            'is_active' => true,
        ]);

        $root = $this->rootCompany();

        $user->companies()->attach($root->id);
        $user->branches()->attach($root->branches()->pluck('id')->all());

        return $user->fresh();
    }
}
