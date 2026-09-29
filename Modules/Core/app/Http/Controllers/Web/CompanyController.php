<?php

namespace Modules\Core\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\Core\Models\Company;
use Modules\Core\Models\UserGroup;
use Modules\Core\Services\FranchiseOnboarding;

class CompanyController extends Controller
{
    public function __construct(protected FranchiseOnboarding $onboarding) {}

    public function index()
    {
        $companies = Company::with('parent')->withCount(['branches', 'users'])->orderBy('name')->get();

        return view('core::companies.index', compact('companies'));
    }

    public function create()
    {
        $parents = Company::orderBy('name')->get();
        $groups = UserGroup::where('is_active', true)->orderBy('name')->get();

        return view('core::companies.create', compact('parents', 'groups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'fantasy_name' => 'nullable|string|max:255',
            'slug' => 'required|string|max:60|unique:companies,slug',
            'code' => 'nullable|string|max:20',
            'parent_id' => 'nullable|exists:companies,id',
            'is_active' => 'boolean',
            'is_franchise' => 'boolean',
            'document' => 'nullable|string|max:30',
            'state_registration' => 'nullable|string|max:30',
            'municipal_registration' => 'nullable|string|max:30',
            'phone' => 'nullable|string|max:30',
            'cellphone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:2',
            'zip' => 'nullable|string|max:20',
            'matrix_name' => 'nullable|string|max:255',
            'matrix_code' => 'nullable|string|max:20',
            'admin_name' => 'nullable|required_with:admin_email|string|max:255',
            'admin_email' => 'nullable|email|max:255|unique:users,email',
            'admin_phone' => 'nullable|string|max:20',
            'admin_password' => 'nullable|string|min:8',
            'admin_group_id' => 'nullable|exists:user_groups,id',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_franchise'] = $request->boolean('is_franchise');

        $result = $this->onboarding->register(
            $validated,
            [
                'name' => $request->input('matrix_name'),
                'code' => $request->input('matrix_code'),
            ],
            [
                'name' => $request->input('admin_name'),
                'email' => $request->input('admin_email'),
                'phone' => $request->input('admin_phone'),
                'password' => $request->input('admin_password'),
                'user_group_id' => $request->integer('admin_group_id') ?: null,
            ],
        );

        return redirect()->route('core.companies.index')
            ->with('success', 'Compania criada com sucesso.')
            ->with('onboarding', $this->onboardingSummary($result));
    }

    public function inviteAdminForm(Company $company)
    {
        $groups = UserGroup::where('is_active', true)->orderBy('name')->get();

        return view('core::companies.invite-admin', compact('company', 'groups'));
    }

    public function inviteAdmin(Request $request, Company $company)
    {
        $existing = User::where('email', $request->input('admin_email'))->first();

        $validated = $request->validate([
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255|unique:users,email'.($existing ? ','.$existing->id : ''),
            'admin_phone' => 'nullable|string|max:20',
            'admin_password' => 'nullable|string|min:8',
            'admin_group_id' => 'nullable|exists:user_groups,id',
        ]);

        $result = $this->onboarding->inviteAdmin($company, null, [
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'phone' => $validated['admin_phone'] ?? null,
            'password' => $validated['admin_password'] ?? null,
            'user_group_id' => $request->integer('admin_group_id') ?: null,
        ]);

        return redirect()->route('core.companies.index')
            ->with('success', "Administrador convidado para {$company->name}.")
            ->with('onboarding', $this->onboardingSummary($result));
    }

    public function edit($id)
    {
        $company = Company::with('parent')->findOrFail($id);
        $parents = Company::where('id', '!=', $company->id)->orderBy('name')->get();

        return view('core::companies.edit', compact('company', 'parents'));
    }

    public function update(Request $request, $id)
    {
        $company = Company::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'fantasy_name' => 'nullable|string|max:255',
            'slug' => 'required|string|max:60|unique:companies,slug,'.$company->id,
            'code' => 'nullable|string|max:20',
            'parent_id' => 'nullable|exists:companies,id',
            'is_active' => 'boolean',
            'is_franchise' => 'boolean',
            'document' => 'nullable|string|max:30',
            'state_registration' => 'nullable|string|max:30',
            'municipal_registration' => 'nullable|string|max:30',
            'phone' => 'nullable|string|max:30',
            'cellphone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:2',
            'zip' => 'nullable|string|max:20',
        ]);

        if ((int) $company->parent_id === 0 || $company->isRoot()) {
            $validated['parent_id'] = null;
        }

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_franchise'] = $request->boolean('is_franchise');

        $company->update($validated);

        return redirect()->route('core.companies.index')
            ->with('success', 'Compania atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $company = Company::findOrFail($id);

        if ($company->isRoot()) {
            return redirect()->route('core.companies.index')
                ->with('error', 'A compania raiz (franqueadora) nao pode ser excluida.');
        }

        if ($company->children()->exists() || $company->branches()->exists()) {
            return redirect()->route('core.companies.index')
                ->with('error', 'Exclua as filiais e sub-companias antes de excluir esta franquia.');
        }

        $company->delete();

        return redirect()->route('core.companies.index')
            ->with('success', 'Compania excluida com sucesso.');
    }

    /**
     * Dados exibidos uma unica vez ao operador: usuario convidado e senha temporaria.
     *
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>|null
     */
    protected function onboardingSummary(array $result): ?array
    {
        $admin = $result['admin'] ?? null;

        if (! $admin) {
            return null;
        }

        return [
            'name' => $admin->name,
            'email' => $admin->email,
            'password' => $result['temporary_password'] ?? null,
        ];
    }
}
