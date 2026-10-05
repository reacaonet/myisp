<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Billing\Models\Invoice;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Company;
use Modules\Core\Models\UserGroup;
use Modules\Core\Services\TenantContext;
use Modules\CRM\Models\Client;
use Modules\CRM\Models\ServiceOrder;
use Tests\TestCase;

class DashboardSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        TenantContext::forget();
    }

    public function test_dashboard_do_tecnico_renderiza(): void
    {
        $company = Company::create(['name' => 'Rede Alfa', 'slug' => 'rede-'.uniqid(), 'is_active' => true, 'is_franchise' => true]);
        $branch = Branch::create(['company_id' => $company->id, 'name' => 'Matriz', 'is_active' => true]);

        $group = UserGroup::firstOrCreate(['slug' => 'tecnico'], ['name' => 'Tecnico', 'is_active' => true]);
        $tech = User::create([
            'name' => 'Tecnico da Silva',
            'email' => 'tec-'.uniqid().'@teste.local',
            'password' => bcrypt('secret123'),
            'user_group_id' => $group->id,
            'is_active' => true,
        ]);
        $tech->companies()->attach($company->id);
        $tech->branches()->attach($branch->id);

        $client = $this->client($company, 'Cliente da Silva');

        // Uma OS em cada etapa, para exercitar todos os ramos de badge.
        foreach ([['O', 'active'], ['A', 'active'], ['F', 'closed']] as [$situacao, $status]) {
            ServiceOrder::create([
                'codigo' => 'OS-'.$situacao.'-'.uniqid(),
                'client_id' => $client->id,
                'situacao' => $situacao,
                'status' => $status,
                'servico' => 'Instalacao',
                'tipo_servico' => 'instalacao',
                'emissao' => now()->subDay(),
                'data_agendamento' => today(),
                'hora_agendamento' => '14:30:00',
                'technician_id' => $tech->id,
            ]);
        }

        $this->actingAs($tech->fresh(), 'technician')
            ->get(route('technician.portal.dashboard'))
            ->assertOk();
    }

    public function test_dashboard_do_cliente_renderiza_em_cada_estado_financeiro(): void
    {
        $company = Company::create(['name' => 'Rede Alfa', 'slug' => 'rede-'.uniqid(), 'is_active' => true, 'is_franchise' => true]);
        $branch = Branch::create(['company_id' => $company->id, 'name' => 'Matriz', 'is_active' => true]);
        $client = $this->client($company, 'Cliente da Silva');

        // 1) Sem nenhuma fatura: precisa cair no estado "voce esta em dia".
        $this->actingAs($client, 'client')->get(route('crm.portal.dashboard'))->assertOk();

        // 2) Fatura vencida: hero de divida com valor e vencimento.
        $overdue = $this->invoice($client, 'overdue', now()->subDays(5), now()->subDays(10)->toDateTimeString());
        $this->actingAs($client->fresh(), 'client')
            ->get(route('crm.portal.dashboard'))
            ->assertOk()
            ->assertSee('Em aberto')
            ->assertSee($overdue->invoice_number);

        // 3) Ultima fatura paga, mas uma anterior ainda em aberto: a tela nao pode
        //    oferecer pagamento nem vencimento da fatura ja paga.
        $this->invoice($client, 'paid', now()->subDays(1), now()->toDateTimeString());
        $this->actingAs($client->fresh(), 'client')
            ->get(route('crm.portal.dashboard'))
            ->assertOk()
            ->assertDontSee($overdue->invoice_number);

        // 4) OS em cada etapa, mais um chamado aberto.
        foreach ([['O', 'active'], ['A', 'active'], ['F', 'active']] as [$situacao, $status]) {
            ServiceOrder::create([
                'codigo' => 'OS-'.$situacao.'-'.uniqid(),
                'client_id' => $client->id,
                'situacao' => $situacao,
                'status' => $status,
                'servico' => 'Manutencao',
                'tipo_servico' => 'manutencao',
                'emissao' => now()->subDay(),
            ]);
        }
        $this->actingAs($client->fresh(), 'client')
            ->get(route('crm.portal.dashboard'))
            ->assertOk()
            ->assertSee('Em andamento');
    }

    private function client(Company $company, string $name): Client
    {
        return Client::create([
            'name' => $name,
            'document' => (string) random_int(10000000000, 99999999999),
            'email' => 'cli-'.uniqid().'@teste.local',
            'company_id' => $company->id,
            'branch_id' => $company->branches()->value('id'),
            'status' => 'active',
        ]);
    }

    private function invoice(Client $client, string $status, $dueDate, ?string $createdAt = null): Invoice
    {
        $invoice = Invoice::create([
            'client_id' => $client->id,
            'contract_id' => null,
            'invoice_number' => strtoupper($status).'-'.uniqid(),
            'status' => $status,
            'due_date' => $dueDate,
            'amount' => 100.00,
            'total' => 100.00,
        ]);

        if ($createdAt) {
            // O dashboard le `invoices` com `latest()`, que ordena por
            // `created_at`. Sem carimbo explicito, duas faturas criadas no mesmo
            // instante ficam sem ordem definida e o teste vira loteria.
            $invoice->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();
        }

        return $invoice;
    }
}
