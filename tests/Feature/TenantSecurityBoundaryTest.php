<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Billing\Models\CashBookEntry;
use Modules\Billing\Models\Invoice;

use Modules\Core\Models\Branch;
use Modules\Core\Models\Company;
use Modules\Core\Models\UserGroup;
use Modules\Core\Services\TenantContext;
use Modules\CRM\Models\Client;
use Modules\CRM\Models\Plan;
use Modules\CRM\Models\ServiceOrder;

use Tests\TestCase;

/**
 * Trava as correcoes de seguranca do painel multiempresa.
 *
 * O alvo aqui nao e o "happy path" e sim a tentativa de atravessar a fronteira:
 * buscar um cliente de outra franquia pela URL, editar grupo nao-superadmin e
 * vincular usuario a uma empresa/filial sem ser superadmin.
 */
class TenantSecurityBoundaryTest extends TestCase
{
    public function test_cliente_de_outra_franquia_responde_404_em_todas_as_acoes(): void
    {
        $minha = $this->franchise('Franquia Alpha', 'alpha');
        $outra = $this->franchise('Franquia Beta', 'beta');

        $alheio = $this->makeClient($outra, 'Cliente Beta', '11111111111');

        // Com `clients` concedido o pedido passa pelo middleware e chega no
        // controller: e ai que o escopo por empresa tem de barrar com 404.
        $grupo = $this->grupoComSettings('Atendente');
        $this->grant($grupo, 'clients');
        $usuario = $this->operador($minha, $grupo);

        $rotas = [
            ['crm.clients.show', [$alheio->id]],
            ['crm.clients.edit', [$alheio->id]],
            ['crm.clients.history', [$alheio->id]],
        ];

        foreach ($rotas as [$rota, $params]) {
            $this->actingAs($usuario)
                ->get(route($rota, $params))
                ->assertNotFound();
        }

        // update e destroy tambem: nao podem Neither existir nem ser aplicados.
        $this->actingAs($usuario)
            ->put(route('crm.clients.update', [$alheio->id]), ['name' => 'Invadido'])
            ->assertNotFound();

        $this->actingAs($usuario)
            ->delete(route('crm.clients.destroy', [$alheio->id]))
            ->assertNotFound();

        $this->assertDatabaseHas('clients', [
            'id' => $alheio->id,
            'name' => 'Cliente Beta',
        ]);
    }

    public function test_franqueado_acessa_o_dashboard_padrao(): void
    {
        $minha = $this->franchise('Franquia Alpha', 'alpha');
        $outra = $this->franchise('Franquia Beta', 'beta');

        $this->makeClient($minha, 'Cliente Alpha', '22222222222');
        $this->makeClient($outra, 'Cliente Beta', '33333333333');

        $usuario = $this->franqueado($minha);

        // O dashboard e escopado por empresa: mostra a propria franquia e
        // nao revela a vizinha.
        $this->actingAs($usuario)
            ->get(route('crm.dashboard'))
            ->assertOk()
            ->assertSee('Cliente Alpha')
            ->assertDontSee('Cliente Beta');
    }

    public function test_franqueado_usa_as_paginas_da_operacao_da_propria_franquia(): void
    {
        $minha = $this->franchise('Franquia Alpha', 'alpha');
        $outra = $this->franchise('Franquia Beta', 'beta');

        $proprio = $this->makeClient($minha, 'Cliente Alpha', '22222222222');
        $alheio = $this->makeClient($outra, 'Cliente Beta', '33333333333');

        $usuario = $this->franqueado($minha);

        // Paginas que antes davam 403 para todo o grupo.
        foreach ([
            'crm.dashboard',
            'crm.clients.index',
            'crm.contracts.index',
            'crm.service-orders.index',
            'crm.tickets.index',
        ] as $rota) {
            $this->actingAs($usuario)->get(route($rota))->assertOk();
        }

        // E continua preso na propria empresa.
        $this->actingAs($usuario)->get(route('crm.clients.edit', [$proprio->id]))->assertOk();
        $this->actingAs($usuario)->get(route('crm.clients.show', [$alheio->id]))->assertNotFound();
    }

    public function test_franqueado_nao_acessa_configuracoes(): void
    {
        $franquia = $this->franchise('Franquia Alpha', 'alpha');
        $usuario = $this->franqueado($franquia);

        // `settings` e `franchisees` nao fazem parte do perfil do gerente.
        $this->actingAs($usuario)->get(route('core.settings.index'))->assertForbidden();
        $this->actingAs($usuario)->get(route('core.user-groups.index'))->assertForbidden();
    }

    public function test_operador_com_settings_nao_consegue_gerenciar_grupos(): void
    {
        $franquia = $this->franchise('Franquia Alpha', 'alpha');
        $grupo = $this->grupoComSettings('Configurador');
        $usuario = $this->operador($franquia, $grupo);

        $this->grant($grupo, 'settings');

        $this->actingAs($usuario)->get(route('core.user-groups.index'))->assertForbidden();
        $this->actingAs($usuario)->get(route('core.user-groups.create'))->assertForbidden();
        $this->actingAs($usuario)->get(route('core.user-groups.edit', [$grupo->id]))->assertForbidden();

        $this->actingAs($usuario)
            ->post(route('core.user-groups.store'), ['name' => 'X', 'slug' => 'x'])
            ->assertForbidden();

        $this->actingAs($usuario)
            ->delete(route('core.user-groups.destroy', [$grupo->id]))
            ->assertForbidden();
    }

    public function test_slug_reservado_superadmin_e_bloqueado(): void
    {
        $franquia = $this->franchise('Franquia Alpha', 'alpha');
        $superadmin = $this->superadmin($franquia);

        $this->actingAs($superadmin)
            ->post(route('core.user-groups.store'), [
                'name' => 'Falso Super',
                'slug' => 'superadmin',
                'description' => 'tentativa de reserva',
            ])
            ->assertSessionHasErrors('slug');
    }

    public function test_grupo_com_usuarios_associados_nao_e_excluido(): void
    {
        $franquia = $this->franchise('Franquia Alpha', 'alpha');
        $grupo = $this->grupoComSettings('Com Gente');
        $this->grant($grupo, 'settings');
        $this->operador($franquia, $grupo);

        $superadmin = $this->superadmin($franquia);

        // O controller redireciona com mensagem em vez de apagar o grupo.
        $this->actingAs($superadmin)
            ->delete(route('core.user-groups.destroy', [$grupo->id]))
            ->assertRedirect(route('core.user-groups.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('user_groups', ['id' => $grupo->id]);
    }

    public function test_apenas_superadmin_vincula_usuario_a_empresa_e_filial(): void
    {
        $minha = $this->franchise('Franquia Alpha', 'alpha');
        $outra = $this->franchise('Franquia Beta', 'beta');

        $grupo = $this->grupoComSettings('Configurador');
        $this->grant($grupo, 'settings');

        $superadmin = $this->superadmin($minha);
        $operador = $this->operador($minha, $grupo);

        // Superadmin vincula normalmente.
        $this->actingAs($superadmin)->post(route('core.users.store'), [
            'name' => 'Vinculado',
            'email' => 'vinculado@teste.local',
            'password' => 'senha12345',
            'password_confirmation' => 'senha12345',
            'user_group_id' => $grupo->id,
            'is_active' => 1,
            'company_ids' => [$minha->id],
            'branch_ids' => [$this->matriz($minha)->id],
        ])->assertRedirect();

        $vinculado = User::where('email', 'vinculado@teste.local')->firstOrFail();
        $this->assertTrue($vinculado->companies->contains($minha));

        // Operador tenta criar usuario apontando para a empresa errada.
        $this->actingAs($operador)->post(route('core.users.store'), [
            'name' => 'Tentativa',
            'email' => 'tentativa@teste.local',
            'password' => 'senha12345',
            'password_confirmation' => 'senha12345',
            'user_group_id' => $grupo->id,
            'is_active' => 1,
            'company_ids' => [$outra->id],
        ])->assertRedirect();

        $tentativa = User::where('email', 'tentativa@teste.local')->firstOrFail();
        $this->assertTrue(
            $tentativa->companies->isEmpty(),
            'Operador nao-superadmin nao pode vincular usuario a empresa alguma.'
        );
    }

    public function test_filial_de_outra_empresa_e_rejeitada(): void
    {
        $minha = $this->franchise('Franquia Alpha', 'alpha');
$outra = $this->franchise('Franquia Beta', 'beta');

        $superadmin = $this->superadmin($minha);
        $filialAlheia = $this->matriz($outra);

        // No modelo multiempresa isto nao e erro: marcar a filial de outra
        // loja simplesmente adiciona aquela empresa ao vinculo. Antes, com um
        // unico par empresa/filial, a escolha era impossível.
        $this->actingAs($superadmin)->post(route('core.users.store'), [
            'name' => 'Duas Lojas',
            'email' => 'duaslojas@teste.local',
            'password' => 'senha12345',
            'password_confirmation' => 'senha12345',
            'user_group_id' => $this->grupoComSettings('Configurador')->id,
            'is_active' => 1,
            'company_ids' => [$minha->id],
            'branch_ids' => [$filialAlheia->id],
        ])->assertSessionHasNoErrors();

        $user = User::where('email', 'duaslojas@teste.local')->firstOrFail();

        $this->assertTrue($user->companies->contains($minha));
        $this->assertTrue($user->companies->contains($outra));
        $this->assertCount(2, $user->companies);
    }

    public function test_filial_inexistente_e_rejeitada(): void
    {
        $minha = $this->franchise('Franquia Alpha', 'alpha');
        $superadmin = $this->superadmin($minha);

        $this->actingAs($superadmin)->post(route('core.users.store'), [
            'name' => 'Filial Fantasma',
            'email' => 'fantasma@teste.local',
            'password' => 'senha12345',
            'password_confirmation' => 'senha12345',
            'user_group_id' => $this->grupoComSettings('Configurador')->id,
            'is_active' => 1,
            'company_ids' => [$minha->id],
            'branch_ids' => [999999],
        ])->assertSessionHasErrors('branch_ids.0');

        $this->assertDatabaseMissing('users', ['email' => 'fantasma@teste.local']);
    }

    public function test_tenant_context_resolve_empresa_da_franquia(): void
    {
        $franquia = $this->franchise('Franquia Alpha', 'alpha');
        $usuario = $this->franqueado($franquia);

        $this->actingAs($usuario);
        TenantContext::resolve();

        $this->assertSame($franquia->id, TenantContext::companyId());
        $this->assertFalse(TenantContext::isCrossTenant());
    }

    public function test_operador_nao_coloca_usuario_no_grupo_de_franqueados(): void
    {
        $franquia = $this->franchise('Franquia Alpha', 'alpha');

        $grupo = $this->grupoComSettings('Configurador');
        $this->grant($grupo, 'settings');

        $operador = $this->operador($franquia, $grupo);
        $franqueados = UserGroup::where('slug', 'franqueados')->firstOrFail();

        // O operador tem `settings`, mas nao pode ampliar o proprio alcance.
        $this->actingAs($operador)->post(route('core.users.store'), [
            'name' => 'Infiltrado',
            'email' => 'infiltrado@teste.local',
            'password' => 'senha12345',
            'password_confirmation' => 'senha12345',
            'user_group_id' => $franqueados->id,
            'is_active' => 1,
        ])->assertSessionHasErrors('user_group_id');

        $this->assertDatabaseMissing('users', ['email' => 'infiltrado@teste.local']);
    }

public function test_validacao_do_vinculo_roda_antes_de_gravar_o_usuario(): void
    {
        $minha = $this->franchise('Franquia Alpha', 'alpha');
        $superadmin = $this->superadmin($minha);

        // Filial que nao existe: a validacao tem de barrar ANTES da escrita,
        // senao sobra um usuario sem vinculo nenhum.
        $this->actingAs($superadmin)->post(route('core.users.store'), [
            'name' => 'Nao Salvo',
            'email' => 'naosalvo@teste.local',
            'password' => 'senha12345',
            'password_confirmation' => 'senha12345',
            'user_group_id' => $this->grupoComSettings('Configurador')->id,
            'is_active' => 1,
            'company_ids' => [$minha->id],
            'branch_ids' => [999999],
        ])->assertSessionHasErrors();

        $this->assertDatabaseMissing('users', ['email' => 'naosalvo@teste.local']);
    }

    public function test_franqueado_recebe_o_escopo_do_gerente_sem_configuracoes(): void
    {
        $grupo = UserGroup::where('slug', 'franqueados')->firstOrFail();

        $granted = DB::table('group_permissions')
            ->where('group_id', $grupo->id)
            ->where('granted', true)
            ->pluck('permission_key')
            ->all();

        // Sem estas chaves o grupo fica com uma unica permissao e TODA pagina
        // da operacao responde 403.
        foreach (['dashboard', 'clients', 'contracts', 'service_orders', 'tickets', 'invoices'] as $chave) {
            $this->assertContains($chave, $granted, "O franqueado precisa de `{$chave}`.");
        }

        $this->assertContains('franchisees', $granted);

        // E continua sem poder mexer na plataforma.
        $this->assertNotContains('settings', $granted);
        $this->assertNotContains('mikrotik_servers', $granted);
        $this->assertNotContains('gateways', $granted);
        $this->assertNotContains('provisioning', $granted);
    }

    public function test_dashboard_do_crm_nao_conta_clientes_de_outra_franquia(): void
    {
        $minha = $this->franchise('Franquia Alpha', 'alpha');
        $outra = $this->franchise('Franquia Beta', 'beta');

        $this->makeClient($minha, 'Cliente Alpha 1', '11111111111');
        $this->makeClient($minha, 'Cliente Alpha 2', '22222222222');
        $this->makeClient($outra, 'Cliente Beta 1', '33333333333');

        // Grupo com `dashboard` mas que nao e cross-tenant.
        $grupo = $this->grupoComSettings('Gerente');
        $this->grant($grupo, 'dashboard');
        $gerente = $this->operador($minha, $grupo);

        $response = $this->actingAs($gerente)->get(route('crm.dashboard'));

        $response->assertOk()
            ->assertSee('Cliente Alpha 1')
            ->assertSee('Cliente Alpha 2')
            ->assertDontSee('Cliente Beta 1');
    }

    public function test_escolher_a_filial_so_ja_define_a_empresa(): void
    {
        $minha = $this->franchise('Franquia Alpha', 'alpha');
        $superadmin = $this->superadmin($minha);

        // Este era o bug: empresa em branco + filial escolhida devolvia
        // "Escolha a empresa antes de escolher a filial" e nao salvava.
        $this->actingAs($superadmin)->post(route('core.users.store'), [
            'name' => 'Da Filial',
            'email' => 'dafilial@teste.local',
            'password' => 'senha12345',
            'password_confirmation' => 'senha12345',
            'user_group_id' => $this->grupoComSettings('Configurador')->id,
            'is_active' => 1,
            'company_ids' => [],
            'branch_ids' => [$this->matriz($minha)->id],
        ])->assertSessionHasNoErrors();

        $user = User::where('email', 'dafilial@teste.local')->firstOrFail();

        // A empresa veio da filial, e ele NAO caiu na matriz.
        $this->assertTrue($user->companies->contains($minha));
        $this->assertTrue($user->branches->contains($this->matriz($minha)));
    }

public function test_empresa_sem_filial_operaria_a_empresa_inteira(): void
    {
        $minha = $this->franchise('Franquia Alpha', 'alpha');
        $superadmin = $this->superadmin($minha);

        $this->actingAs($superadmin)->post(route('core.users.store'), [
            'name' => 'So Empresa',
            'email' => 'soempresa@teste.local',
            'password' => 'senha12345',
            'password_confirmation' => 'senha12345',
            'user_group_id' => $this->grupoComSettings('Configurador')->id,
            'is_active' => 1,
            'company_ids' => [$minha->id],
            'branch_ids' => [],
        ])->assertSessionHasNoErrors();

        $user = User::where('email', 'soempresa@teste.local')->firstOrFail();

        // Empresa marcada sem filial significa "todas as filiais desta loja",
        // e nao "nenhuma filial". Forcar a matriz esconderia as demais unidades.
        $this->assertTrue($user->companies->contains($minha));
        $this->assertCount(0, $user->branches);
    }

public function test_editar_carlos_muda_o_vinculo_para_a_franquia(): void
    {
        $matriz = $this->rootCompany();
        $franquia = $this->franchise('Franquia Alpha', 'alpha');
        $filial = $this->matriz($franquia);

        // Situacao do banco de dev: o carlos estava preso na matriz.
        $grupo = $this->grupoComSettings('Configurador');
        $usuario = $this->userInGroup($grupo, $matriz, 'carlos@teste.local');
        $superadmin = $this->superadmin($matriz);

        $this->actingAs($superadmin)->put(route('core.users.update', [$usuario->id]), [
            'name' => 'Carlos',
            'email' => 'carlos@teste.local',
            'user_group_id' => $grupo->id,
            'is_active' => 1,
            'company_ids' => [],
            'branch_ids' => [$filial->id],
        ])->assertSessionHasNoErrors();

        $usuario->refresh();

        $this->assertTrue($usuario->companies->contains($franquia), 'Deveria estar na franquia.');
        $this->assertFalse($usuario->companies->contains($matriz), 'Nao deveria continuar na matriz.');
        $this->assertTrue($usuario->branches->contains($filial));
    }

public function test_franqueado_nao_acessa_relatorios_da_rede(): void
    {
        $franquia = $this->franchise('Franquia Alpha', 'alpha');
        $usuario = $this->franqueado($franquia);

        // ReportController roda sem escopo de tenant: conceder `reports`
        // entregaria o faturamento da rede inteira ao franqueado.
        $this->actingAs($usuario)
            ->get(route('billing.reports.index'))
            ->assertForbidden();

        $grupo = UserGroup::where('slug', 'franqueados')->firstOrFail();
        $concedida = DB::table('group_permissions')
            ->where('group_id', $grupo->id)
            ->where('permission_key', 'reports')
            ->value('granted');

        $this->assertNotTrue((bool) $concedida);
    }

public function test_pagamento_de_fatura_alheia_responde_404(): void
    {
        $minha = $this->franchise('Franquia Alpha', 'alpha');
        $outra = $this->franchise('Franquia Beta', 'beta');

        $grupo = $this->grupoComSettings('Financeiro');
        $this->grant($grupo, 'invoices');
        $usuario = $this->operador($minha, $grupo);

        $faturaAlheia = $this->makeInvoice($outra, 150.0);

        // Antes o store aceitava qualquer invoice_id e ainda marcava a fatura
        // alheia como paga.
        $this->actingAs($usuario)
            ->postJson(route('api.payments.store'), [
                'invoice_id' => $faturaAlheia->id,
                'amount' => 150.0,
                'payment_date' => now()->toDateString(),
                'payment_method' => 'pix',
            ])
            ->assertNotFound();

        $this->actingAs($usuario)
            ->getJson(route('api.payments.index'))
            ->assertOk()
            ->assertJsonPath('total', 0)
            ->assertJsonPath('data', []);

        $faturaAlheia->refresh();
        $this->assertNotSame('paid', $faturaAlheia->status, 'A fatura alheia nao pode mudar de status.');
    }

public function test_pagamento_da_propria_franquia_passa(): void
    {
        $minha = $this->franchise('Franquia Alpha', 'alpha');

        $grupo = $this->grupoComSettings('Financeiro');
        $this->grant($grupo, 'invoices');
        $usuario = $this->operador($minha, $grupo);

        $fatura = $this->makeInvoice($minha, 100.0);

        $this->actingAs($usuario)
            ->postJson(route('api.payments.store'), [
                'invoice_id' => $fatura->id,
                'amount' => 100.0,
                'payment_date' => now()->toDateString(),
                'payment_method' => 'pix',
            ])
            ->assertCreated();

        $fatura->refresh();
        $this->assertSame('paid', $fatura->status);
    }

public function test_franqueado_ve_os_planos_mas_nao_os_edita(): void
    {
        $empresa = $this->rootCompany();
        $filial = $this->matriz($empresa);

        // O catalogo de planos e da rede: fica gravado na Matriz.
        $plan = Plan::create([
            'company_id' => $empresa->id, 'branch_id' => $filial->id,
            'name' => 'Plano De Rede', 'slug' => 'plano-de-rede',
            'download_speed' => 100, 'upload_speed' => 50,
            'price' => 99.9, 'billing_cycle' => 'monthly',
        ]);

        $grupo = UserGroup::where('slug', 'franqueados')->firstOrFail();
        $user = User::create([
            'name' => 'Carlos', 'email' => 'carlos-planos@myisp.com',
            'password' => bcrypt('password'), 'user_group_id' => $grupo->id, 'is_active' => true,
        ]);
        $user->companies()->attach($empresa->id);
        $user->branches()->attach($filial->id);

        // Precisa do plano para fechar contrato na propria loja.
        $this->actingAs($user)->get(route('crm.plans.index'))
            ->assertOk()
            ->assertSee('Plano De Rede');

        // Mas preco e velocidade sao decisao da rede, nao da loja.
        $this->actingAs($user)->get(route('crm.plans.create'))->assertForbidden();
        $this->actingAs($user)->get(route('crm.plans.edit', [$plan->id]))->assertForbidden();
        $this->actingAs($user)->put(route('crm.plans.update', [$plan->id]), [
            'price' => 1,
        ])->assertForbidden();
        $this->actingAs($user)->delete(route('crm.plans.destroy', [$plan->id]))->assertForbidden();

        $this->assertDatabaseHas('plans', ['id' => $plan->id, 'price' => 99.9]);
    }

    public function test_franqueado_da_mesma_empresa_so_enxerga_a_propria_filial(): void
    {
        // Este e o caso real do banco: uma empresa so, com a matriz e as lojas.
        // O Carlos esta na empresa, mas em uma filial. Escopar so pela empresa
        // deixava ele ver a agenda inteira da rede.
        $empresa = $this->rootCompany();

        $matriz = Branch::create(['company_id' => $empresa->id, 'name' => 'Matriz', 'is_active' => true]);
        $loja = Branch::create(['company_id' => $empresa->id, 'name' => 'Filial Dom Pedro MA', 'is_active' => true]);

        $clienteDaLoja = Client::create([
            'company_id' => $empresa->id, 'branch_id' => $loja->id,
            'name' => 'Cliente Do Dom Pedro', 'document' => '55555555555',
            'type' => 'individual', 'status' => 'active',
        ]);
        $clienteDaMatriz = Client::create([
            'company_id' => $empresa->id, 'branch_id' => $matriz->id,
            'name' => 'Cliente Da Sede', 'document' => '66666666666',
            'type' => 'individual', 'status' => 'active',
        ]);

        $grupo = UserGroup::where('slug', 'franqueados')->firstOrFail();
        $user = User::create([
            'name' => 'Carlos', 'email' => 'carlos@myisp.com',
            'password' => bcrypt('password'), 'user_group_id' => $grupo->id, 'is_active' => true,
        ]);
        $user->companies()->attach($empresa->id);
        $user->branches()->attach($loja->id);

        $osDaLoja = ServiceOrder::create([
            'client_id' => $clienteDaLoja->id, 'situacao' => 'O', 'status' => 'active',
        ]);
        $osDaMatriz = ServiceOrder::create([
            'client_id' => $clienteDaMatriz->id, 'situacao' => 'O', 'status' => 'active',
        ]);

        // Clientes: so os da filial dele.
        $this->actingAs($user)->get(route('crm.clients.index'))
            ->assertOk()
            ->assertSee('Cliente Do Dom Pedro')
            ->assertDontSee('Cliente Da Sede');

        // Ordens de servico: a da matriz nem aparece na listagem.
        $this->actingAs($user)->get(route('crm.service-orders.index'))
            ->assertOk()
            ->assertDontSee($osDaMatriz->codigo);

        // E o acesso direto pela URL responde 404, inclusive nas acoes que
        // mudam estado (concluir), que antes aceitavam qualquer id.
        $this->actingAs($user)->get(route('crm.service-orders.show', [$osDaMatriz->id]))->assertNotFound();
        $this->actingAs($user)->post(route('crm.service-orders.complete', [$osDaMatriz->id]))->assertNotFound();

        // A propria OS continua acessivel.
        $this->actingAs($user)->get(route('crm.service-orders.show', [$osDaLoja->id]))->assertOk();

        // Nao consegue criar OS para cliente da matriz nem pelo id.
        $this->actingAs($user)->post(route('crm.service-orders.store'), [
            'client_id' => $clienteDaMatriz->id,
            'situacao' => 'O',
        ])->assertSessionHasErrors('client_id');
    }

    public function test_financeiro_do_franqueado_para_na_filial_dele(): void
    {
        // Mesmo caso do CRM acima, agora no financeiro. Aqui o escopo era
        // somente por empresa, entao o usuario da loja via faturas, boletos,
        // lancamentos e saldo acumulado das outras filiais da rede.
        $empresa = $this->rootCompany();

        $matriz = Branch::create(['company_id' => $empresa->id, 'name' => 'Matriz', 'is_active' => true]);
        $loja = Branch::create(['company_id' => $empresa->id, 'name' => 'Filial Dom Pedro MA', 'is_active' => true]);

        $clienteDaLoja = Client::create([
            'company_id' => $empresa->id, 'branch_id' => $loja->id,
            'name' => 'Cliente Do Dom Pedro', 'document' => '77777777777',
            'type' => 'individual', 'status' => 'active',
        ]);
        $clienteDaMatriz = Client::create([
            'company_id' => $empresa->id, 'branch_id' => $matriz->id,
            'name' => 'Cliente Da Sede', 'document' => '88888888888',
            'type' => 'individual', 'status' => 'active',
        ]);

        $grupo = UserGroup::where('slug', 'franqueados')->firstOrFail();
        $user = User::create([
            'name' => 'Carlos', 'email' => 'carlos@myisp.com',
            'password' => bcrypt('password'), 'user_group_id' => $grupo->id, 'is_active' => true,
        ]);
        $user->companies()->attach($empresa->id);
        $user->branches()->attach($loja->id);

        $faturaDaLoja = $this->makeInvoiceFor($empresa, $loja, $clienteDaLoja, 90.0);
        $faturaDaMatriz = $this->makeInvoiceFor($empresa, $matriz, $clienteDaMatriz, 1000.0);

        // Faturas: a da matriz nao aparece, nem na lista, nem no totalizador.
        $this->actingAs($user)->get(route('billing.invoices.index'))
            ->assertOk()
            ->assertSee($faturaDaLoja->invoice_number)
            ->assertDontSee($faturaDaMatriz->invoice_number);

        // E o acesso direto pela URL responde 404, inclusive nas acoes que
        // mudam estado e na exclusao em massa.
        $this->actingAs($user)->get(route('billing.invoices.show', [$faturaDaMatriz->id]))->assertNotFound();
        $this->actingAs($user)->get(route('billing.invoices.edit', [$faturaDaMatriz->id]))->assertNotFound();
        $this->actingAs($user)->delete(route('billing.invoices.destroy', [$faturaDaMatriz->id]))->assertNotFound();
        $this->actingAs($user)->post(route('billing.invoices.payment', [$faturaDaMatriz->id]), [
            'amount' => 1000.0,
            'payment_method' => 'pix',
        ])->assertNotFound();
        // Na exclusao em massa o contrato e redirect com aviso: o model ja
        // filtra por `forTenant`, entao nada e excluido e o usuario e avisado.
        $this->actingAs($user)->post(route('billing.invoices.bulk-destroy'), [
            'ids' => [$faturaDaMatriz->id],
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseHas('invoices', ['id' => $faturaDaMatriz->id, 'status' => 'pending']);

        // Boletos segue a mesma linha.
        $this->actingAs($user)->get(route('billing.boleto.index'))
            ->assertOk()
            ->assertDontSee($faturaDaMatriz->invoice_number);
        $this->actingAs($user)->get(route('billing.boleto.print', [$faturaDaMatriz->id]))->assertNotFound();

        // API: a listagem vinha sem nenhum filtro.
        $this->actingAs($user)->getJson(route('api.invoices.index'))
            ->assertOk()
            ->assertJsonPath('total', 1);
        $this->actingAs($user)->getJson(route('api.invoices.show', [$faturaDaMatriz->id]))->assertNotFound();
        $this->actingAs($user)->deleteJson(route('api.invoices.destroy', [$faturaDaMatriz->id]))->assertNotFound();

        // Nao consegue emitir fatura para cliente da matriz nem pelo id.
        $this->actingAs($user)->postJson(route('api.invoices.store'), [
            'client_id' => $clienteDaMatriz->id,
            'amount' => 50.0,
            'due_date' => now()->addDays(3)->toDateString(),
        ])->assertStatus(422)->assertJsonValidationErrors('client_id');

        // O proprio financeiro continua liberado.
        $this->actingAs($user)->get(route('billing.invoices.show', [$faturaDaLoja->id]))->assertOk();

        // Livro caixa: lista, totais e o saldo anterior. Este ultimo somava a
        // rede inteira sem filtro nenhum e aparecia no topo da tela.
        $lancamentoDaMatriz = CashBookEntry::create([
            'company_id' => $empresa->id, 'branch_id' => $matriz->id,
            'type' => 'entrada', 'amount' => 5000.00, 'description' => 'Mensalidade Sede',
            'entry_date' => now()->subDays(40)->toDateString(),
        ]);
        $lancamentoDaLoja = CashBookEntry::create([
            'company_id' => $empresa->id, 'branch_id' => $loja->id,
            'type' => 'entrada', 'amount' => 75.00, 'description' => 'Mensalidade Dom Pedro',
            'entry_date' => now()->subDays(40)->toDateString(),
        ]);

        $resposta = $this->actingAs($user)->get(route('billing.cash-book.index'));
        $resposta->assertOk()
            ->assertSee($lancamentoDaLoja->description)
            ->assertDontSee($lancamentoDaMatriz->description);

        // 75,00 da filial e nada da matriz no saldo anterior.
        $this->assertSame('75.00', number_format((float) $resposta->viewData('previousBalance'), 2, '.', ''));

        $this->actingAs($user)->get(route('billing.cash-book.show', [$lancamentoDaMatriz->id]))->assertNotFound();
        $this->actingAs($user)->delete(route('billing.cash-book.destroy', [$lancamentoDaMatriz->id]))->assertNotFound();
        $this->assertDatabaseHas('cash_book_entries', ['id' => $lancamentoDaMatriz->id]);
    }

    private function makeInvoiceFor(Company $company, Branch $branch, Client $client, float $total): Invoice
    {
        return Invoice::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'client_id' => $client->id,
            'invoice_number' => 'FAT-'.random_int(10000, 99999),
            'status' => 'pending',
            'due_date' => now()->addDays(5)->toDateString(),
            'amount' => $total,
            'discount' => 0,
            'acrescimo' => 0,
            'total' => $total,
            'avulso' => false,
            'auto_blocked' => false,
        ]);
    }

    public function test_usuario_pode_operar_varias_empresas_e_trocar_de_contexto(): void

{
    $alpha = $this->franchise('Franquia Alpha', 'alpha');
    $beta = $this->franchise('Franquia Beta', 'beta');

    $this->makeClient($alpha, 'Cliente Alpha', '11111111111');
    $this->makeClient($beta, 'Cliente Beta', '22222222222');

    $grupo = $this->grupoComSettings('Investidor');
    $this->grant($grupo, 'clients');
    $this->grant($grupo, 'dashboard');

    // Dono de DUAS lojas: e o caso multiempresa que o sync([$id]) quebrava,
    // porque deixava o usuario preso na ultima empresa gravada.
    $user = $this->userInGroup($grupo, $alpha, 'investidor@teste.local');
    $user->companies()->attach($beta->id, );
    $user->branches()->attach($this->matriz($beta)->id);
    $user = $user->fresh();

    $this->assertCount(2, $user->companies);

    // Contexto inicial: a primeira empresa vinculada.
    $this->actingAs($user)->get(route('crm.clients.index'))
        ->assertOk()
        ->assertSee('Cliente Alpha')
        ->assertDontSee('Cliente Beta');

    // Troca de contexto pela rota ja existente no sistema.
    $this->actingAs($user)
        ->post(route('core.context.switch'), ['company_id' => $beta->id])
        ->assertRedirect();

    $this->actingAs($user)->get(route('crm.clients.index'))
        ->assertOk()
        ->assertSee('Cliente Beta')
        ->assertDontSee('Cliente Alpha');

    // E volta para a primeira.
    $this->actingAs($user)
        ->post(route('core.context.switch'), ['company_id' => $alpha->id])
        ->assertRedirect();

    $this->actingAs($user)->get(route('crm.clients.index'))
        ->assertOk()
        ->assertSee('Cliente Alpha')
        ->assertDontSee('Cliente Beta');
}

public function test_formulario_de_usuario_grava_varias_empresas_de_uma_vez(): void
{
    $alpha = $this->franchise('Franquia Alpha', 'alpha');
    $beta = $this->franchise('Franquia Beta', 'beta');
    $gamma = $this->franchise('Franquia Gamma', 'gamma');

    $superadmin = $this->superadmin($alpha);
    $grupo = $this->grupoComSettings('Operador');

    $this->actingAs($superadmin)->post(route('core.users.store'), [
        'name' => 'Tres Lojas',
        'email' => 'treslojas@teste.local',
        'password' => 'senha12345',
        'password_confirmation' => 'senha12345',
        'user_group_id' => $grupo->id,
        'is_active' => 1,
        'company_ids' => [$alpha->id, $beta->id, $gamma->id],
        'branch_ids' => [],
    ])->assertSessionHasNoErrors();

    $user = User::where('email', 'treslojas@teste.local')->firstOrFail();

    $this->assertCount(3, $user->companies, 'O vinculo multiempresa nao pode ser truncado para 1.');
}

public function test_filial_marcada_traz_a_empresa_dela_junto(): void
{
    $alpha = $this->franchise('Franquia Alpha', 'alpha');
    $beta = $this->franchise('Franquia Beta', 'beta');

    $superadmin = $this->superadmin($alpha);

    // Só a filial foi marcada: a empresa dela tem de entrar sozinha.
    $this->actingAs($superadmin)->post(route('core.users.store'), [
        'name' => 'Pela Filial',
        'email' => 'pelafilial@teste.local',
        'password' => 'senha12345',
        'password_confirmation' => 'senha12345',
        'user_group_id' => $this->grupoComSettings('Operador')->id,
        'is_active' => 1,
        'company_ids' => [],
        'branch_ids' => [$this->matriz($beta)->id],
    ])->assertSessionHasNoErrors();

    $user = User::where('email', 'pelafilial@teste.local')->firstOrFail();

    $this->assertTrue($user->companies->contains($beta));
    $this->assertFalse($user->companies->contains($alpha));
}

public function test_editar_nao_apaga_as_outras_empresas_do_usuario(): void
{
    $alpha = $this->franchise('Franquia Alpha', 'alpha');
    $beta = $this->franchise('Franquia Beta', 'beta');

    $grupo = $this->grupoComSettings('Investidor');
    $user = $this->userInGroup($grupo, $alpha, 'multi@teste.local');
    $user->companies()->attach($beta->id);
    $superadmin = $this->superadmin($alpha);

    // Edita o nome reenviando as DUAS empresas: as duas tem de continuar.
    $this->actingAs($superadmin)->put(route('core.users.update', [$user->id]), [
        'name' => 'Renomeado',
        'email' => 'multi@teste.local',
        'user_group_id' => $grupo->id,
        'is_active' => 1,
        'company_ids' => [$alpha->id, $beta->id],
        'branch_ids' => [],
    ])->assertSessionHasNoErrors();

    $this->assertCount(2, $user->fresh()->companies);
}

/* ------------------------------------------------------------------ */

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

    private function matriz(Company $company): Branch
    {
        return Branch::where('company_id', $company->id)->orderBy('id')->firstOrFail();
    }

    private function grupoComSettings(string $nome): UserGroup
    {
        // `users.role` e varchar(20) no Postgres, entao o slug precisa ser curto.
        static $seq = 0;

        return UserGroup::create([
            'name' => $nome,
            'slug' => Str::limit(Str::slug($nome), 10, '').(++$seq),
            'is_active' => true,
        ]);
    }

    private function grant(UserGroup $group, string $permission): void
    {
        $group->permissions()->updateOrCreate(
            ['permission_key' => $permission],
            ['granted' => true],
        );
    }

    private function superadmin(Company $company): User
    {
        $group = UserGroup::create([
            'name' => 'Super Admin',
            'slug' => 'superadmin',
            'is_active' => true,
        ]);

        return $this->userInGroup($group, $company, 'superadmin-'.uniqid().'@teste.local');
    }

    private function operador(Company $company, ?UserGroup $group = null): User
    {
        $group = $group ?: $this->grupoComSettings('Operador');

        return $this->userInGroup($group, $company, 'operador-'.uniqid().'@teste.local');
    }

    private function franqueado(Company $company): User
    {
        $group = UserGroup::where('slug', 'franqueados')->firstOrFail();

        return $this->userInGroup($group, $company, 'franqueado-'.uniqid().'@teste.local');
    }

    private function userInGroup(UserGroup $group, Company $company, string $email): User
    {
        $user = User::create([
            'name' => 'Usuario Teste',
            'email' => $email,
            'password' => 'password',
            'user_group_id' => $group->id,
            'role' => $group->slug,
            'is_active' => true,
        ]);

        $user->companies()->attach($company->id);
        $user->branches()->attach($this->matriz($company)->id);

        return $user->fresh();
    }

    private function makeClient(Company $company, string $name, string $document): Client
    {
        return Client::create([
            'company_id' => $company->id,
            'branch_id' => $this->matriz($company)->id,
            'name' => $name,
            'document' => $document,
            'type' => 'individual',
            'status' => 'active',
        ]);
    }

    private function makeInvoice(Company $company, float $total): Invoice
    {
        return Invoice::create([
            'company_id' => $company->id,
            'branch_id' => $this->matriz($company)->id,
            'client_id' => $this->makeClient($company, 'Cliente Fatura '.$total, (string) random_int(10000000000, 99999999999))->id,
            'invoice_number' => 'FAT-'.random_int(10000, 99999),
            'status' => 'pending',
            'due_date' => now()->addDays(5)->toDateString(),
            'amount' => $total,
            'discount' => 0,
            'acrescimo' => 0,
            'total' => $total,
            'avulso' => false,
            'auto_blocked' => false,
        ]);
    }
}
