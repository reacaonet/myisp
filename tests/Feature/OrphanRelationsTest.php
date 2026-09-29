<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Billing\Models\Invoice;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Models\UserGroup;
use Modules\Core\Services\TenantContext;
use Modules\CRM\Models\Client;
use Modules\CRM\Models\Contract;
use Modules\CRM\Models\Plan;
use Modules\CRM\Models\ServiceOrder;
use Modules\CRM\Models\Ticket;
use Tests\TestCase;

class OrphanRelationsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        TenantContext::forget();
    }

    private function actingAsSuperAdmin(): void
    {
        $group = UserGroup::create([
            'name' => 'Super Admin',
            'slug' => 'superadmin',
            'is_active' => true,
        ]);

        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin-orphan@teste.com.br',
            'password' => 'password',
            'user_group_id' => $group->id,
            'is_active' => true,
        ]);

        $this->actingAs($user->fresh());
    }

    private function plan(): Plan
    {
        return Plan::create([
            'company_id' => Company::whereNull('parent_id')->orderBy('id')->first()->id,
            'name' => 'Plano Teste Orfao',
            'slug' => 'plano-teste-orfao',
            'price' => 79.9,
            'download_speed' => 100,
            'upload_speed' => 50,
        ]);
    }

    private function rootScope(): array
    {
        $company = Company::whereNull('parent_id')->orderBy('id')->first();

        return [$company->id, $company->branches()->orderBy('id')->first()->id];
    }

    public function test_contracts_index_renders_when_client_was_soft_deleted(): void
    {
        $this->actingAsSuperAdmin();
        [$companyId, $branchId] = $this->rootScope();

        $client = Client::create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'name' => 'Cliente Orfao',
            'document' => '123.456.789-00',
            'type' => 'individual',
            'status' => 'active',
        ]);

        Contract::create([
            'client_id' => $client->id,
            'plan_id' => $this->plan()->id,
            'activation_date' => now()->subMonths(2),
            'due_day' => 10,
            'status' => 'active',
            'billing_type' => 'pix',
        ]);

        $client->delete();

        $this->assertNull(Contract::first()->client);

        $this->get(route('crm.contracts.index'))->assertOk()->assertSee('Cliente removido');
    }

    public function test_contracts_show_renders_when_client_was_soft_deleted(): void
    {
        $this->actingAsSuperAdmin();
        [$companyId, $branchId] = $this->rootScope();

        $client = Client::create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'name' => 'Cliente Orfao Show',
            'document' => '987.654.321-00',
            'type' => 'individual',
            'status' => 'active',
        ]);

        $contract = Contract::create([
            'client_id' => $client->id,
            'plan_id' => $this->plan()->id,
            'activation_date' => now()->subMonths(2),
            'due_day' => 10,
            'status' => 'active',
            'billing_type' => 'pix',
        ]);

        $client->delete();

        $this->get(route('crm.contracts.show', $contract))->assertOk()->assertSee('Cliente removido');
    }

    public function test_invoice_show_renders_when_client_was_soft_deleted(): void
    {
        $this->actingAsSuperAdmin();
        [$companyId, $branchId] = $this->rootScope();

        $client = Client::create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'name' => 'Cliente Orfao Fatura',
            'document' => '456.789.123-00',
            'type' => 'individual',
            'status' => 'active',
        ]);

        $invoice = Invoice::create([
            'client_id' => $client->id,
            'invoice_number' => 'FAT-2026-9999',
            'amount' => 99.9,
            'total' => 99.9,
            'due_date' => now()->addDays(5),
            'status' => 'pending',
        ]);

        $client->delete();

        $this->get(route('billing.invoices.show', $invoice))->assertOk()->assertSee('Cliente removido');
    }

    public function test_invoices_index_renders_when_client_was_soft_deleted(): void
    {
        $this->actingAsSuperAdmin();
        [$companyId, $branchId] = $this->rootScope();

        $client = Client::create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'name' => 'Cliente Orfao Fatura Index',
            'document' => '111.222.333-44',
            'type' => 'individual',
            'status' => 'active',
        ]);

        $invoice = Invoice::create([
            'client_id' => $client->id,
            'invoice_number' => 'FAT-2026-9997',
            'amount' => 75,
            'total' => 75,
            'due_date' => now()->addDays(3),
            'status' => 'paid',
            'paid_date' => now(),
        ]);

        $client->delete();

        $this->assertNull(Invoice::where('invoice_number', 'FAT-2026-9997')->first()->client);

        $this->get(route('billing.invoices.index'))->assertOk();
        $this->get(route('billing.invoices.receipt', $invoice))->assertOk();
    }

    public function test_boleto_print_renders_when_client_was_soft_deleted(): void
    {
        $this->actingAsSuperAdmin();
        [$companyId, $branchId] = $this->rootScope();

        $client = Client::create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'name' => 'Cliente Orfao Boleto',
            'document' => '222.333.444-55',
            'type' => 'individual',
            'status' => 'active',
        ]);

        $invoice = Invoice::create([
            'client_id' => $client->id,
            'invoice_number' => 'FAT-2026-9996',
            'amount' => 120,
            'total' => 120,
            'due_date' => now()->addDays(7),
            'status' => 'pending',
        ]);

        $client->delete();

        $this->get(route('billing.boleto.print', $invoice))->assertOk()->assertSee('N/A');
    }

    public function test_tickets_and_service_orders_listings_render_when_client_was_soft_deleted(): void
    {
        $this->actingAsSuperAdmin();
        [$companyId, $branchId] = $this->rootScope();

        $client = Client::create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'name' => 'Cliente Orfao Chamados',
            'document' => '333.444.555-66',
            'type' => 'individual',
            'status' => 'active',
        ]);

        Ticket::create([
            'codigo' => 'CHM-ORFAO-1',
            'client_id' => $client->id,
            'subject' => 'Chamado sem cliente ativo',
            'description' => 'Teste de renderizacao',
            'status' => 'open',
            'priority' => 'medium',
        ]);

        ServiceOrder::create([
            'client_id' => $client->id,
            'tipo_servico' => 'instalacao',
            'status' => 'active',
        ]);
        $client->delete();

        $this->get(route('crm.tickets.index'))->assertOk();
        $this->get(route('crm.service-orders.index'))->assertOk();
        $this->get(route('billing.boleto.index'))->assertOk();
    }

    public function test_dashboard_renders_when_overdue_invoice_has_no_client(): void
    {
        $this->actingAsSuperAdmin();
        [$companyId, $branchId] = $this->rootScope();

        $client = Client::create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'name' => 'Cliente Orfao Dashboard',
            'document' => '753.951.357-00',
            'type' => 'individual',
            'status' => 'active',
        ]);

        Invoice::create([
            'client_id' => $client->id,
            'invoice_number' => 'FAT-2026-9998',
            'amount' => 50,
            'total' => 50,
            'due_date' => now()->subWeek(),
            'status' => 'overdue',
        ]);

        $client->delete();

        $this->get(route('crm.dashboard'))->assertOk();
    }
}
