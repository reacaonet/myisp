<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Company;
use Modules\Core\Models\GroupPermission;
use Modules\Core\Models\UserGroup;
use Tests\TestCase;

class FranchiseOnboardingTest extends TestCase
{
    use DatabaseTransactions;

    public function test_creating_a_company_creates_the_matrix_branch_and_the_invited_admin(): void
    {
        $operator = $this->operatorWithSettingsPermission();
        $group = $this->adminGroup();
        $slug = 'franquia-'.uniqid();
        $email = 'admin-franquia-'.uniqid().'@teste.local';

        $this->actingAs($operator)->post(route('core.companies.store'), [
            'name' => 'Franquia teste',
            'slug' => $slug,
            'is_franchise' => 1,
            'is_active' => 1,
            'matrix_name' => 'Matriz Centro',
            'matrix_code' => 'MC',
            'admin_name' => 'Admin da Franquia',
            'admin_email' => $email,
            'admin_group_id' => $group->id,
        ])->assertRedirect(route('core.companies.index'));

        $company = Company::where('slug', $slug)->firstOrFail();

        $this->assertTrue($company->is_franchise);
        $this->assertDatabaseHas('branches', [
            'company_id' => $company->id,
            'name' => 'Matriz Centro',
            'code' => 'MC',
        ]);

        $admin = User::where('email', $email)->firstOrFail();

        $this->assertSame('admin', $admin->role);
        $this->assertTrue($admin->must_change_password);
        $this->assertDatabaseHas('company_user', [
            'company_id' => $company->id,
            'user_id' => $admin->id,
        ]);
        $this->assertDatabaseHas('branch_user', [
            'branch_id' => $company->branches()->value('id'),
            'user_id' => $admin->id,
        ]);

        $temporaryPassword = session('onboarding')['password'] ?? null;

        $this->assertNotEmpty($temporaryPassword);
        $this->assertTrue(Hash::check($temporaryPassword, $admin->password));
    }

    public function test_invite_admin_reattaches_an_existing_user_to_the_company(): void
    {
        $this->adminGroup();
        $operator = $this->operatorWithSettingsPermission();
        $company = Company::create([
            'name' => 'Franquia existente',
            'slug' => 'franquia-existente-'.uniqid(),
            'is_active' => true,
        ]);

        $matrix = Branch::create([
            'company_id' => $company->id,
            'code' => 'MAT',
            'name' => 'Matriz',
            'is_active' => true,
        ]);

        $email = 'convidado-'.uniqid().'@teste.local';

        $this->actingAs($operator)
            ->post(route('core.companies.admin.store', $company), [
                'admin_name' => 'Convidado',
                'admin_email' => $email,
                'admin_password' => 'senha-definida-123',
            ])
            ->assertRedirect(route('core.companies.index'));

        $admin = User::where('email', $email)->firstOrFail();

        $this->assertFalse($admin->must_change_password);
        $this->assertTrue(Hash::check('senha-definida-123', $admin->password));
        $this->assertDatabaseHas('company_user', ['company_id' => $company->id, 'user_id' => $admin->id]);
        $this->assertDatabaseHas('branch_user', ['branch_id' => $matrix->id, 'user_id' => $admin->id]);
    }

    public function test_invited_user_must_change_the_temporary_password_before_using_the_panel(): void
    {
        $operator = $this->operatorWithSettingsPermission();
        $group = $this->adminGroup();
        $email = 'bloqueado-'.uniqid().'@teste.local';

        $this->actingAs($operator)->post(route('core.companies.store'), [
            'name' => 'Franquia bloqueada',
            'slug' => 'franquia-bloqueada-'.uniqid(),
            'is_active' => 1,
            'admin_name' => 'Admin Bloqueado',
            'admin_email' => $email,
            'admin_group_id' => $group->id,
        ]);

        $temporaryPassword = session('onboarding')['password'];
        $admin = User::where('email', $email)->firstOrFail();

        $this->post(route('logout'));
        $this->post(route('login'), ['email' => $email, 'password' => $temporaryPassword])
            ->assertRedirect(route('password.edit'));

        $this->get(route('crm.dashboard'))->assertRedirect(route('password.edit'));

        $this->put(route('password.update'), [
            'current_password' => $temporaryPassword,
            'password' => 'nova-senha-forte-123',
            'password_confirmation' => 'nova-senha-forte-123',
        ])->assertRedirect(route('crm.dashboard'));

        $this->assertFalse($admin->fresh()->must_change_password);
        $this->assertTrue(Hash::check('nova-senha-forte-123', $admin->fresh()->password));
    }

    public function test_company_can_be_created_without_an_admin(): void
    {
        $operator = $this->operatorWithSettingsPermission();
        $slug = 'sem-admin-'.uniqid();

        $this->actingAs($operator)->post(route('core.companies.store'), [
            'name' => 'Franquia sem admin',
            'slug' => $slug,
            'is_active' => 1,
        ])->assertRedirect(route('core.companies.index'));

        $company = Company::where('slug', $slug)->firstOrFail();

        $this->assertSame(1, $company->branches()->count());
        $this->assertSame(0, $company->users()->count());
    }

    private function adminGroup(): UserGroup
    {
        $group = UserGroup::firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Administrador', 'is_active' => true]
        );

        foreach (array_keys(GroupPermission::MENU_PERMISSIONS()) as $key) {
            DB::table('group_permissions')->updateOrInsert(
                ['group_id' => $group->id, 'permission_key' => $key],
                ['granted' => true, 'updated_at' => now()]
            );
        }

        return $group;
    }

    private function operatorWithSettingsPermission(): User
    {
        $group = UserGroup::firstOrCreate(
            ['slug' => 'operador-onboarding'],
            ['name' => 'Operador Onboarding', 'is_active' => true]
        );

        DB::table('group_permissions')->updateOrInsert(
            ['group_id' => $group->id, 'permission_key' => 'settings'],
            ['granted' => true, 'updated_at' => now()]
        );

        return User::create([
            'name' => 'Operador Onboarding',
            'email' => 'operador-onboarding-'.uniqid().'@teste.local',
            'password' => bcrypt('secret123'),
            'user_group_id' => $group->id,
            'is_active' => true,
        ]);
    }
}
