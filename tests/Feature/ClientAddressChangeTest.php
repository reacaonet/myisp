<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\Address;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Company;
use Modules\Core\Models\UserGroup;
use Modules\Core\Services\TenantContext;
use Modules\CRM\Models\Client;
use Modules\CRM\Models\Ticket;
use Tests\TestCase;

/**
 * Mudanca de endereco do cliente: vira chamado e so um admin aplica.
 *
 * O ponto sensivel aqui nao e o formulario, e o limite. `tickets` nao tem
 * `company_id`, entao tanto o cliente (para nao abrir chamado em nome de outro)
 * quanto o admin (para nao reescrever endereco de outra franquia) precisam de
 * recorte proprio.
 */
class ClientAddressChangeTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        TenantContext::forget();
        session()->forget(['current_company_id', 'current_branch_id']);
    }

    public function test_perfil_mostra_o_endereco_atual(): void
    {
        $client = $this->clientWithAddress();

        $this->actingAs($client, 'client')->get(route('crm.portal.profile'))
            ->assertOk()
            ->assertSee('Rua das Flores')
            ->assertSee('Sao Luis')
            ->assertSee('Solicitar mudanca');
    }

    public function test_cliente_envia_solicitacao_e_vira_chamado(): void
    {
        $client = $this->clientWithAddress();

        $response = $this->actingAs($client, 'client')
            ->post(route('crm.portal.profile.address.store'), [
                'street' => 'Avenida Principal',
                'number' => '1500',
                'referencia' => 'Bloco B',
                'complement' => 'Sala 12',
                'neighborhood' => 'Centro',
                'city' => 'Imperatriz',
                'state' => 'ma',
                'zipcode' => '65000000',
                'reason' => 'Mudei de casa',
            ]);

        $ticket = Ticket::where('client_id', $client->id)->latest('id')->firstOrFail();

        $response->assertRedirect(route('crm.portal.tickets.show', $ticket));
        $this->assertSame(Ticket::CATEGORY_ADDRESS, $ticket->category);
        $this->assertSame('open', $ticket->status);
        $this->assertSame('Mudei de casa', $ticket->description);

        $proposto = $ticket->proposedAddress();
        $this->assertSame('Avenida Principal', $proposto['street']);
        $this->assertSame('MA', $proposto['state'], 'UF deve entrar em maiuscula');
        $this->assertSame('Bloco B', $proposto['referencia']);

        // A primeira mensagem entra no chamado, para o cliente ter rastro.
        $this->assertSame('client', $ticket->messages()->first()->sender_type);

        // E o endereco cadastrado NAO muda com a solicitacao.
        $this->assertSame('Rua das Flores', $client->fresh()->addresses()->first()->street);
    }

    public function test_formulario_bloqueia_segunda_solicitacao_em_aberto(): void
    {
        $client = $this->clientWithAddress();

        $this->actingAs($client, 'client')->get(route('crm.portal.profile.address'))->assertOk();

        $this->actingAs($client, 'client')
            ->post(route('crm.portal.profile.address.store'), $this->payload())
            ->assertRedirect();

        $this->assertSame(1, Ticket::where('client_id', $client->id)->count());

        $pending = Ticket::where('client_id', $client->id)->firstOrFail();

        // Com uma solicitacao em aberto, o formulario e o POST nao aceitam mais.
        $this->actingAs($client, 'client')->get(route('crm.portal.profile.address'))
            ->assertRedirect(route('crm.portal.tickets.show', $pending));

        $this->actingAs($client, 'client')
            ->post(route('crm.portal.profile.address.store'), $this->payload())
            ->assertRedirect(route('crm.portal.tickets.show', $pending));

        $this->assertSame(1, Ticket::where('client_id', $client->id)->count());
    }

    public function test_perfil_mostra_a_solicitacao_pendente(): void
    {
        $client = $this->clientWithAddress();

        $this->actingAs($client, 'client')
            ->post(route('crm.portal.profile.address.store'), $this->payload());

        $ticket = Ticket::where('client_id', $client->id)->firstOrFail();

        $this->actingAs($client, 'client')->get(route('crm.portal.profile'))
            ->assertOk()
            ->assertSee('Solicitacao em analise')
            ->assertSee($ticket->codigo)
            // O botao some enquanto houver pedido em aberto.
            ->assertDontSee('Solicitar mudanca</a>');
    }

    public function test_cliente_nao_abre_chamado_em_nome_de_outro(): void
    {
        $client = $this->clientWithAddress();
        $alheio = $this->clientWithAddress('Cliente Alheio');

        $this->actingAs($client, 'client')
            ->post(route('crm.portal.profile.address.store'), $this->payload());

        $ticket = Ticket::where('client_id', $alheio->id)->count();
        $this->assertSame(0, $ticket, 'nenhum chamado pode nascer para outro cliente');
    }

    public function test_campos_obrigatorios_do_endereco(): void
    {
        $client = $this->clientWithAddress();

        $this->actingAs($client, 'client')
            ->post(route('crm.portal.profile.address.store'), ['street' => 'So o logradouro'])
            ->assertSessionHasErrors(['neighborhood', 'city', 'state', 'zipcode']);

        $this->assertSame(0, Ticket::where('client_id', $client->id)->count());
    }

    public function test_admin_aplica_o_endereco_e_resolve_o_chamado(): void
    {
        $client = $this->clientWithAddress();
        $this->actingAs($client, 'client')->post(route('crm.portal.profile.address.store'), $this->payload());
        $ticket = Ticket::where('client_id', $client->id)->firstOrFail();

        $this->actingAs($this->supportAgent())->post(route('crm.tickets.address.apply', $ticket))
            ->assertRedirect(route('crm.tickets.show', $ticket));

        $address = $client->fresh()->addresses()->first();
        $this->assertSame('Avenida Principal', $address->street);
        $this->assertSame('1500', $address->number);
        $this->assertSame('Centro', $address->neighborhood);
        $this->assertSame('Imperatriz', $address->city);
        $this->assertSame('MA', $address->state);
        $this->assertSame('65000000', $address->zipcode);
        // `referencia` era descartada em silencio ao criar endereco.
        $this->assertSame('Bloco B', $address->referencia);

        $this->assertSame('resolved', $ticket->fresh()->status);

        // Continua sendo um endereco so: a mudanca atualiza, nao duplica.
        $this->assertSame(1, $client->fresh()->addresses()->count());
    }

    public function test_admin_cria_endereco_quando_cliente_nao_tem_nenhum(): void
    {
        $company = $this->rootCompany();
        $branch = $this->firstBranch($company);
        $client = $this->client($branch, 'Sem Endereco');

        $ticket = Ticket::create([
            'codigo' => 'CHM-90001',
            'client_id' => $client->id,
            'subject' => 'Solicitacao de mudanca de endereco',
            'description' => 'Primeiro cadastro',
            'status' => 'open',
            'priority' => 'low',
            'category' => Ticket::CATEGORY_ADDRESS,
            'proposed_address' => $this->payload(),
        ]);

        $this->actingAs($this->supportAgent())->post(route('crm.tickets.address.apply', $ticket));

        $this->assertSame(1, $client->fresh()->addresses()->count());
        $this->assertSame('Avenida Principal', $client->fresh()->addresses()->first()->street);
    }

    public function test_admin_recusa_sem_trocar_o_endereco(): void
    {
        $client = $this->clientWithAddress();
        $this->actingAs($client, 'client')->post(route('crm.portal.profile.address.store'), $this->payload());
        $ticket = Ticket::where('client_id', $client->id)->firstOrFail();

        $this->actingAs($this->supportAgent())
            ->post(route('crm.tickets.address.reject', $ticket), ['reason' => 'Fora da area de atuacao'])
            ->assertRedirect(route('crm.tickets.show', $ticket));

        $this->assertSame('closed', $ticket->fresh()->status);
        $this->assertSame('Rua das Flores', $client->fresh()->addresses()->first()->street);
        $this->assertStringContainsString(
            'Fora da area de atuacao',
            // `messages()` ja entra em `orderBy('created_at')` para exibicao
            // cronologica, entao `latest('id')` vira `created_at ASC, id DESC` e
            // so desempata. Se as duas mensagens cairem em segundos diferentes,
            // `first()` devolve a do cliente. `reorder` zera a ordenacao herdada.
            $ticket->fresh()->messages()->where('sender_type', 'admin')->reorder('id', 'desc')->first()->message
        );
    }

    public function test_endereco_aplicado_nao_e_aplicado_de_novo(): void
    {
        $client = $this->clientWithAddress();
        $this->actingAs($client, 'client')->post(route('crm.portal.profile.address.store'), $this->payload());
        $ticket = Ticket::where('client_id', $client->id)->firstOrFail();

        $agent = $this->supportAgent();
        $this->actingAs($agent)->post(route('crm.tickets.address.apply', $ticket));

        // A abertura do chamado ja deixou a mensagem do cliente.
        $mensagens = $ticket->fresh()->messages()->count();

        // Reaplicar mudaria o endereco de novo sem o cliente ter pedido nada.
        $this->actingAs($agent)
            ->post(route('crm.tickets.address.apply', $ticket))
            ->assertRedirect();

        $this->assertSame($mensagens, $ticket->fresh()->messages()->count());
    }

    public function test_admin_de_outra_franquia_nao_aplica_o_endereco(): void
    {
        $client = $this->clientWithAddress();

        // O chamado nasce direto no banco: autenticar como o cliente antes
        // deixaria o contexto estatico de tenant resolvido para a matriz, e o
        // teste passaria a medir o cache e nao o corte entre franchises.
        $ticket = Ticket::create([
            'codigo' => 'CHM-91000',
            'client_id' => $client->id,
            'subject' => 'Solicitacao de mudanca de endereco',
            'description' => 'Mudei de cidade',
            'status' => 'open',
            'priority' => 'low',
            'category' => Ticket::CATEGORY_ADDRESS,
            'proposed_address' => $this->payload(),
        ]);

        $outro = Company::create([
            'name' => 'Franquia de Outro Estado',
            'slug' => 'franquia-'.uniqid(),
            'is_active' => true,
            'is_franchise' => true,
        ]);
        $filial = Branch::create(['company_id' => $outro->id, 'name' => 'Unica', 'is_active' => true]);

        $this->actingAs($this->supportAgent($outro, $filial))
            ->post(route('crm.tickets.address.apply', $ticket))
            ->assertNotFound();

        $this->assertSame('Rua das Flores', $client->fresh()->addresses()->first()->street);
        $this->assertSame('open', $ticket->fresh()->status);
    }

    public function test_chamado_comum_nao_abre_o_formulario_de_aprovacao(): void
    {
        $client = $this->clientWithAddress();

        $ticket = Ticket::create([
            'codigo' => 'CHM-90002',
            'client_id' => $client->id,
            'subject' => 'Internet lenta',
            'description' => 'Sem sinal a noite',
            'status' => 'open',
            'priority' => 'low',
            'category' => 'conexao',
        ]);

        $this->actingAs($this->supportAgent())->post(route('crm.tickets.address.apply', $ticket))
            ->assertNotFound();
    }

    public function test_chamado_sem_endereco_proposto_nao_e_aplicado(): void
    {
        $client = $this->clientWithAddress();

        $ticket = Ticket::create([
            'codigo' => 'CHM-90003',
            'client_id' => $client->id,
            'subject' => 'Assunto com categoria de endereco mas sem proposta',
            'description' => 'Cadastro antigo',
            'status' => 'open',
            'priority' => 'low',
            'category' => Ticket::CATEGORY_ADDRESS,
        ]);

        $this->actingAs($this->supportAgent())->post(route('crm.tickets.address.apply', $ticket))
            ->assertNotFound();

        $this->assertSame('Rua das Flores', $client->fresh()->addresses()->first()->street);
    }

    public function test_tela_do_chamado_mostra_o_confronto_de_endereco(): void
    {
        $client = $this->clientWithAddress();
        $this->actingAs($client, 'client')->post(route('crm.portal.profile.address.store'), $this->payload());
        $ticket = Ticket::where('client_id', $client->id)->firstOrFail();

        $this->actingAs($this->supportAgent())->get(route('crm.tickets.show', $ticket))
            ->assertOk()
            ->assertSee('Endereco atual')
            ->assertSee('Rua das Flores')
            ->assertSee('Avenida Principal')
            ->assertSee('Aplicar endereco');
    }

    public function test_cliente_nao_aceita_contrato_de_outro_cliente(): void
    {
        $branch = $this->firstBranch($this->rootCompany());
        $client = $this->client($branch, 'Cliente Correto');

        // Um contrato de outro cliente dentro da mesma empresa/filial.
        $contractId = \DB::table('contracts')->insertGetId([
            'client_id' => $this->client($branch, 'Outro Cliente')->id,
            'plan_id' => $this->planId(),
            'status' => 'active',
            'activation_date' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($client, 'client')
            ->post(route('crm.portal.tickets.store'), [
                'subject' => 'Assunto',
                'description' => 'Descricao',
                'contract_id' => $contractId,
            ])
            ->assertSessionHasErrors('contract_id');

        $this->assertSame(0, Ticket::where('client_id', $client->id)->count());
    }

    public function test_endereco_formatado_em_uma_linha(): void
    {
        $address = new Address([
            'street' => 'Rua A',
            'number' => '10',
            'referencia' => 'Casa azul',
            'neighborhood' => 'Centro',
            'city' => 'Sao Luis',
            'state' => 'MA',
            'zipcode' => '65000000',
        ]);

        $this->assertSame('Rua A, 10, Casa azul - Centro, Sao Luis/MA, 65000000', $address->full_address);

        // Sem numero/referencia as partes vazias nao deixam buraco no texto.
        $semNumero = new Address([
            'street' => 'Rua B',
            'neighborhood' => 'Centro',
            'city' => 'Sao Luis',
            'state' => 'MA',
        ]);
        $this->assertSame('Rua B - Centro, Sao Luis/MA', $semNumero->full_address);
    }

    private function planId(): int
    {
        return (int) \DB::table('plans')->insertGetId([
            'name' => 'Plano de Teste',
            'slug' => 'plano-'.uniqid(),
            'download_speed' => 100,
            'upload_speed' => 50,
            'price' => 99.90,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function payload(): array
    {
        return [
            'street' => 'Avenida Principal',
            'number' => '1500',
            'referencia' => 'Bloco B',
            'neighborhood' => 'Centro',
            'city' => 'Imperatriz',
            'state' => 'MA',
            'zipcode' => '65000000',
        ];
    }

    private function rootCompany(): Company
    {
        return Company::whereNull('parent_id')->orderBy('id')->firstOrFail();
    }

    private function firstBranch(Company $company): Branch
    {
        return Branch::where('company_id', $company->id)->orderBy('id')->firstOrFail();
    }

    private function client(Branch $branch, string $name): Client
    {
        return Client::create([
            'company_id' => $branch->company_id,
            'branch_id' => $branch->id,
            'name' => $name,
            'document' => (string) random_int(10000000000, 99999999999),
            'type' => 'individual',
            'status' => 'active',
        ]);
    }

    private function clientWithAddress(string $name = 'Cliente de Teste'): Client
    {
        $company = $this->rootCompany();
        $client = $this->client($this->firstBranch($company), $name);

        $client->addresses()->create([
            'street' => 'Rua das Flores',
            'number' => '100',
            'neighborhood' => 'Centro Historico',
            'city' => 'Sao Luis',
            'state' => 'MA',
            'zipcode' => '65000000',
        ]);

        return $client->fresh();
    }

    private function supportAgent(?Company $company = null, ?Branch $branch = null): User
    {
        $company ??= $this->rootCompany();
        $branch ??= $this->firstBranch($company);

        $group = UserGroup::firstOrCreate(
            ['slug' => 'suporte'],
            ['name' => 'Suporte', 'is_active' => true]
        );

        DB::table('group_permissions')->updateOrInsert(
            ['group_id' => $group->id, 'permission_key' => 'tickets'],
            ['granted' => true, 'updated_at' => now()]
        );

        $user = User::create([
            'name' => 'Agente de Suporte',
            'email' => 'suporte-'.uniqid().'@teste.local',
            'password' => 'secret123',
            'user_group_id' => $group->id,
            'is_active' => true,
        ]);

        $user->companies()->attach($company->id);
        $user->branches()->attach($branch->id);

        return $user->fresh();
    }
}
