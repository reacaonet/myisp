<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Models\UserGroup;
use Tests\TestCase;

class MultiCompanyTest extends TestCase
{
    use DatabaseTransactions;

    private function superAdminUser(): User
    {
        $group = UserGroup::create([
            'name' => 'Super Admin',
            'slug' => 'superadmin',
            'is_active' => true,
        ]);

        $user = User::create([
            'name' => 'Admin Teste',
            'email' => 'admin@teste.com.br',
            'password' => 'password',
            'user_group_id' => $group->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        return $user;
    }

    public function test_root_company_created_by_retrofit_migration(): void
    {
        $this->assertSame(1, Company::count());
        $this->assertSame(1, Branch::count());

        $company = Company::first();
        $branch = Branch::first();

        $this->assertTrue($company->isRoot());
        $this->assertTrue($branch->isMatrix());
        $this->assertSame('Matriz', $branch->name);
        $this->assertSame($company->id, $branch->company_id);
    }

    public function test_company_and_branch_crud(): void
    {
        $user = $this->superAdminUser();
        $this->actingAs($user);

        $this->get(route('core.companies.index'))->assertOk();
        $this->get(route('core.companies.create'))->assertOk();

        $this->post(route('core.companies.store'), [
            'name' => 'Franquia Campinas',
            'slug' => 'franquia-campinas',
            'code' => 'FR001',
            'is_franchise' => '1',
            'is_active' => '1',
            'matrix_name' => 'Matriz',
            'matrix_code' => 'MAT',
        ])->assertRedirect(route('core.companies.index'));

        $company = Company::where('slug', 'franquia-campinas')->first();
        $this->assertNotNull($company);
        $this->assertTrue($company->is_franchise);
        $this->assertDatabaseHas('branches', ['company_id' => $company->id, 'name' => 'Matriz']);

        $this->get(route('core.companies.edit', $company))->assertOk();

        $this->put(route('core.companies.update', $company), [
            'name' => 'Franquia Campinas Atualizada',
            'slug' => 'franquia-campinas',
            'is_active' => '1',
        ])->assertRedirect(route('core.companies.index'));

        $this->assertDatabaseHas('companies', ['id' => $company->id, 'name' => 'Franquia Campinas Atualizada']);

        $this->get(route('core.branches.index'))->assertOk();
        $this->get(route('core.branches.create'))->assertOk();

        $this->post(route('core.branches.store'), [
            'name' => 'Centro',
            'code' => '002',
            'document' => '12.345.678/0001-95',
            'is_active' => '1',
        ])->assertRedirect(route('core.branches.index'));

        $centro = Branch::where('name', 'Centro')->first();
        $this->assertNotNull($centro);
        $this->assertSame('12345678000195', $centro->document);
        $this->assertSame(Company::whereNull('parent_id')->orderBy('id')->value('id'), $centro->company_id);

        $this->get(route('core.branches.edit', $centro))->assertOk();

        $this->put(route('core.branches.update', $centro), [
            'name' => 'Centro II',
            'is_active' => '1',
        ])->assertRedirect(route('core.branches.index'));

        $this->assertDatabaseHas('branches', ['id' => $centro->id, 'name' => 'Centro II']);
    }

    public function test_context_switch_stores_session(): void
    {
        $user = $this->superAdminUser();
        $this->actingAs($user);

        $root = Company::first();
        $matrix = Branch::first();

        $this->post(route('core.context.switch'), [
            'company_id' => $root->id,
            'branch_id' => $matrix->id,
        ])->assertRedirect();

        $this->assertSame($root->id, session('current_company_id'));
        $this->assertSame($matrix->id, session('current_branch_id'));
    }

    public function test_root_company_cannot_be_deleted(): void
    {
        $user = $this->superAdminUser();
        $this->actingAs($user);

        $root = Company::first();

        $this->delete(route('core.companies.destroy', $root))->assertRedirect();

        $this->assertDatabaseHas('companies', ['id' => $root->id]);
    }

    public function test_non_root_company_with_branches_cannot_be_deleted(): void
    {
        $user = $this->superAdminUser();
        $this->actingAs($user);

        $company = Company::create([
            'name' => 'Franquia Teste',
            'slug' => 'franquia-teste',
            'is_active' => true,
        ]);

        Branch::create([
            'company_id' => $company->id,
            'name' => 'Matriz',
            'is_active' => true,
        ]);

        $this->delete(route('core.companies.destroy', $company))->assertRedirect();

        $this->assertDatabaseHas('companies', ['id' => $company->id]);
    }

    public function test_fiscal_inheritance_from_parent(): void
    {
        $root = Company::first();
        $root->update(['document' => '08.470.613/0001-02']);

        $child = Company::create([
            'parent_id' => $root->id,
            'name' => 'Franquia Filha',
            'slug' => 'franquia-filha',
            'is_active' => true,
            'document' => null,
        ]);

        $this->assertSame('08.470.613/0001-02', $child->fiscal('document'));

        $child->update(['document' => '12.345.678/0001-99']);
        $this->assertSame('12.345.678/0001-99', $child->fiscal('document'));
    }
}
