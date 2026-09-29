<?php

namespace Modules\Core\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Company;
use Modules\Core\Services\TenantContext;

class ContextController extends Controller
{
    public function switch(Request $request)
    {
        $request->validate([
            'company_id' => 'nullable|exists:companies,id',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $user = $request->user();

        if (! $user) {
            return back()->with('error', 'Sessão expirada.');
        }

        $superadmin = TenantContext::isSuperadmin($user);

        if ($request->filled('company_id')) {
            $companyId = $request->integer('company_id');

            $allowed = $superadmin
                ? Company::pluck('id')->all()
                : $user->companies()->pluck('companies.id')->all();

            if (! in_array($companyId, $allowed, true)) {
                return back()->with('error', 'Você não tem acesso a esta companhia.');
            }

            session(['current_company_id' => $companyId]);
        }

        if ($request->filled('branch_id')) {
            $branch = Branch::find($request->integer('branch_id'));

            if (! $branch) {
                return back()->with('error', 'Filial inválida.');
            }

            $sessionCompany = $request->filled('company_id')
                ? $request->integer('company_id')
                : (int) session('current_company_id');

            if ($sessionCompany && (int) $branch->company_id !== $sessionCompany) {
                return back()->with('error', 'Filial não pertence à companhia selecionada.');
            }

            $allowed = $superadmin
                ? Branch::where('company_id', $branch->company_id)->pluck('id')->all()
                : $user->branches()->pluck('branches.id')->all();

            if (! in_array((int) $branch->id, $allowed, true)) {
                return back()->with('error', 'Você não tem acesso a esta filial.');
            }

            session(['current_branch_id' => (int) $branch->id]);
        }

        TenantContext::forget();

        return back();
    }
}
