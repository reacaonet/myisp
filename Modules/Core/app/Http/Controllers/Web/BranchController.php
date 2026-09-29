<?php

namespace Modules\Core\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Company;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $companies = Company::orderBy('name')->get();

        $branches = Branch::with(['company', 'parent'])
            ->when($request->filled('company_id'), fn ($q) => $q->where('company_id', $request->integer('company_id')))
            ->orderBy('company_id')
            ->orderBy('name')
            ->get();

        return view('core::branches.index', compact('companies', 'branches'));
    }

    public function create()
    {
        $companies = Company::orderBy('name')->get();
        $branches = Branch::with('company')->get();

        return view('core::branches.create', compact('companies', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'parent_id' => 'nullable|exists:branches,id',
            'code' => 'nullable|string|max:20',
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        Branch::create($validated);

        return redirect()->route('core.branches.index')
            ->with('success', 'Filial criada com sucesso.');
    }

    public function edit($id)
    {
        $branch = Branch::with('company')->findOrFail($id);
        $companies = Company::orderBy('name')->get();
        $branches = Branch::where('id', '!=', $branch->id)->with('company')->get();

        return view('core::branches.edit', compact('branch', 'companies', 'branches'));
    }

    public function update(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'parent_id' => 'nullable|exists:branches,id',
            'code' => 'nullable|string|max:20',
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        if ((int) $branch->parent_id === 0 || $branch->isMatrix()) {
            $validated['parent_id'] = null;
        }

        $validated['is_active'] = $request->boolean('is_active');

        $branch->update($validated);

        return redirect()->route('core.branches.index')
            ->with('success', 'Filial atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $branch = Branch::findOrFail($id);

        if ($branch->children()->exists()) {
            return redirect()->route('core.branches.index')
                ->with('error', 'Exclua as sub-filiais antes de excluir esta filial.');
        }

        $branch->delete();

        return redirect()->route('core.branches.index')
            ->with('success', 'Filial excluida com sucesso.');
    }
}
