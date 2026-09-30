<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Payment;
use Modules\Billing\Models\PaymentGateway;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Company;
use Modules\Core\Models\UserGroup;
use Modules\Core\Services\TenantContext;
use Modules\CRM\Models\Client;
use Tests\TestCase;

/**
 * As asserções usam o nome do cliente como ancora porque o campo de busca
 * ecoa o termo pesquisado no HTML: o número da fatura não serve para provar
 * que a linha sumiu da tabela.
 */
class BillingListFilterTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        TenantContext::forget();
        session()->forget(['current_company_id', 'current_branch_id']);
    }

    public function test_fatura_busca_por_numero_boleto_transacao_e_cliente(): void
    {
        $branch = $this->branch('Dom Pedro', $this->rootCompany());

        $this->invoice($branch, 'FAT-BUSCA-1', 'Cliente Alvo Busca', [
            'boleto_numero' => '34191.09008 01047.000148 10000.150008 8',
            'transaction_id' => 'E2EABC123',
        ]);
        $this->invoice($branch, 'FAT-OUTRA-1', 'Cliente Isolado Busca');

        $this->invoices(['search' => 'FAT-BUSCA-1'])
            ->assertSee('Cliente Alvo Busca')
            ->assertDontSee('Cliente Isolado Busca');

        $this->invoices(['search' => 'fat-busca-1'])
            ->assertSee('Cliente Alvo Busca')
            ->assertDontSee('Cliente Isolado Busca');

        $this->invoices(['search' => '34191.09008'])
            ->assertSee('Cliente Alvo Busca')
            ->assertDontSee('Cliente Isolado Busca');

        $this->invoices(['search' => 'e2eabc123'])
            ->assertSee('Cliente Alvo Busca')
            ->assertDontSee('Cliente Isolado Busca');

        $this->invoices(['search' => 'alvo busca'])
            ->assertSee('Cliente Alvo Busca')
            ->assertDontSee('Cliente Isolado Busca');
    }

    public function test_fatura_busca_combinada_com_status_nao_traz_a_fatura_errada(): void
    {
        $branch = $this->branch('Dom Pedro', $this->rootCompany());

        $this->invoice($branch, 'FAT-PAGA-1', 'Cliente Fatura Paga', ['status' => 'paid']);
        $this->invoice($branch, 'FAT-PENDENTE-1', 'Cliente Fatura Pendente', ['status' => 'pending']);

        $this->invoices(['search' => 'FAT-PAGA-1', 'status' => 'paid'])
            ->assertSee('Cliente Fatura Paga')
            ->assertDontSee('Cliente Fatura Pendente');

        // o status que não bate não pode "vazar" a fatura pela busca
        $this->invoices(['search' => 'FAT-PAGA-1', 'status' => 'pending'])
            ->assertDontSee('Cliente Fatura Paga');
    }

    public function test_fatura_filtra_por_forma_pagamento_filial_e_valores(): void
    {
        $matriz = $this->branch('Dom Pedro', $this->rootCompany());
        $centro = $this->branch('Centro', $this->rootCompany());

        $this->invoice($matriz, 'FAT-PIX-1', 'Cliente Fatura Pix', ['payment_method' => 'pix', 'total' => 80]);
        $this->invoice($matriz, 'FAT-BOLETO-1', 'Cliente Fatura Boleto', ['payment_method' => 'boleto', 'total' => 150]);
        $this->invoice($centro, 'FAT-CENTRO-1', 'Cliente Fatura Centro', ['total' => 300]);

        $this->invoices(['payment_method' => 'pix'])
            ->assertSee('Cliente Fatura Pix')
            ->assertDontSee('Cliente Fatura Boleto');

        $this->invoices(['branch_id' => $centro->id])
            ->assertSee('Cliente Fatura Centro')
            ->assertDontSee('Cliente Fatura Pix');

        $this->invoices(['total_min' => 100, 'total_max' => 200])
            ->assertSee('Cliente Fatura Boleto')
            ->assertDontSee('Cliente Fatura Pix')
            ->assertDontSee('Cliente Fatura Centro');

        $this->invoices(['total_max' => 100])
            ->assertSee('Cliente Fatura Pix')
            ->assertDontSee('Cliente Fatura Boleto');
    }

    public function test_fatura_filtra_por_periodo_de_vencimento_e_pagamento(): void
    {
        $branch = $this->branch('Dom Pedro', $this->rootCompany());

        $this->invoice($branch, 'FAT-JAN-1', 'Cliente Fatura Janeiro', [
            'due_date' => '2026-01-15',
            'status' => 'paid',
            'paid_date' => '2026-01-10',
        ]);
        $this->invoice($branch, 'FAT-MAR-1', 'Cliente Fatura Marco', [
            'due_date' => '2026-03-15',
            'status' => 'paid',
            'paid_date' => '2026-02-10',
        ]);
        $this->invoice($branch, 'FAT-ABR-1', 'Cliente Fatura Abril', ['due_date' => '2026-04-15']);

        $this->invoices(['due_from' => '2026-03-01', 'due_to' => '2026-03-31'])
            ->assertSee('Cliente Fatura Marco')
            ->assertDontSee('Cliente Fatura Janeiro')
            ->assertDontSee('Cliente Fatura Abril');

        $this->invoices(['paid_from' => '2026-02-01', 'paid_to' => '2026-02-28'])
            ->assertSee('Cliente Fatura Marco')
            ->assertDontSee('Cliente Fatura Janeiro')
            ->assertDontSee('Cliente Fatura Abril');
    }

    public function test_fatura_filtra_por_bloqueio_e_tipo(): void
    {
        $branch = $this->branch('Dom Pedro', $this->rootCompany());

        $this->invoice($branch, 'FAT-BLOQUEADA-1', 'Cliente Fatura Bloqueada', ['blocked_at' => now()]);
        $this->invoice($branch, 'FAT-LIVRE-1', 'Cliente Fatura Livre');
        $this->invoice($branch, 'FAT-AVULSA-1', 'Cliente Fatura Avulsa', ['avulso' => true]);

        $this->invoices(['blocked' => 'sim'])
            ->assertSee('Cliente Fatura Bloqueada')
            ->assertDontSee('Cliente Fatura Livre');

        $this->invoices(['blocked' => 'nao'])
            ->assertSee('Cliente Fatura Livre')
            ->assertDontSee('Cliente Fatura Bloqueada');

        $this->invoices(['avulso' => 'sim'])
            ->assertSee('Cliente Fatura Avulsa')
            ->assertDontSee('Cliente Fatura Livre');
    }

    public function test_busca_na_fatura_nao_atravessa_a_empresa_do_operador(): void
    {
        $root = $this->rootCompany();
        $matriz = $this->branch('Dom Pedro', $root);

        $filialFranquia = $this->franchiseBranch('Franquia Filtro', 'franquia-filtro-billing');

        $this->invoice($matriz, 'FAT-DA-MATRIZ-1', 'Cliente Fatura Matriz', ['status' => 'pending']);
        $this->invoice($filialFranquia, 'FAT-DA-FRANQUIA-1', 'Cliente Fatura Franquia', ['status' => 'pending']);

        $this->invoices(['search' => 'FAT-DA-FRANQUIA-1'])
            ->assertDontSee('Cliente Fatura Franquia');

        $this->invoices(['search' => 'FAT-DA-FRANQUIA-1', 'status' => 'pending'])
            ->assertDontSee('Cliente Fatura Franquia');

        // a fatura da propria empresa segue visivel
        $this->invoices(['search' => 'FAT-DA-MATRIZ-1'])
            ->assertSee('Cliente Fatura Matriz')
            ->assertDontSee('Cliente Fatura Franquia');
    }

    public function test_valor_de_filtro_invalido_e_recusado(): void
    {
        $branch = $this->branch('Dom Pedro', $this->rootCompany());
        $this->invoice($branch, 'FAT-VISIVEL-1', 'Cliente Fatura Visivel');

        $this->actingAs($this->operator(['invoices']))
            ->get(route('billing.invoices.index', ['status' => "pending' or '1'='1"]))
            ->assertRedirect()
            ->assertSessionHasErrors('status');

        $this->actingAs($this->operator(['invoices']))
            ->get(route('billing.invoices.index', ['due_from' => '2026-05-01', 'due_to' => '2026-01-01']))
            ->assertRedirect()
            ->assertSessionHasErrors('due_to');

        $this->actingAs($this->operator(['invoices']))
            ->get(route('billing.invoices.index', ['total_min' => 500, 'total_max' => 100]))
            ->assertRedirect()
            ->assertSessionHasErrors('total_max');

        $this->invoices(['status' => 'pending'])->assertSee('Cliente Fatura Visivel');

        $this->assertDatabaseHas('invoices', ['invoice_number' => 'FAT-VISIVEL-1']);
    }

    public function test_exclusao_em_massa_de_faturas_apaga_pagamentos_e_preserva_as_demais(): void
    {
        $branch = $this->branch('Dom Pedro', $this->rootCompany());

        $primeira = $this->invoice($branch, 'FAT-EXCLUIR-1', 'Cliente Excluir Um');
        $segunda = $this->invoice($branch, 'FAT-EXCLUIR-2', 'Cliente Excluir Dois');
        $mantida = $this->invoice($branch, 'FAT-MANTER-1', 'Cliente Manter');

        foreach ([$primeira, $segunda] as $invoice) {
            Payment::create([
                'invoice_id' => $invoice->id,
                'amount' => 10,
                'payment_date' => now()->toDateString(),
                'payment_method' => 'pix',
            ]);
        }

        $this->actingAs($this->operator(['invoices']))
            ->post(route('billing.invoices.bulk-destroy'), ['ids' => [$primeira->id, $segunda->id]])
            ->assertRedirect(route('billing.invoices.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('invoices', ['id' => $primeira->id]);
        $this->assertDatabaseMissing('invoices', ['id' => $segunda->id]);
        $this->assertDatabaseMissing('payments', ['invoice_id' => $primeira->id]);
        $this->assertDatabaseMissing('payments', ['invoice_id' => $segunda->id]);
        $this->assertDatabaseHas('invoices', ['id' => $mantida->id]);
    }

    public function test_exclusao_em_massa_de_faturas_respeita_o_escopo_da_empresa(): void
    {
        $matriz = $this->branch('Dom Pedro', $this->rootCompany());

        $daMatriz = $this->invoice($matriz, 'FAT-MATRIZ-1', 'Cliente Bulk Matriz');

        $daFranquia = $this->invoice(
            $this->franchiseBranch('Franquia Bulk', 'franquia-bulk-billing'),
            'FAT-FRANQUIA-1',
            'Cliente Bulk Franquia'
        );

        $this->actingAs($this->operator(['invoices']))
            ->post(route('billing.invoices.bulk-destroy'), ['ids' => [$daMatriz->id, $daFranquia->id]])
            ->assertRedirect(route('billing.invoices.index'));

        $this->assertDatabaseMissing('invoices', ['id' => $daMatriz->id]);
        $this->assertDatabaseHas('invoices', ['id' => $daFranquia->id]);
    }

    public function test_exclusao_em_massa_sem_selecao_devolve_erro(): void
    {
        $branch = $this->branch('Dom Pedro', $this->rootCompany());
        $invoice = $this->invoice($branch, 'FAT-INTOCADA-1', 'Cliente Inalterada');

        $this->actingAs($this->operator(['invoices']))
            ->post(route('billing.invoices.bulk-destroy'), ['ids' => []])
            ->assertSessionHasErrors('ids');

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id]);
    }

    public function test_exclusao_individual_de_fatura_de_outra_empresa_nao_acessa(): void
    {
        $daFranquia = $this->invoice(
            $this->franchiseBranch('Franquia Destroy', 'franquia-destroy-billing'),
            'FAT-PROTETIDA-1',
            'Cliente Fatura Protegida'
        );

        $this->actingAs($this->operator(['invoices']))
            ->delete(route('billing.invoices.destroy', $daFranquia->id))
            ->assertNotFound();

        $this->assertDatabaseHas('invoices', ['id' => $daFranquia->id]);
    }

    public function test_boleto_filtra_por_gateway_e_situacao_da_cobranca(): void
    {
        $root = $this->rootCompany();
        $branch = $this->branch('Dom Pedro', $root);

        $gateway = PaymentGateway::create([
            'company_id' => $root->id,
            'name' => 'Gateway Filtro',
            'slug' => 'gateway-filtro-'.uniqid(),
            'status' => 'active',
            'config' => [],
        ]);

        $this->invoice($branch, 'FAT-COM-BOLETO-1', 'Cliente Boleto Gerado', [
            'gateway_id' => $gateway->id,
            'boleto_numero' => '34191789012345678901',
        ]);
        $this->invoice($branch, 'FAT-COM-PIX-1', 'Cliente Pix Gerado', [
            'pix_copy_paste' => '00020126580014BR.GOV.BCB.PIX',
        ]);
        $this->invoice($branch, 'FAT-SEM-COBRANCA-1', 'Cliente Sem Cobranca');

        $this->boletos(['charge' => 'boleto'])
            ->assertSee('Cliente Boleto Gerado')
            ->assertDontSee('Cliente Pix Gerado');

        $this->boletos(['charge' => 'pix'])
            ->assertSee('Cliente Pix Gerado')
            ->assertDontSee('Cliente Boleto Gerado');

        $this->boletos(['charge' => 'sem'])
            ->assertSee('Cliente Sem Cobranca')
            ->assertDontSee('Cliente Boleto Gerado');

        $this->boletos(['gateway_id' => $gateway->id])
            ->assertSee('Cliente Boleto Gerado')
            ->assertDontSee('Cliente Pix Gerado');

        $this->boletos(['search' => 'FAT-COM-PIX-1', 'status' => 'pending'])
            ->assertSee('Cliente Pix Gerado')
            ->assertDontSee('Cliente Boleto Gerado');
    }

    public function test_boletos_filtra_por_periodo_e_valor(): void
    {
        $branch = $this->branch('Dom Pedro', $this->rootCompany());

        $this->invoice($branch, 'FAT-JAN-BOLETO-1', 'Cliente Boleto Janeiro', ['due_date' => '2026-01-15', 'total' => 50]);
        $this->invoice($branch, 'FAT-MAR-BOLETO-1', 'Cliente Boleto Marco', ['due_date' => '2026-03-15', 'total' => 250]);

        $this->boletos(['due_from' => '2026-03-01', 'due_to' => '2026-03-31'])
            ->assertSee('Cliente Boleto Marco')
            ->assertDontSee('Cliente Boleto Janeiro');

        $this->boletos(['total_max' => 100])
            ->assertSee('Cliente Boleto Janeiro')
            ->assertDontSee('Cliente Boleto Marco');
    }

    public function test_exclusao_em_massa_na_tela_de_boletos(): void
    {
        $branch = $this->branch('Dom Pedro', $this->rootCompany());

        $primeira = $this->invoice($branch, 'FAT-BOLETO-EXCLUIR-1', 'Cliente Boleto Excluir Um', [
            'boleto_numero' => '34191789012345678902',
        ]);
        $segunda = $this->invoice($branch, 'FAT-BOLETO-EXCLUIR-2', 'Cliente Boleto Excluir Dois');
        $mantida = $this->invoice($branch, 'FAT-BOLETO-MANTER-1', 'Cliente Boleto Manter');

        $this->actingAs($this->operator(['boleto']))
            ->post(route('billing.boleto.bulk-destroy'), ['ids' => [$primeira->id, $segunda->id]])
            ->assertRedirect(route('billing.boleto.index'));

        $this->assertDatabaseMissing('invoices', ['id' => $primeira->id]);
        $this->assertDatabaseMissing('invoices', ['id' => $segunda->id]);
        $this->assertDatabaseHas('invoices', ['id' => $mantida->id]);
    }

    private function invoices(array $filters)
    {
        return $this->actingAs($this->operator(['invoices']))
            ->get(route('billing.invoices.index', $filters))
            ->assertOk();
    }

    private function boletos(array $filters)
    {
        return $this->actingAs($this->operator(['boleto']))
            ->get(route('billing.boleto.index', $filters))
            ->assertOk();
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

    private function franchiseBranch(string $name, string $slug): Branch
    {
        $franchise = Company::create([
            'parent_id' => $this->rootCompany()->id,
            'name' => $name,
            'slug' => $slug,
            'is_franchise' => true,
            'is_active' => true,
        ]);

        return $this->branch('Matriz', $franchise);
    }

    private function invoice(Branch $branch, string $number, string $clientName, array $overrides = []): Invoice
    {
        $client = Client::create([
            'company_id' => $branch->company_id,
            'branch_id' => $branch->id,
            'name' => $clientName,
            'document' => (string) random_int(10000000000, 99999999999),
            'type' => 'individual',
            'status' => 'active',
        ]);

        return Invoice::create(array_merge([
            'company_id' => $branch->company_id,
            'branch_id' => $branch->id,
            'client_id' => $client->id,
            'invoice_number' => $number,
            'amount' => 100,
            'discount' => 0,
            'acrescimo' => 0,
            'total' => 100,
            'due_date' => now()->toDateString(),
            'status' => 'pending',
        ], $overrides));
    }

    private function operator(array $permissions): User
    {
        $group = UserGroup::create([
            'name' => 'Operador Faturamento',
            'slug' => 'operador-faturamento-'.uniqid(),
            'is_active' => true,
        ]);

        foreach ($permissions as $permission) {
            DB::table('group_permissions')->updateOrInsert(
                ['group_id' => $group->id, 'permission_key' => $permission],
                ['granted' => true, 'updated_at' => now()]
            );
        }

        $user = User::create([
            'name' => 'Operador Faturamento',
            'email' => 'faturamento-'.uniqid().'@teste.local',
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
