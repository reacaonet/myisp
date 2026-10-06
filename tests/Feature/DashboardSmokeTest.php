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

    /**
     * O wrapper do shell precisa ser flex em TODOS os breakpoints, nao so em
     * `lg`.
     *
     * Abaixo de `lg` ele era `display: block`: o div de conteudo deixava de ser
     * item flex, o `flex-1` do `<main>` nao resolvia altura e o
     * `overflow: hidden` do pai cortava o formulario no meio. O sintoma era a
     * tela de dados pessoais sem barra de rolagem vertical, sem jeito de
     * chegar nos campos de baixo nem de editar. No desktop nunca aparecia,
     * porque `lg:flex` entrava e a pendencia ficava escondida.
     */
    public function test_shell_responsivo_do_portal_precisa_ser_flex_em_todos_os_breakpoints(): void
    {
        $company = Company::create(['name' => 'Rede Alfa', 'slug' => 'rede-'.uniqid(), 'is_active' => true, 'is_franchise' => true]);
        $branch = Branch::create(['company_id' => $company->id, 'name' => 'Matriz', 'is_active' => true]);
        $client = $this->client($company, 'Cliente da Silva');

        // O guard `client` autentica o proprio model `Client` (provider
        // `clients`), nao um model de sessao separado.
        $html = $this->actingAs($client, 'client')
            ->get(route('crm.portal.profile'))
            ->assertOk()
            ->getContent();

        // Isola o wrapper do shell: e o unico elemento com `x-data` de gaveta.
        preg_match('/<div[^>]*x-data="\{ open: false \}"[^>]*>/', $html, $m);
        $this->assertNotEmpty($m, 'wrapper do shell nao encontrado');

        $wrapper = $m[0];

        $this->assertStringContainsString('flex', $wrapper);
        $this->assertStringContainsString('overflow-hidden', $wrapper);
        $this->assertStringContainsString('h-screen', $wrapper);

        // `lg:flex` e a forma quebrada: o `flex` sem prefixo e o que segura o
        // layout no celular. Este e o assert que trava a regressao.
        $this->assertStringNotContainsString('lg:flex', $wrapper);

        // E o `<main>` precisa ser o elemento que rola.
        $this->assertMatchesRegularExpression(
            '/<main class="[^"]*overflow-y-auto[^"]*"/',
            $html,
            'a barra de rolagem precisa ficar no main, nao na pagina inteira'
        );
    }

    public function test_shell_responsivo_do_tecnico_precisa_ser_flex_em_todos_os_breakpoints(): void
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

        $html = $this->actingAs($tech->fresh(), 'technician')
            ->get(route('technician.portal.ftth'))
            ->assertOk()
            ->getContent();

        preg_match('/<div[^>]*x-data="\{ open: false \}"[^>]*>/', $html, $m);
        $this->assertNotEmpty($m, 'wrapper do shell nao encontrado');

        // Mesmo defeito, mesmo shell: o do tecnico tinha a mesma linha.
        $this->assertStringNotContainsString('lg:flex', $m[0]);
        $this->assertStringContainsString('flex', $m[0]);

        $this->assertMatchesRegularExpression(
            '/<main class="[^"]*overflow-y-auto[^"]*"/',
            $html,
            'a barra de rolagem precisa ficar no main, nao na pagina inteira'
        );
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
