<?php

namespace Modules\Core\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Collection;
use App\Models\User;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Company;
use Modules\Core\Models\UserGroup;
use Modules\Core\Services\TenantContext;

class UserController extends Controller
{
    /**
     * Grupo do painel do franqueado. O vinculo com a empresa continua sendo o
     * que isola os dados, mas colocar gente neste grupo decide quem enxerga a
     * tela de franja, entao a atribuicao e restrita ao superadmin.
     */
    private const FRANCHISEE_GROUP = 'franqueados';

    public function index()
    {
        $users = User::with('group')->orderBy('name')->get();
        return view('core::users.index', compact('users'));
    }

    public function create()
    {
        $groups = UserGroup::where('is_active', true)->orderBy('name')->get();

        return view('core::users.create', compact('groups') + $this->contextOptions());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'user_group_id' => [
                'required',
                'exists:user_groups,id',
                $this->franchiseeGroupRule(),
            ],
            'company_ids' => ['nullable', 'array'],
            'company_ids.*' => ['integer', Rule::exists('companies', 'id')],
            'branch_ids' => ['nullable', 'array'],
            'branch_ids.*' => ['integer', Rule::exists('branches', 'id')],
            'is_active' => 'boolean',
        ]);

        $validated['password'] = $validated['password'];
        $validated['is_active'] = $request->boolean('is_active');

        $group = UserGroup::find($validated['user_group_id']);
        $validated['role'] = $group->slug;

        // `users` nao tem colunas company_id/branch_id: o vinculo vive nas
        // tabelas de ponte. Sem este unset o $fillable as descartaria em
        // silencio, o que quebraria sozinho se alguem as adicionasse depois.
        unset($validated['company_ids'], $validated['branch_ids']);

        // Valida o vinculo ANTES de gravar, e grava usuario + contexto na mesma
        // transacao: assim uma filial invalida nao deixa usuario orfao.
        $context = $this->resolveContext($request);

        DB::transaction(function () use ($validated, $context) {
            $user = User::create($validated);

            $user->companies()->sync($context['companies']);
            $user->branches()->sync($context['branches']);
        });

        return redirect()->route('core.users.index')
            ->with('success', 'Usuario criado com sucesso.');
    }

    public function edit($id)
    {
        $user = User::with(['companies', 'branches'])->findOrFail($id);
        $groups = UserGroup::where('is_active', true)->orderBy('name')->get();

        return view('core::users.edit', compact('user', 'groups') + $this->contextOptions());
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'user_group_id' => [
                'required',
                'exists:user_groups,id',
                $this->franchiseeGroupRule(),
            ],
            'company_ids' => ['nullable', 'array'],
            'company_ids.*' => ['integer', Rule::exists('companies', 'id')],
            'branch_ids' => ['nullable', 'array'],
            'branch_ids.*' => ['integer', Rule::exists('branches', 'id')],
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $group = UserGroup::find($validated['user_group_id']);
        $validated['role'] = $group->slug;

        unset($validated['company_ids'], $validated['branch_ids']);

        $context = $this->resolveContext($request);

        DB::transaction(function () use ($user, $validated, $context) {
            $user->update($validated);

            $user->companies()->sync($context['companies']);
            $user->branches()->sync($context['branches']);
        });

        return redirect()->route('core.users.index')
            ->with('success', 'Usuario atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return redirect()->route('core.users.index')
                ->with('error', 'Voce nao pode excluir seu proprio usuario.');
        }

        $user->delete();

        return redirect()->route('core.users.index')
            ->with('success', 'Usuario excluido com sucesso.');
    }

    /**
     * Selects de empresa e filial. Sao a definicao de "de qual franchise e o
     * usuario": sem o vinculo em company_user o TenantContext nao resolve
     * empresa nenhuma e o usuario nao ve nada.
     *
     * @return array{canAssignContext: bool, companies: Collection<int, Company>, branches: Collection<int, Branch>}
     */
    private function contextOptions(): array
    {
        if (! TenantContext::isSuperadmin()) {
            return ['canAssignContext' => false, 'companies' => collect(), 'branches' => collect()];
        }

        return [
            'canAssignContext' => true,
            'companies' => Company::orderBy('name')->get(),
            'branches' => Branch::orderByRaw('parent_id is null desc')->orderBy('name')->get(),
        ];
    }

/**
 * Resolve o vinculo do usuario com empresas e filiais.
 *
 * O sistema e multiempresa: um usuario pode operar varias lojas ao mesmo tempo
 * (o dono do negocio, um investidor com duas franquias). O TenantContext ja
 * esperava isso --allowedCompanyIds() devolve uma lista e o contexto ativo vem
 * de session('current_company_id')-- entao aqui o vinculo e gravado como
 * conjunto, e nao como um unico par.
 *
 * Regras:
 *  - cada filial escolhida traz a empresa dela junto, porque a filial e que
 *    define o escopo de atendimento;
 *  - toda filial precisa pertencer a alguma das empresas marcadas;
 *  - quem nao e superadmin recebe listas vazias e assim preserva o vinculo.
 *
 * @return array{companies: array<int, int>, branches: array<int, int>}
 */
private function resolveContext(Request $request): array
{
    if (! TenantContext::isSuperadmin()) {
        return ['companies' => [], 'branches' => []];
    }

    $companies = collect($request->input('company_ids', []))
        ->filter()
        ->map(fn ($id) => (int) $id)
        ->unique()
        ->values();

    $branches = collect($request->input('branch_ids', []))
        ->filter()
        ->map(fn ($id) => (int) $id)
        ->unique()
        ->values();

    // A filial arrasta a empresa dela.
    if ($branches->isNotEmpty()) {
        $companies = $companies
            ->merge(Branch::whereIn('id', $branches)->pluck('company_id'))
            ->unique()
            ->values();
    }

    // Nenhuma filial pode sobrar de fora das empresas marcadas.
    if ($branches->isNotEmpty() && $companies->isNotEmpty()) {
        $estranhas = Branch::whereIn('id', $branches)
            ->whereNotIn('company_id', $companies)
            ->pluck('id');

        if ($estranhas->isNotEmpty()) {
            throw ValidationException::withMessages([
                'branch_ids' => 'Uma das filiais informadas nao pertence a uma empresa marcada.',
            ]);
        }
    }

    return [
        'companies' => $companies->all(),
        'branches' => $branches->all(),
    ];
}

    /**
     * Barra a atribuicao ao grupo do franqueado para quem nao e superadmin.
     * Sem isto, um operador com `settings` colocaria usuarios no grupo que da
     * acesso ao painel e ainda poderia mexer no proprio grupo.
     */
    private function franchiseeGroupRule(): ?Closure
    {
        if (TenantContext::isSuperadmin()) {
            return null;
        }

        return function (string $attribute, $value, Closure $fail): void {
            $slug = UserGroup::whereKey($value)->value('slug');

            if ($slug === self::FRANCHISEE_GROUP) {
                $fail('Somente o superadmin pode vincular usuarios ao grupo de franqueados.');
            }
        };
    }
}
