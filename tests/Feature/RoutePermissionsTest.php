<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Modules\Core\Models\GroupPermission;
use Modules\Core\Models\UserGroup;
use Tests\TestCase;

class RoutePermissionsTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Rotas de modulo que exigem permissao de grupo (Fase 3 do plano).
     * O prefixo de cada uma e conferido em test_every_protected_module_route_declares_a_permission.
     */
    protected array $guardedUriPrefixes = [
        'crm',
        'faturas',
        'livro-caixa',
        'relatorios',
        'boletos',
        'gateways',
        'infra/',
        'api/',
    ];

    protected array $guardedUriRoots = [
        'configuracoes', 'banners', 'usuarios', 'grupos', 'companias', 'filiais',
    ];

    protected array $guestRoutes = [
        'infra/login', 'infra/logout', 'crm/portal/login', 'crm/portal/logout',
    ];

    public function test_every_protected_module_route_declares_a_permission(): void
    {
        $guarded = 0;

        foreach (Route::getRoutes() as $route) {
            if ($route->getName() === null) {
                continue;
            }

            $uri = $route->uri();

            // portais com guard proprio (cliente/tecnico) nao usam group.permission
            if (str_starts_with($uri, 'crm/portal') || str_starts_with($uri, 'tecnico')) {
                continue;
            }

            // telas de login/logout sao publicas
            if (in_array($uri, $this->guestRoutes, true)) {
                continue;
            }

            if (! $this->isProtected($uri)) {
                continue;
            }

            $this->assertTrue(
                $this->hasGroupPermission($route),
                "A rota [{$route->methods()[0]} {$uri}] nao exige permissao de grupo (Fase 3)."
            );

            $guarded++;
        }

        $this->assertGreaterThan(100, $guarded, 'Nenhuma rota de modulo foi encontrada para validar.');
    }

    public function test_user_without_permission_gets_403_and_with_permission_gets_200(): void
    {
        $this->actingAs($this->userWith(['dashboard' => true]));

        $this->get(route('crm.dashboard'))->assertOk();
        $this->get(route('crm.plans.index'))->assertForbidden();
        $this->get(route('billing.invoices.index'))->assertForbidden();
        $this->get(route('infra.ftth.projects.index'))->assertForbidden();
        $this->get(route('core.settings.index'))->assertForbidden();

        $this->actingAs($this->userWith(['dashboard' => true, 'plans' => true, 'invoices' => true, 'ftth' => true]));

        $this->get(route('crm.plans.index'))->assertOk();
        $this->get(route('billing.invoices.index'))->assertOk();
        $this->get(route('infra.ftth.projects.index'))->assertOk();
        $this->get(route('core.settings.index'))->assertForbidden();
    }

    public function test_group_without_permissions_is_locked_out(): void
    {
        $this->actingAs($this->userWith([]));

        $routes = [
            'crm.dashboard', 'crm.clients.index', 'crm.plans.index', 'crm.tickets.index',
            'billing.invoices.index', 'billing.gateways.index', 'infra.dashboard',
            'infra.equipment.index', 'core.users.index', 'core.settings.index',
        ];

        foreach ($routes as $name) {
            $this->get(route($name))->assertForbidden();
        }
    }

    public function test_superadmin_bypasses_every_permission(): void
    {
        $this->actingAs($this->userWith([], exactSlug: 'superadmin'));

        $routes = [
            'crm.dashboard', 'crm.plans.index', 'billing.invoices.index',
            'billing.gateways.index', 'infra.dashboard', 'core.users.index', 'core.settings.index',
        ];

        foreach ($routes as $name) {
            $this->get(route($name))->assertOk();
        }
    }

    public function test_guest_is_redirected_and_api_requires_authentication(): void
    {
        $this->get(route('crm.clients.index'))->assertRedirect('/login');

        $this->getJson(route('api.clients.index'))->assertStatus(401);
        $this->getJson(route('api.invoices.index'))->assertStatus(401);
    }

    public function test_api_returns_403_json_when_permission_is_missing(): void
    {
        $this->actingAs($this->userWith(['dashboard' => true]));

        $this->getJson(route('api.clients.index'))->assertStatus(403);
        $this->getJson(route('api.invoices.index'))->assertStatus(403);
    }

    public function test_middleware_accepts_any_of_the_permissions(): void
    {
        Route::middleware(['web', 'auth', 'group.permission:invoices,reports'])
            ->get('/__test-permission-multi', fn () => 'ok')
            ->name('test.permission.multi');

        $this->actingAs($this->userWith(['reports' => true]));
        $this->get('/__test-permission-multi')->assertOk();

        $this->actingAs($this->userWith(['cash_book' => true]));
        $this->get('/__test-permission-multi')->assertForbidden();
    }

    public function test_permission_keys_used_by_routes_exist_in_the_catalog(): void
    {
        $catalog = array_keys(GroupPermission::MENU_PERMISSIONS());

        $this->assertContains('olts', $catalog);
        $this->assertContains('ftth', $catalog);
        $this->assertContains('gateways', $catalog);

        $used = [];

        foreach (Route::getRoutes() as $route) {
            foreach ($route->gatherMiddleware() as $middleware) {
                if (! str_starts_with($middleware, 'group.permission:')) {
                    continue;
                }

                foreach (explode(',', substr($middleware, strlen('group.permission:'))) as $key) {
                    $used[] = trim($key);
                }
            }
        }

        $this->assertNotEmpty($used);

        foreach (array_unique($used) as $key) {
            $this->assertContains($key, $catalog, "Chave de permissao fora do catalogo: {$key}");
        }
    }

    protected function hasGroupPermission(\Illuminate\Routing\Route $route): bool
    {
        foreach ($route->gatherMiddleware() as $middleware) {
            if (is_string($middleware) && str_starts_with($middleware, 'group.permission')) {
                return true;
            }
        }

        return false;
    }

    protected function isProtected(string $uri): bool
    {
        $root = explode('/', $uri)[0];

        if (in_array($root, $this->guardedUriRoots, true)) {
            return true;
        }

        foreach ($this->guardedUriPrefixes as $prefix) {
            if ($uri === rtrim($prefix, '/') || str_starts_with($uri, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Cria um usuario cujo grupo concede exatamente as permissoes informadas.
     * Com $exactSlug reaproveita/cria um grupo de slug fixo (ex.: superadmin).
     */
    protected function userWith(array $granted, ?string $exactSlug = null): User
    {
        $group = $exactSlug
            ? UserGroup::firstOrNew(['slug' => $exactSlug])
            : new UserGroup(['slug' => 'teste-permissoes-'.uniqid()]);

        $group->name = $exactSlug === 'superadmin' ? 'Super Administrador' : 'Teste Permissoes';
        $group->description = 'Criado pelo teste de permissoes';
        $group->is_active = true;
        $group->save();

        DB::table('group_permissions')->where('group_id', $group->id)->delete();

        foreach (GroupPermission::MENU_PERMISSIONS() as $key => $label) {
            DB::table('group_permissions')->insert([
                'group_id' => $group->id,
                'permission_key' => $key,
                'granted' => (bool) ($granted[$key] ?? false),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return User::create([
            'name' => 'Usuario Teste',
            'email' => uniqid().'@teste.local',
            'password' => bcrypt('secret123'),
            'user_group_id' => $group->id,
            'is_active' => true,
        ]);
    }
}
