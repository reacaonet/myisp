<?php

namespace Modules\CRM\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\Supplier;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('document', 'like', "%{$search}%");
            });
        }

        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        $suppliers = $query->orderBy('name')->paginate(15);
        $categories = Supplier::whereNotNull('category')->distinct()->pluck('category')->sort()->values();

        return view('crm::suppliers.index', compact('suppliers', 'categories'));
    }

    public function create()
    {
        return view('crm::suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'document' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'contact_person' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        Supplier::create($validated);

        return redirect()->route('crm.suppliers.index')
            ->with('success', 'Fornecedor cadastrado com sucesso.');
    }

    public function show($id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('crm::suppliers.show', compact('supplier'));
    }

    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('crm::suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'document' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'contact_person' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $supplier->update($validated);

        return redirect()->route('crm.suppliers.index')
            ->with('success', 'Fornecedor atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();

        return redirect()->route('crm.suppliers.index')
            ->with('success', 'Fornecedor removido com sucesso.');
    }
}
