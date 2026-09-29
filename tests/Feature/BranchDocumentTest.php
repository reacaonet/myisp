<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Company;
use Modules\Core\Models\UserGroup;
use Modules\Core\Services\TenantContext;
use Tests\TestCase;

class BranchDocumentTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        TenantContext::forget();
        session()->forget(['current_company_id', 'current_branch_id']);
    }

    public function test_filial_pode_ser_criada_sem_cnpj(): void
    {
        $this->actingAs($this->superAdmin());

        $this->post(route('core.branches.store'), [
            'name' => 'Filial sem CNPJ',
            'is_active' => '1',
        ])->assertRedirect(route('core.branches.index'))
            ->assertSessionHasNoErrors();

        $branch = Branch::where('name', 'Filial sem CNPJ')->firstOrFail();

        $this->assertNull($branch->document);
        $this->assertNull($branch->documentFormatted());
    }

    public function test_cnpj_e_salvo_apenas_com_digitos_e_aparece_formatado(): void
    {
        $this->actingAs($this->superAdmin());

        $this->post(route('core.branches.store'), [
            'name' => 'Filial com CNPJ',
            'document' => '12.345.678/0001-95',
            'is_active' => '1',
        ])->assertRedirect(route('core.branches.index'))
            ->assertSessionHasNoErrors();

        $branch = Branch::where('name', 'Filial com CNPJ')->firstOrFail();

        $this->assertSame('12345678000195', $branch->document);
        $this->assertSame('12.345.678/0001-95', $branch->documentFormatted());
    }

    public function test_cnpj_com_formatacao_diferente_ainda_conflita_com_o_ja_cadastrado(): void
    {
        Branch::create([
            'company_id' => $this->rootCompany()->id,
            'name' => 'Filial Original',
            'document' => '12345678000195',
            'is_active' => true,
        ]);

        $this->actingAs($this->superAdmin());

        $this->post(route('core.branches.store'), [
            'name' => 'Filial Duplicada',
            'document' => '12.345.678/0001-95',
        ])->assertSessionHasErrors('document');

        $this->assertDatabaseMissing('branches', ['name' => 'Filial Duplicada']);
    }

    public function test_cnpj_incompleto_e_recusado(): void
    {
        $this->actingAs($this->superAdmin());

        $this->post(route('core.branches.store'), [
            'name' => 'Filial CNPJ Curto',
            'document' => '1234567800',
        ])->assertSessionHasErrors('document');

        $this->assertDatabaseMissing('branches', ['name' => 'Filial CNPJ Curto']);
    }

    public function test_edicao_atualiza_e_limpa_o_cnpj(): void
    {
        $this->actingAs($this->superAdmin());

        $branch = Branch::create([
            'company_id' => $this->rootCompany()->id,
            'name' => 'Filial Editavel',
            'document' => '12345678000195',
            'is_active' => true,
        ]);

        $this->put(route('core.branches.update', $branch), [
            'name' => 'Filial Editavel',
            'document' => '98.765.432/0001-11',
            'is_active' => '1',
        ])->assertRedirect(route('core.branches.index'))
            ->assertSessionHasNoErrors();

        $this->assertSame('98765432000111', $branch->fresh()->document);

        $this->put(route('core.branches.update', $branch), [
            'name' => 'Filial Editavel',
            'document' => '',
            'is_active' => '1',
        ])->assertRedirect(route('core.branches.index'))
            ->assertSessionHasNoErrors();

        $this->assertNull($branch->fresh()->document);
    }

    public function test_filial_nasce_na_empresa_do_contexto_mesmo_enviando_outra(): void
    {
        $this->actingAs($this->superAdmin());

        $outra = Company::create([
            'name' => 'Empresa Terceira',
            'slug' => 'empresa-terceira-'.uniqid(),
            'is_active' => true,
        ]);

        $this->post(route('core.branches.store'), [
            'company_id' => $outra->id,
            'name' => 'Filial Sem Empresa Escolhida',
            'is_active' => '1',
        ])->assertRedirect(route('core.branches.index'))
            ->assertSessionHasNoErrors();

        $branch = Branch::where('name', 'Filial Sem Empresa Escolhida')->firstOrFail();

        $this->assertSame($this->rootCompany()->id, $branch->company_id);
    }

    public function test_telas_de_filial_nao_oferecem_troca_de_empresa(): void
    {
        $this->actingAs($this->superAdmin());

        foreach ([route('core.branches.index'), route('core.branches.create')] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertDontSee('name="company_id"', false);
        }

        $branch = Branch::firstOrFail();

        $this->get(route('core.branches.edit', $branch))
            ->assertOk()
            ->assertDontSee('name="company_id"', false);
    }

    public function test_listagem_mostra_o_cnpj_da_filial(): void
    {
        Branch::create([
            'company_id' => $this->rootCompany()->id,
            'name' => 'Filial Listada',
            'document' => '12345678000195',
            'is_active' => true,
        ]);

        $this->actingAs($this->superAdmin());

        $this->get(route('core.branches.index'))
            ->assertOk()
            ->assertSee('12.345.678/0001-95');
    }

    private function rootCompany(): Company
    {
        return Company::whereNull('parent_id')->orderBy('id')->firstOrFail();
    }

    private function superAdmin(): User
    {
        $group = UserGroup::create([
            'name' => 'Super Admin Filial',
            'slug' => 'superadmin',
            'is_active' => true,
        ]);

        DB::table('group_permissions')->updateOrInsert(
            ['group_id' => $group->id, 'permission_key' => 'settings'],
            ['granted' => true, 'updated_at' => now()]
        );

        $user = User::create([
            'name' => 'Super Admin Filial',
            'email' => 'filial-'.uniqid().'@teste.local',
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
