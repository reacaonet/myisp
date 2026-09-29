<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Company;
use Modules\Core\Models\GroupPermission;
use Modules\Core\Models\UserGroup;
use Tests\TestCase;

class CompanySubscriptionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_raiz_opera_com_assinatura_ilimitada(): void
    {
        $root = $this->rootCompany();

        $this->assertSame('unlimited', $root->subscriptionStatus());
        $this->assertTrue($root->isSubscriptionActive());
        $this->assertFalse($root->onTrial());
        $this->assertFalse($root->isSubscriptionExpired());
        $this->assertNull($root->subscriptionDaysRemaining());
    }

    public function test_matriz_ignora_os_campos_de_assinatura_enviados(): void
    {
        $operator = $this->operator();

        $this->actingAs($operator)->post(route('core.companies.store'), [
            'name' => 'Rede Sem Assinatura',
            'slug' => 'rede-sem-assinatura-'.uniqid(),
            'subscription_status' => 'overdue',
            'trial_ends_at' => now()->subYear()->toDateString(),
        ])->assertRedirect(route('core.companies.index'));

        $company = Company::where('name', 'Rede Sem Assinatura')->latest('id')->firstOrFail();

        $this->assertSame('unlimited', $company->subscription_status);
        $this->assertNull($company->trial_ends_at);
        $this->assertNull($company->plan_slug);
    }

    public function test_franquiza_nova_entra_em_teste_com_vencimento(): void
    {
        $operator = $this->operator();
        $parent = $this->rootCompany();
        $trialEnd = now()->addDays(30)->toDateString();

        $this->actingAs($operator)->post(route('core.companies.store'), [
            'name' => 'Franquia em Teste',
            'slug' => 'franquia-teste-'.uniqid(),
            'parent_id' => $parent->id,
            'is_franchise' => 1,
            'is_active' => 1,
            'plan_slug' => 'gold',
            'subscription_status' => 'trial',
            'trial_ends_at' => $trialEnd,
        ])->assertRedirect(route('core.companies.index'));

        $company = Company::where('name', 'Franquia em Teste')->latest('id')->firstOrFail();

        $this->assertTrue($company->is_franchise);
        $this->assertSame('gold', $company->plan_slug);
        $this->assertSame('trial', $company->subscriptionStatus());
        $this->assertTrue($company->onTrial());
        $this->assertFalse($company->isSubscriptionExpired());
        $this->assertSame(30, $company->subscriptionDaysRemaining());
    }

    public function test_teste_vencido_marca_a_franquia_como_expirada(): void
    {
        $franchise = $this->franchise();

        $franchise->update([
            'subscription_status' => 'trial',
            'trial_ends_at' => now()->subDays(3),
        ]);

        $franchise = $franchise->fresh();

        $this->assertTrue($franchise->isSubscriptionExpired());
        $this->assertFalse($franchise->isSubscriptionActive());
        $this->assertSame(-3, $franchise->subscriptionDaysRemaining());
    }

    public function test_assinatura_ativa_nao_considera_o_teste_vencido(): void
    {
        $franchise = $this->franchise();

        $franchise->update([
            'subscription_status' => 'active',
            'subscription_ends_at' => now()->addMonths(6),
            'trial_ends_at' => now()->subYear(),
        ]);

        $franchise = $franchise->fresh();

        $this->assertTrue($franchise->isSubscriptionActive());
        $this->assertFalse($franchise->isSubscriptionExpired());
        $this->assertFalse($franchise->onTrial());
    }

    public function test_edicao_persiste_o_vencimento_da_franquiza(): void
    {
        $operator = $this->operator();
        $franchise = $this->franchise();

        $this->actingAs($operator)->put(route('core.companies.update', $franchise->id), [
            'name' => $franchise->name,
            'slug' => $franchise->slug,
            'parent_id' => $this->rootCompany()->id,
            'is_franchise' => 1,
            'is_active' => 1,
            'plan_slug' => 'platinum',
            'subscription_status' => 'overdue',
            'subscription_ends_at' => now()->addDays(5)->toDateString(),
        ])->assertRedirect(route('core.companies.index'));

        $franchise = $franchise->fresh();

        $this->assertSame('platinum', $franchise->plan_slug);
        $this->assertSame('overdue', $franchise->subscriptionStatus());
        $this->assertFalse($franchise->isSubscriptionExpired());
        $this->assertSame(5, $franchise->subscriptionDaysRemaining());
    }

    public function test_situacao_invalida_e_rejeitada(): void
    {
        $operator = $this->operator();
        $franchise = $this->franchise();

        $this->actingAs($operator)->put(route('core.companies.update', $franchise->id), [
            'name' => $franchise->name,
            'slug' => $franchise->slug,
            'parent_id' => $this->rootCompany()->id,
            'is_franchise' => 1,
            'subscription_status' => 'gratis',
        ])->assertSessionHasErrors('subscription_status');
    }

    private function rootCompany(): Company
    {
        return Company::whereNull('parent_id')->orderBy('id')->firstOrFail();
    }

    private function franchise(): Company
    {
        $franchise = Company::create([
            'parent_id' => $this->rootCompany()->id,
            'name' => 'Franquia Assinatura',
            'slug' => 'franquia-assinatura-'.uniqid(),
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

    private function operator(): User
    {
        $group = UserGroup::create([
            'name' => 'Operador Companhias',
            'slug' => 'operador-companhias-'.uniqid(),
            'is_active' => true,
        ]);

        foreach (array_keys(GroupPermission::MENU_PERMISSIONS()) as $key) {
            DB::table('group_permissions')->updateOrInsert(
                ['group_id' => $group->id, 'permission_key' => $key],
                ['granted' => true, 'updated_at' => now()]
            );
        }

        $user = User::create([
            'name' => 'Operador Companhias',
            'email' => 'operador-companhias-'.uniqid().'@teste.local',
            'password' => bcrypt('secret123'),
            'user_group_id' => $group->id,
            'is_active' => true,
        ]);

        $user->companies()->attach($this->rootCompany()->id);
        $user->branches()->attach(
            Branch::where('company_id', $this->rootCompany()->id)->orderBy('id')->value('id')
        );

        return $user->fresh();
    }
}
