<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Company;
use Modules\Core\Models\UserGroup;
use Modules\Core\Services\TenantContext;
use Modules\CRM\Models\Client;
use Tests\TestCase;

class CheckboxToggleTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        TenantContext::forget();
        session()->forget(['current_company_id', 'current_branch_id']);
    }

    public function test_desmarcar_nf_de_cliente_e_salvar(): void
    {
        $this->actingAs($this->superAdmin());

        $client = $this->client(['nf' => true]);

        // o navegador nao envia o campo quando o checkbox esta desmarcado
        $this->put(route('crm.clients.update', $client), [
            'name' => $client->name,
            'type' => 'individual',
            'status' => 'active',
            'branch_id' => $client->branch_id,
        ])->assertRedirect(route('crm.clients.index'));

        $this->assertFalse((bool) $client->fresh()->nf);
    }

    public function test_marcar_nf_de_cliente_continua_salvando(): void
    {
        $this->actingAs($this->superAdmin());

        $client = $this->client(['nf' => false]);

        $this->put(route('crm.clients.update', $client), [
            'name' => $client->name,
            'type' => 'individual',
            'status' => 'active',
            'branch_id' => $client->branch_id,
            'nf' => '1',
        ])->assertRedirect(route('crm.clients.index'));

        $this->assertTrue((bool) $client->fresh()->nf);
    }

    public function test_desativar_tecnico_e_salvar(): void
    {
        $this->actingAs($this->superAdmin());

        $technician = $this->technician(['is_active' => true]);

        $this->put(route('crm.technicians.update', $technician), [
            'name' => $technician->name,
            'email' => $technician->email,
        ])->assertRedirect(route('crm.technicians.index'));

        $this->assertFalse((bool) $technician->fresh()->is_active);
    }

    private function client(array $overrides = []): Client
    {
        $branch = Branch::where('company_id', $this->company()->id)->orderBy('id')->firstOrFail();

        return Client::create(array_merge([
            'company_id' => $branch->company_id,
            'branch_id' => $branch->id,
            'name' => 'Cliente Checkbox '.uniqid(),
            'document' => (string) random_int(10000000000, 99999999999),
            'type' => 'individual',
            'status' => 'active',
            'nf' => false,
        ], $overrides));
    }

    private function technician(array $overrides = []): User
    {
        $group = UserGroup::where('slug', 'tecnico')->first()
            ?? UserGroup::create(['name' => 'Tecnico', 'slug' => 'tecnico', 'is_active' => true]);

        return User::create(array_merge([
            'name' => 'Tecnico Checkbox '.uniqid(),
            'email' => 'tecnico-'.uniqid().'@teste.local',
            'password' => bcrypt('secret123'),
            'user_group_id' => $group->id,
            'is_active' => true,
        ], $overrides));
    }

    private function company(): Company
    {
        return Company::whereNull('parent_id')->orderBy('id')->firstOrFail();
    }

    private function superAdmin(): User
    {
        $group = UserGroup::create([
            'name' => 'Super Admin Checkbox',
            'slug' => 'superadmin',
            'is_active' => true,
        ]);

        DB::table('group_permissions')->updateOrInsert(
            ['group_id' => $group->id, 'permission_key' => 'clients'],
            ['granted' => true, 'updated_at' => now()]
        );

        DB::table('group_permissions')->updateOrInsert(
            ['group_id' => $group->id, 'permission_key' => 'technicians'],
            ['granted' => true, 'updated_at' => now()]
        );

        $user = User::create([
            'name' => 'Super Admin Checkbox',
            'email' => 'checkbox-'.uniqid().'@teste.local',
            'password' => bcrypt('secret123'),
            'user_group_id' => $group->id,
            'is_active' => true,
        ]);

        $company = $this->company();

        $user->companies()->attach($company->id);
        $user->branches()->attach(Branch::where('company_id', $company->id)->pluck('id')->all());

        return $user->fresh();
    }
}
