<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Core\Models\Company;
use Modules\Core\Models\UserGroup;
use Modules\Core\Services\TenantContext;
use Modules\CRM\Models\Plan;
use Tests\TestCase;

class PlanCheckboxTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        TenantContext::forget();
        session()->forget(['current_company_id', 'current_branch_id']);
    }

    public function test_desmarcar_hotspot_em_plano_ja_salvo_persiste(): void
    {
        $this->actingAs($this->superAdmin());

        $plan = $this->plan(['has_hotspot' => true]);

        // o navegador nao envia nada quando o checkbox e desmarcado
        $this->put(route('crm.plans.update', $plan), [
            'name' => $plan->name,
            'download_speed' => $plan->download_speed,
            'upload_speed' => $plan->upload_speed,
            'price' => $plan->price,
            'billing_cycle' => $plan->billing_cycle,
            'has_pppoe' => '1',
            'is_active' => '1',
        ])->assertRedirect(route('crm.plans.index'))
            ->assertSessionHasNoErrors();

        $this->assertFalse($plan->fresh()->has_hotspot);
    }

    public function test_desmarcar_pppoe_e_ativo_tambem_persiste(): void
    {
        $this->actingAs($this->superAdmin());

        $plan = $this->plan(['has_pppoe' => true, 'is_active' => true]);

        $this->put(route('crm.plans.update', $plan), [
            'name' => $plan->name,
            'download_speed' => $plan->download_speed,
            'upload_speed' => $plan->upload_speed,
            'price' => $plan->price,
            'billing_cycle' => $plan->billing_cycle,
        ])->assertRedirect(route('crm.plans.index'))
            ->assertSessionHasNoErrors();

        $plan = $plan->fresh();

        $this->assertFalse($plan->has_pppoe);
        $this->assertFalse($plan->is_active);
    }

    public function test_marcar_hotspot_continua_funcionando(): void
    {
        $this->actingAs($this->superAdmin());

        $plan = $this->plan(['has_hotspot' => false]);

        $this->put(route('crm.plans.update', $plan), [
            'name' => $plan->name,
            'download_speed' => $plan->download_speed,
            'upload_speed' => $plan->upload_speed,
            'price' => $plan->price,
            'billing_cycle' => $plan->billing_cycle,
            'has_hotspot' => '1',
        ])->assertRedirect(route('crm.plans.index'));

        $this->assertTrue($plan->fresh()->has_hotspot);
    }

    public function test_criar_plano_sem_marcar_hotspot_salva_desligado(): void
    {
        $this->actingAs($this->superAdmin());

        $this->post(route('crm.plans.store'), [
            'name' => 'Plano Sem Hotspot',
            'download_speed' => 20000,
            'upload_speed' => 10000,
            'price' => 149.9,
            'billing_cycle' => 'monthly',
        ])->assertRedirect(route('crm.plans.index'))
            ->assertSessionHasNoErrors();

        $plan = Plan::where('name', 'Plano Sem Hotspot')->firstOrFail();

        $this->assertFalse($plan->has_hotspot);
        $this->assertFalse($plan->has_pppoe);
        $this->assertFalse($plan->is_active);
    }

    private function plan(array $overrides = []): Plan
    {
        $company = Company::whereNull('parent_id')->orderBy('id')->firstOrFail();

        return Plan::create(array_merge([
            'company_id' => $company->id,
            'name' => 'Plano Checkbox '.uniqid(),
            'slug' => 'plano-checkbox-'.uniqid(),
            'download_speed' => 10000,
            'upload_speed' => 5000,
            'price' => 99.9,
            'billing_cycle' => 'monthly',
        ], $overrides));
    }

    private function superAdmin(): User
    {
        $group = UserGroup::create([
            'name' => 'Super Admin Planos',
            'slug' => 'superadmin',
            'is_active' => true,
        ]);

        $user = User::create([
            'name' => 'Super Admin Planos',
            'email' => 'planos-'.uniqid().'@teste.local',
            'password' => bcrypt('secret123'),
            'user_group_id' => $group->id,
            'is_active' => true,
        ]);

        $company = Company::whereNull('parent_id')->orderBy('id')->firstOrFail();

        $user->companies()->attach($company->id);
        $user->branches()->attach($company->branches()->pluck('id')->all());

        return $user->fresh();
    }
}
