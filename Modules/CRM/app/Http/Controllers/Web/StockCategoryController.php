<?php

namespace Modules\CRM\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\StockCategory;

class StockCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = StockCategory::withCount('items');

        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $categories = $query->orderBy('name')->paginate(15);

        return view('crm::stock.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('crm::stock.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:stock_categories,name',
            'description' => 'nullable|string',
        ]);

        StockCategory::create($validated);

        return redirect()->route('crm.stock-categories.index')
            ->with('success', 'Categoria criada com sucesso.');
    }

    public function edit($id)
    {
        $category = StockCategory::findOrFail($id);
        return view('crm::stock.categories.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $category = StockCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:stock_categories,name,' . $category->id,
            'description' => 'nullable|string',
        ]);

        $category->update($validated);

        return redirect()->route('crm.stock-categories.index')
            ->with('success', 'Categoria atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $category = StockCategory::findOrFail($id);
        $category->delete();

        return redirect()->route('crm.stock-categories.index')
            ->with('success', 'Categoria removida com sucesso.');
    }
}
