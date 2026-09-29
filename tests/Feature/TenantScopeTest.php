<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\PaymentGateway;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Models\UserGroup;
use Modules\Core\Services\TenantContext;
use Modules\CRM\Models\Client;
use Modules\CRM\Models\Plan;
use Modules\CRM\Models\StockCategory;
use Modules\CRM\Models\StockItem;
use Modules\CRM\Models\StockLocation;
use Tests\TestCase;

class TenantScopeTest extends TestCase
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
            'name' => 'Franquia Testada',
            'slug' => 'franquia-testada',
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

    private function operatorUser(Company $company, ?Branch $branch = null): User
    {
        $group = UserGroup::create([
            'name' => 'Operador',
            'slug' => 'operador',
            'is_active' => true,
        ]);

        $user = User::create([
            'name' => 'Operador Teste',
            'email' => 'operador@teste.com.br',
            'password' => 'password',
            'user_group_id' => $group->id,
            'is_active' => true,
        ]);

        $user->companies()->attach($company->id);
        $user->branches()->attach($branch?->id ?? $this->matrixBranch($company)->id);

        return $user->fresh();
    }

    private function makeClient(Company $company, Branch $branch, string $document, string $name = 'Cliente Teste'): Client
    {
        return Client::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => $name,
            'document' => $document,
            'type' => 'individual',
            'status' => 'active',
        ]);
    }

    public function test_context_falls_back_to_root_when_there_is_no_user(): void
    {
        $root = $this->rootCompany();

        $this->assertSame($root->id, TenantContext::companyId());
        $this->assertSame($this->matrixBranch($root)->id, TenantContext::branchId());
    }

    public function test_context_uses_only_companies_the_user_belongs_to(): void
    {
        $franchise = $this->franchise();
        $user = $this->operatorUser($franchise);

        $this->actingAs($user);

        $this->assertSame($franchise->id, TenantContext::companyId());
        $this->assertSame($this->matrixBranch($franchise)->id, TenantContext::branchId());

        session(['current_company_id' => $this->rootCompany()->id]);
        TenantContext::forget();

        $this->assertSame($franchise->id, TenantContext::companyId());
    }

    public function test_models_receive_company_and_branch_from_context(): void
    {
        $franchise = $this->franchise();
        $branch = $this->matrixBranch($franchise);
        $user = $this->operatorUser($franchise);

        $this->actingAs($user);

        $client = Client::create([
            'name' => 'Cliente sem escopo explicito',
            'document' => '111.111.111-11',
            'type' => 'individual',
            'status' => 'active',
        ]);

        $this->assertSame($franchise->id, $client->company_id);
        $this->assertSame($branch->id, $client->branch_id);

        $location = StockLocation::create(['name' => 'Deposito Central', 'type' => 'deposit']);
        $this->assertSame($franchise->id, $location->company_id);
        $this->assertSame($branch->id, $location->branch_id);

        $plan = Plan::create(['name' => 'Plano 100MB', 'slug' => 'plano-100mb', 'price' => 79.9, 'download_speed' => 100, 'upload_speed' => 50]);
        $this->assertSame($franchise->id, $plan->company_id);
        $this->assertSame($branch->id, $plan->branch_id);

        $gateway = PaymentGateway::create(['name' => 'Mercado Pago', 'slug' => 'mercado-pago', 'status' => 'active']);
        $this->assertSame($franchise->id, $gateway->company_id);
    }

    public function test_client_document_can_repeat_in_another_company_but_not_in_the_same_one(): void
    {
        $root = $this->rootCompany();
        $franchise = $this->franchise();

        $first = $this->makeClient($root, $this->matrixBranch($root), '222.222.222-22', 'Cliente Raiz');
        $second = $this->makeClient($franchise, $this->matrixBranch($franchise), '222.222.222-22', 'Cliente Franquia');

        $this->assertNotSame($first->company_id, $second->company_id);

        $this->expectException(QueryException::class);

        $this->makeClient($root, $this->matrixBranch($root), '222.222.222-22', 'Cliente Duplicado');
    }

    public function test_plan_and_gateway_slug_can_repeat_per_company(): void
    {
        $root = $this->rootCompany();
        $franchise = $this->franchise();

        Plan::create(['company_id' => $root->id, 'name' => 'Plano Root', 'slug' => 'plano-compartilhado', 'price' => 50, 'download_speed' => 50, 'upload_speed' => 25]);
        Plan::create(['company_id' => $franchise->id, 'name' => 'Plano Franquia', 'slug' => 'plano-compartilhado', 'price' => 60, 'download_speed' => 60, 'upload_speed' => 30]);

        PaymentGateway::create(['company_id' => $root->id, 'name' => 'MP Root', 'slug' => 'mercado-pago', 'status' => 'active']);
        PaymentGateway::create(['company_id' => $franchise->id, 'name' => 'MP Franquia', 'slug' => 'mercado-pago', 'status' => 'active']);

        $this->assertSame(2, Plan::where('slug', 'plano-compartilhado')->count());
        $this->assertSame(2, PaymentGateway::where('slug', 'mercado-pago')->count());

        $this->expectException(QueryException::class);

        Plan::create(['company_id' => $franchise->id, 'name' => 'Plano Duplicado', 'slug' => 'plano-compartilhado', 'price' => 70, 'download_speed' => 70, 'upload_speed' => 35]);
    }

    public function test_stock_item_sku_can_repeat_per_company(): void
    {
        $root = $this->rootCompany();
        $franchise = $this->franchise();

        $category = StockCategory::create(['name' => 'Cabos']);

        StockItem::create(['company_id' => $root->id, 'category_id' => $category->id, 'sku' => 'SKU-1', 'name' => 'Item Raiz', 'unit' => 'un', 'min_stock' => 0]);
        StockItem::create(['company_id' => $franchise->id, 'category_id' => $category->id, 'sku' => 'SKU-1', 'name' => 'Item Franquia', 'unit' => 'un', 'min_stock' => 0]);

        $this->assertSame(2, StockItem::where('sku', 'SKU-1')->count());
    }

    public function test_invoice_inherits_tenant_from_client_and_numbers_per_company(): void
    {
        $root = $this->rootCompany();
        $franchise = $this->franchise();

        $rootClient = $this->makeClient($root, $this->matrixBranch($root), '333.333.333-33', 'Cliente Fatura Raiz');
        $franchiseClient = $this->makeClient($franchise, $this->matrixBranch($franchise), '444.444.444-44', 'Cliente Fatura Franquia');

        $invoiceRoot = Invoice::create([
            'client_id' => $rootClient->id,
            'invoice_number' => Invoice::nextNumber($root->id, '2026-03-10'),
            'amount' => 100,
            'total' => 100,
            'due_date' => '2026-03-10',
            'status' => 'pending',
        ]);

        $invoiceFranchise = Invoice::create([
            'client_id' => $franchiseClient->id,
            'invoice_number' => Invoice::nextNumber($franchise->id, '2026-03-10'),
            'amount' => 200,
            'total' => 200,
            'due_date' => '2026-03-10',
            'status' => 'pending',
        ]);

        $this->assertSame($root->id, $invoiceRoot->company_id);
        $this->assertSame($this->matrixBranch($root)->id, $invoiceRoot->branch_id);
        $this->assertSame('FAT-2026-0001', $invoiceRoot->invoice_number);

        $this->assertSame($franchise->id, $invoiceFranchise->company_id);
        $this->assertSame('FAT-2026-0001', $invoiceFranchise->invoice_number);

        $secondRoot = Invoice::create([
            'client_id' => $rootClient->id,
            'invoice_number' => Invoice::nextNumber($root->id, '2026-03-10'),
            'amount' => 50,
            'total' => 50,
            'due_date' => '2026-03-10',
            'status' => 'pending',
        ]);

        $this->assertSame('FAT-2026-0002', $secondRoot->invoice_number);
        $this->assertSame(1, Invoice::forCompany($franchise->id)->count());
        $this->assertSame(2, Invoice::forCompany($root->id)->count());
    }

    public function test_invoice_number_is_unique_per_company(): void
    {
        $root = $this->rootCompany();
        $client = $this->makeClient($root, $this->matrixBranch($root), '555.555.555-55');

        Invoice::create([
            'client_id' => $client->id,
            'invoice_number' => 'FAT-2026-0001',
            'amount' => 10,
            'total' => 10,
            'due_date' => '2026-03-10',
            'status' => 'pending',
        ]);

        $this->expectException(QueryException::class);

        Invoice::create([
            'client_id' => $client->id,
            'invoice_number' => 'FAT-2026-0001',
            'amount' => 10,
            'total' => 10,
            'due_date' => '2026-04-10',
            'status' => 'pending',
        ]);
    }

    public function test_context_switch_rejects_company_outside_the_user_membership(): void
    {
        $root = $this->rootCompany();
        $franchise = $this->franchise();
        $user = $this->operatorUser($franchise);

        $this->actingAs($user);

        $this->from('/')
            ->post(route('core.context.switch'), ['company_id' => $root->id])
            ->assertRedirect('/');

        $this->assertNull(session('current_company_id'));
    }

    public function test_context_switch_rejects_branch_from_another_company(): void
    {
        $root = $this->rootCompany();
        $franchise = $this->franchise();
        $user = $this->operatorUser($franchise);

        $this->actingAs($user);

        $this->from('/')
            ->post(route('core.context.switch'), [
                'company_id' => $franchise->id,
                'branch_id' => $this->matrixBranch($root)->id,
            ])
            ->assertRedirect('/');

        $this->assertSame($franchise->id, session('current_company_id'));
        $this->assertNull(session('current_branch_id'));
    }

    public function test_context_switch_accepts_own_company_and_branch(): void
    {
        $franchise = $this->franchise();
        $branch = $this->matrixBranch($franchise);
        $user = $this->operatorUser($franchise);

        $this->actingAs($user);

        $this->from('/')
            ->post(route('core.context.switch'), [
                'company_id' => $franchise->id,
                'branch_id' => $branch->id,
            ])
            ->assertRedirect('/');

        $this->assertSame($franchise->id, session('current_company_id'));
        $this->assertSame($branch->id, session('current_branch_id'));
    }

    public function test_superadmin_sees_every_company(): void
    {
        $root = $this->rootCompany();
        $franchise = $this->franchise();

        $group = UserGroup::create(['name' => 'Super Admin', 'slug' => 'superadmin', 'is_active' => true]);
        $user = User::create([
            'name' => 'Admin',
            'email' => 'superadmin@teste.com.br',
            'password' => 'password',
            'user_group_id' => $group->id,
            'is_active' => true,
        ]);

        $this->actingAs($user->fresh());

        $this->assertTrue(TenantContext::isCrossTenant());
        $this->assertSame([$root->id, $franchise->id], TenantContext::allowedCompanyIds());

        $this->makeClient($root, $this->matrixBranch($root), '666.666.666-66', 'Cliente Visivel 1');
        $this->makeClient($franchise, $this->matrixBranch($franchise), '777.777.777-77', 'Cliente Visivel 2');

        $this->assertSame(2, Client::query()->count());
    }

    public function test_client_listing_is_limited_to_the_tenant_of_the_user(): void
    {
        $root = $this->rootCompany();
        $franchise = $this->franchise();

        $this->makeClient($root, $this->matrixBranch($root), '888.888.888-88', 'Cliente da Raiz');
        $visible = $this->makeClient($franchise, $this->matrixBranch($franchise), '999.999.999-99', 'Cliente da Franquia');

        $user = $this->operatorUser($franchise);
        $this->actingAs($user);

        $this->assertSame(1, Client::forCompany(TenantContext::companyId())->count());
        $this->assertSame($visible->id, Client::forCompany(TenantContext::companyId())->first()->id);
    }

    public function test_layout_context_selector_works_with_the_authenticated_user_model(): void
    {
        $root = $this->rootCompany();
        $branch = $this->matrixBranch($root);

        $group = UserGroup::create(['name' => 'Super Admin', 'slug' => 'superadmin', 'is_active' => true]);

        $user = \App\Models\User::create([
            'name' => 'Admin Autenticado',
            'email' => 'autenticado@teste.com.br',
            'password' => 'password',
            'user_group_id' => $group->id,
            'is_active' => true,
        ]);

        $user->companies()->attach($root->id);
        $user->branches()->attach($branch->id);

        $this->actingAs($user->fresh());

        $this->get(route('crm.clients.index'))->assertOk();
        $this->get(route('crm.contracts.index'))->assertOk();

        $this->assertNotNull($user->companies()->first());
        $this->assertNotNull($user->branches()->first());
    }
}
