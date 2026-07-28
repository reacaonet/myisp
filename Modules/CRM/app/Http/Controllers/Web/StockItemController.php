<?php

namespace Modules\CRM\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\StockItem;
use Modules\CRM\Models\StockCategory;
use Modules\CRM\Models\StockBalance;

class StockItemController extends Controller
{
    public function index(Request $request)
    {
        $query = StockItem::with('category', 'balances');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($category = $request->get('category')) {
            $query->where('category_id', $category);
        }

        $items = $query->orderBy('name')->paginate(15);
        $categories = StockCategory::orderBy('name')->get();

        return view('crm::stock.items.index', compact('items', 'categories'));
    }

    public function create()
    {
        $categories = StockCategory::orderBy('name')->get();
        return view('crm::stock.items.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:stock_categories,id',
            'sku' => 'required|string|max:50|unique:stock_items,sku',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:20',
            'min_stock' => 'required|integer|min:0',
        ]);

        StockItem::create($validated);

        return redirect()->route('crm.stock-items.index')
            ->with('success', 'Item criado com sucesso.');
    }

    public function show($id)
    {
        $item = StockItem::with('category', 'balances.location')->findOrFail($id);
        $movements = $item->movements()->with('location', 'performer')->latest()->paginate(15);

        return view('crm::stock.items.show', compact('item', 'movements'));
    }

    public function edit($id)
    {
        $item = StockItem::findOrFail($id);
        $categories = StockCategory::orderBy('name')->get();

        return view('crm::stock.items.edit', compact('item', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $item = StockItem::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'required|exists:stock_categories,id',
            'sku' => 'required|string|max:50|unique:stock_items,sku,' . $item->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:20',
            'min_stock' => 'required|integer|min:0',
        ]);

        $item->update($validated);

        return redirect()->route('crm.stock-items.index')
            ->with('success', 'Item atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $item = StockItem::findOrFail($id);
        $item->delete();

        return redirect()->route('crm.stock-items.index')
            ->with('success', 'Item removido com sucesso.');
    }
}
