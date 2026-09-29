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
use Modules\CRM\Models\Contract;
use Modules\CRM\Models\Plan;
use Tests\TestCase;

class ClientFilterTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        TenantContext::forget();
        session()->forget(['current_company_id', 'current_branch_id']);
    }

    public function test_filtra_por_status(): void
    {
        $branch = $this->branch('Dom Pedro');

        $this->client($branch, 'Cliente Ativo', ['status' => 'active']);
        $this->client($branch, 'Cliente Suspenso', ['status' => 'suspended']);

        $this->listing(['status' => 'suspended'])
            ->assertSee('Cliente Suspenso')
            ->assertDontSee('Cliente Ativo');
    }

    public function test_filtra_por_tipo_de_pessoa(): void
    {
        $branch = $this->branch('Dom Pedro');

        $this->client($branch, 'Cliente Fisica', ['type' => 'individual']);
        $this->client($branch, 'Cliente Juridica', ['type' => 'legal']);

        $this->listing(['type' => 'legal'])
            ->assertSee('Cliente Juridica')
            ->assertDontSee('Cliente Fisica');
    }

    public function test_filtra_por_tipo_de_assinante_e_utilizacao(): void
    {
        $branch = $this->branch('Dom Pedro');

        $this->client($branch, 'Cliente PF Residencial', ['tipo_assinante' => 'pf', 'tipo_utilizacao' => 'residencial']);
        $this->client($branch, 'Cliente PJ Comercial', ['tipo_assinante' => 'pj', 'tipo_utilizacao' => 'comercial']);

        $this->listing(['tipo_assinante' => 'pj'])
            ->assertSee('Cliente PJ Comercial')
            ->assertDontSee('Cliente PF Residencial');

        $this->listing(['tipo_utilizacao' => 'residencial'])
            ->assertSee('Cliente PF Residencial')
            ->assertDontSee('Cliente PJ Comercial');
    }

    public function test_filtra_por_grupo_existente_na_base(): void
    {
        $branch = $this->branch('Dom Pedro');

        $this->client($branch, 'Cliente Ouro', ['grupo' => 'ouro']);
        $this->client($branch, 'Cliente Diamante', ['grupo' => 'diamante']);

        $this->listing(['grupo' => 'diamante'])
            ->assertSee('Cliente Diamante')
            ->assertDontSee('Cliente Ouro');
    }

    public function test_busca_cobre_login_codigo_e_telefone(): void
    {
        $branch = $this->branch('Dom Pedro');

        $this->client($branch, 'Cliente Sem Login', ['login' => 'pedro.silva', 'codigo' => 'C-0001', 'phone' => '1133334444']);
        $this->client($branch, 'Outro Cliente');

        $this->listing(['search' => 'C-0001'])
            ->assertSee('Cliente Sem Login')
            ->assertDontSee('Outro Cliente');

        $this->listing(['search' => 'pedro.silva'])
            ->assertSee('Cliente Sem Login')
            ->assertDontSee('Outro Cliente');
    }

    public function test_filtra_por_contrato_ativo_e_sem_contrato(): void
    {
        $branch = $this->branch('Dom Pedro');

        $comContrato = $this->client($branch, 'Cliente Com Contrato');
        $this->contract($comContrato, 'active');
        $this->contract($comContrato, 'canceled');

        $this->client($branch, 'Cliente Sem Contrato');

        $this->listing(['contract' => 'active'])
            ->assertSee('Cliente Com Contrato')
            ->assertDontSee('Cliente Sem Contrato');

        $this->listing(['contract' => 'without'])
            ->assertSee('Cliente Sem Contrato')
            ->assertDontSee('Cliente Com Contrato');

        $this->listing(['contract' => 'suspended'])
            ->assertSee('Cliente Com Contrato')
            ->assertDontSee('Cliente Sem Contrato');
    }

    public function test_filtra_por_filial_e_ordena_por_nome(): void
    {
        $domPedro = $this->branch('Dom Pedro');
        $pioXii = $this->branch('Pio XII');

        $this->client($domPedro, 'Zeca Pedro');
        $this->client($domPedro, 'Ana Pedro');
        $this->client($pioXii, 'Bruno Pio');

        $this->listing(['branch_id' => $pioXii->id])
            ->assertSee('Bruno Pio')
            ->assertDontSee('Ana Pedro');

        $this->listing(['order' => 'name'])
            ->assertSeeInOrder(['Ana Pedro', 'Bruno Pio', 'Zeca Pedro']);
    }

    public function test_valor_de_filtro_desconhecido_e_descartado(): void
    {
        $branch = $this->branch('Dom Pedro');

        $this->client($branch, 'Cliente Visivel', ['status' => 'active']);

        // injeccao classica: nao pode virar coluna/valor na query
        $this->listing(['status' => "active' or '1'='1", 'order' => 'drop table clients'])
            ->assertOk()
            ->assertSee('Cliente Visivel');

        $this->assertDatabaseHas('clients', ['name' => 'Cliente Visivel']);
    }

    public function test_total_da_listagem_respeita_o_filtro(): void
    {
        $branch = $this->branch('Dom Pedro');

        $this->client($branch, 'Cliente Ativo Um', ['status' => 'active']);
        $this->client($branch, 'Cliente Ativo Dois', ['status' => 'active']);
        $this->client($branch, 'Cliente Inativo', ['status' => 'inactive']);

        $this->listing(['status' => 'active'])
            ->assertSee('2 clientes na rede');
    }

    public function test_limpar_tira_todos_os_filtros(): void
    {
        $response = $this->actingAs($this->superAdmin())->get(route('crm.clients.index'));

        $response->assertOk();
        $response->assertSee('Nenhum filtro ativo');
        $response->assertSee('Todas as filiais');
    }

    private function listing(array $filters)
    {
        return $this->actingAs($this->superAdmin())
            ->get(route('crm.clients.index', $filters))
            ->assertOk();
    }

    private function rootCompany(): Company
    {
        return Company::whereNull('parent_id')->orderBy('id')->firstOrFail();
    }

    private function branch(string $name): Branch
    {
        return Branch::create([
            'company_id' => $this->rootCompany()->id,
            'name' => $name,
            'is_active' => true,
        ]);
    }

    private function client(Branch $branch, string $name, array $overrides = []): Client
    {
        return Client::create(array_merge([
            'company_id' => $branch->company_id,
            'branch_id' => $branch->id,
            'name' => $name,
            'document' => (string) random_int(10000000000, 99999999999),
            'type' => 'individual',
            'status' => 'active',
        ], $overrides));
    }

    private function contract(Client $client, string $status): Contract
    {
        $company = $this->rootCompany();

        $plan = Plan::create([
            'company_id' => $company->id,
            'name' => 'Plano Filtro '.uniqid(),
            'slug' => 'plano-'.uniqid(),
            'download_speed' => 10000,
            'upload_speed' => 5000,
            'price' => 99.9,
            'billing_cycle' => 'monthly',
        ]);

        return Contract::create([
            'company_id' => $company->id,
            'client_id' => $client->id,
            'plan_id' => $plan->id,
            'status' => $status,
            'start_date' => now()->toDateString(),
            'activation_date' => now()->toDateString(),
        ]);
    }

    private function superAdmin(): User
    {
        $group = UserGroup::create([
            'name' => 'Super Admin Filtros',
            'slug' => 'superadmin-filtros-'.uniqid(),
            'is_active' => true,
        ]);

        DB::table('group_permissions')->updateOrInsert(
            ['group_id' => $group->id, 'permission_key' => 'clients'],
            ['granted' => true, 'updated_at' => now()]
        );

        $user = User::create([
            'name' => 'Super Admin Filtros',
            'email' => 'filtros-'.uniqid().'@teste.local',
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
