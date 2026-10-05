<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Company;
use Modules\Core\Models\UserGroup;
use Modules\Core\Services\TenantContext;
use Tests\TestCase;

class WebProfileTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        TenantContext::forget();
        Storage::fake('public');
    }

    public function test_tela_de_perfil_exige_login(): void
    {
        $this->get(route('core.profile.edit'))->assertRedirect(route('login'));
    }

    public function test_usuario_comum_acessa_o_perfil_sem_permissao_de_usuarios(): void
    {
        $user = $this->operator();

        // `core.users.*` fica atrás de `group.permission:settings`; o perfil não.
        $this->actingAs($user)->get(route('core.users.index'))->assertForbidden();

        $this->actingAs($user)->get(route('core.profile.edit'))
            ->assertOk()
            ->assertSee('Meu Perfil')
            ->assertSee($user->name);
    }

    public function test_atualiza_dados_pessoais(): void
    {
        $user = $this->operator();

        $this->actingAs($user)->put(route('core.profile.update'), [
            'name' => 'Maria da Silva',
            'email' => 'maria@provedor.com',
            'phone' => '(98) 3200-1000',
            'cellphone' => '(98) 99123-4567',
            'city' => 'Sao Luis',
            'state' => 'ma',
        ])->assertRedirect(route('core.profile.edit'));

        $user->refresh();

        $this->assertSame('Maria da Silva', $user->name);
        $this->assertSame('maria@provedor.com', $user->email);
        $this->assertSame('(98) 99123-4567', $user->cellphone);
        $this->assertSame('Sao Luis', $user->city);
        // UF entra em maiuscula: o campo e `varchar(2)` e nao ha mascara.
        $this->assertSame('MA', $user->state);
    }

    public function test_perfil_nao_altera_grupo_nem_situacao(): void
    {
        $user = $this->operator();
        $grupoAntes = $user->user_group_id;
        $companiesAntes = $user->companies()->pluck('companies.id')->all();

        $this->actingAs($user)->put(route('core.profile.update'), [
            'name' => 'Maria Escalada',
            'email' => $user->email,
            // Campos de administrador: se algum passar, o perfil virou um
            // caminho para se promover, se reativar ou trocar de contexto.
            'user_group_id' => $this->group('superadmin')->id,
            'role' => 'superadmin',
            'is_active' => '0',
            'company_ids' => [$this->otherCompany()->id],
            'branch_ids' => [],
        ])->assertRedirect();

        $user->refresh();

        $this->assertSame('Maria Escalada', $user->name);
        $this->assertSame($grupoAntes, $user->user_group_id, 'perfil nao pode trocar o grupo');
        $this->assertSame('operator', $user->role, 'perfil nao pode trocar o papel');
        $this->assertTrue((bool) $user->is_active, 'perfil nao pode se desativar');
        $this->assertSame($companiesAntes, $user->companies()->pluck('companies.id')->all(), 'perfil nao pode trocar a empresa');
    }

    public function test_email_precisa_ser_unico(): void
    {
        $um = $this->operator(['email' => 'um@teste.local']);
        $dois = $this->operator(['email' => 'dois@teste.local']);

        $this->actingAs($um)->put(route('core.profile.update'), [
            'name' => $um->name,
            'email' => $dois->email,
        ])->assertSessionHasErrors('email');

        $this->assertSame($um->email, $um->fresh()->email);
        $this->assertSame($dois->email, $dois->fresh()->email);
    }

    public function test_envia_e_troca_avatar_apagando_o_anterior(): void
    {
        $user = $this->operator();

        $this->actingAs($user)->put(route('core.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => UploadedFile::fake()->image('primeira.jpg'),
        ])->assertRedirect();

        $caminhoAntigo = $user->fresh()->avatar;
        $this->assertNotNull($caminhoAntigo);
        Storage::disk('public')->assertExists($caminhoAntigo);

        $this->actingAs($user)->put(route('core.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => UploadedFile::fake()->image('segunda.png'),
        ])->assertRedirect();

        $caminhoNovo = $user->fresh()->avatar;
        $this->assertNotSame($caminhoAntigo, $caminhoNovo);
        Storage::disk('public')->assertExists($caminhoNovo);
        Storage::disk('public')->assertMissing($caminhoAntigo);
    }

    public function test_remove_avatar(): void
    {
        $user = $this->operator();

        $this->actingAs($user)->put(route('core.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => UploadedFile::fake()->image('foto.jpg'),
        ]);

        $caminho = $user->fresh()->avatar;

        $this->actingAs($user)->put(route('core.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'remove_avatar' => '1',
        ])->assertRedirect();

        $this->assertNull($user->fresh()->avatar);
        Storage::disk('public')->assertMissing($caminho);
    }

    public function test_rejeita_arquivo_que_nao_e_imagem(): void
    {
        $user = $this->operator();

        $this->actingAs($user)->put(route('core.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => UploadedFile::fake()->create('perfil.php', 8, 'application/x-php'),
        ])->assertSessionHasErrors('avatar');

        $this->assertNull($user->fresh()->avatar);
    }

    public function test_mostra_empresa_e_filial_atual(): void
    {
        $empresa = Company::whereNull('parent_id')->orderBy('id')->firstOrFail();
        $filial = Branch::where('company_id', $empresa->id)->orderBy('id')->firstOrFail();

        $user = $this->operator();
        $user->companies()->attach($empresa->id);
        $user->branches()->attach($filial->id);

        $this->actingAs($user)->get(route('core.profile.edit'))
            ->assertOk()
            ->assertSee($empresa->name)
            ->assertSee($filial->name);
    }

    public function test_troca_de_email_zera_a_verificacao(): void
    {
        $user = $this->operator(['email' => 'antigo@teste.local']);
        $user->forceFill(['email_verified_at' => now()])->save();

        $this->actingAs($user)->put(route('core.profile.update'), [
            'name' => $user->name,
            'email' => 'novo@teste.local',
        ])->assertRedirect();

        $user->refresh();

        $this->assertSame('novo@teste.local', $user->email);
        // Sem isso o `verified` bloquearia o painel depois da troca.
        $this->assertNull($user->email_verified_at);
    }

    public function test_iniciais_do_usuario(): void
    {
        // Padrao "Maria Silva" -> MS (primeiro e ultimo nome), nao o do meio.
        $this->assertSame('MS', User::make(['name' => 'Maria Joana Silva'])->initials());
        $this->assertSame('J', User::make(['name' => 'Joana'])->initials());
        $this->assertSame('?', User::make(['name' => '  '])->initials());
    }

    private function group(string $slug): UserGroup
    {
        return UserGroup::firstOrCreate(['slug' => $slug], ['name' => ucfirst($slug), 'is_active' => true]);
    }

    private function operator(array $attributes = []): User
    {
        $group = $this->group('operator');

        $user = User::create(array_merge([
            'name' => 'Operador Padrao',
            'email' => 'operador-'.uniqid().'@teste.local',
            'password' => 'secret123',
            'user_group_id' => $group->id,
            'role' => $group->slug,
            'is_active' => true,
        ], $attributes));

        // `email_verified_at` nao e fillable e as rotas do perfil exigem
        // `verified`, entao o atalho e obrigatorio.
        return $user->forceFill(['email_verified_at' => now()])->fresh();
    }

    /**
     * Uma empresa diferente da que o usuario esta vinculado, para provar que o
     * perfil nao mexe no contexto.
     */
    private function otherCompany(): Company
    {
        $atual = Company::whereNull('parent_id')->orderBy('id')->firstOrFail();

        return Company::firstOrCreate(
            ['slug' => 'perfil-testes'],
            ['name' => 'Empresa de Testes do Perfil', 'is_active' => true, 'is_franchise' => true]
        ) ?? $atual;
    }
}
