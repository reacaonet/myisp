<?php

namespace Modules\Core\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Core\Models\Branch;
use Modules\Core\Services\TenantContext;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::with('parent')
            ->forCompany($this->companyId())
            ->orderByRaw('parent_id is null desc')
            ->orderBy('name')
            ->get();

        return view('core::branches.index', compact('branches'));
    }

    public function create()
    {
        return view('core::branches.create', [
            'branches' => Branch::forCompany($this->companyId())->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->normalizeDocument($request);

        $validated = $request->validate($this->rules());

        $validated['company_id'] = $this->companyId();
        $validated['is_active'] = $request->boolean('is_active');

        Branch::create($validated);

        return redirect()->route('core.branches.index')
            ->with('success', 'Filial criada com sucesso.');
    }

    public function edit($id)
    {
        $branch = $this->findBranch($id);

        return view('core::branches.edit', [
            'branch' => $branch,
            'branches' => Branch::forCompany($this->companyId())->where('id', '!=', $branch->id)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $branch = $this->findBranch($id);

        $this->normalizeDocument($request);

        $validated = $request->validate($this->rules($branch));

        if ($branch->isMatrix()) {
            $validated['parent_id'] = null;
        }

        $validated['is_active'] = $request->boolean('is_active');

        $branch->update($validated);

        return redirect()->route('core.branches.index')
            ->with('success', 'Filial atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $branch = $this->findBranch($id);

        if ($branch->children()->exists()) {
            return redirect()->route('core.branches.index')
                ->with('error', 'Exclua as sub-filiais antes de excluir esta filial.');
        }

        $branch->delete();

        return redirect()->route('core.branches.index')
            ->with('success', 'Filial excluida com sucesso.');
    }

    /**
     * A rede tem uma empresa so: a filial nasce sempre nela, sem escolha no
     * formulario. E o TenantContext que define qual e.
     */
    private function companyId(): int
    {
        return (int) TenantContext::companyId();
    }

    private function findBranch($id): Branch
    {
        return Branch::forCompany($this->companyId())->findOrFail($id);
    }

    /**
     * O CNPJ e opcional, mas quando informado precisa vir completo e nao pode
     * repetir entre filiais: a nota sai em nome da filial.
     *
     * Os digitos sao limpos antes de validar porque a unicidade e conferida
     * assim: "12.345.678/0001-95" colide com "12345678000195" ja salvo.
     */
    private function rules(?Branch $branch = null): array
    {
        return [
            'parent_id' => 'nullable|exists:branches,id',
            'code' => 'nullable|string|max:20',
            'name' => 'required|string|max:255',
            'document' => [
                'nullable',
                'string',
                'size:14',
                Rule::unique('branches', 'document')->ignore($branch?->id),
            ],
            'is_active' => 'boolean',
        ];
    }

    private function normalizeDocument(Request $request): void
    {
        $document = preg_replace('/\D/', '', (string) $request->input('document'));

        $request->merge(['document' => $document === '' ? null : $document]);
    }
}
