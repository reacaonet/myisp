<?php

namespace Modules\CRM\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\StockMovement;
use Modules\CRM\Models\StockItem;
use Modules\CRM\Models\StockLocation;
use Modules\CRM\Models\StockBalance;
use Illuminate\Support\Facades\DB;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $query = StockMovement::with('item', 'location', 'performer');

        if ($search = $request->get('search')) {
            $query->whereHas('item', fn ($q) => $q->where('name', 'like', "%{$search}%"));
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($location = $request->get('location')) {
            $query->where('location_id', $location);
        }

        $movements = $query->latest()->paginate(15);
        $locations = StockLocation::orderBy('name')->get();

        return view('crm::stock.movements.index', compact('movements', 'locations'));
    }

    public function create()
    {
        $items = StockItem::orderBy('name')->get();
        $locations = StockLocation::orderBy('name')->get();

        return view('crm::stock.movements.create', compact('items', 'locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:stock_items,id',
            'location_id' => 'required|exists:stock_locations,id',
            'type' => 'required|in:entry,exit,return,transfer,installation',
            'quantity' => 'required|integer|min:1',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $validated['performed_by'] = auth()->id();

            StockMovement::create($validated);

            $balance = StockBalance::firstOrCreate(
                ['item_id' => $validated['item_id'], 'location_id' => $validated['location_id']],
                ['quantity' => 0]
            );

            if (in_array($validated['type'], ['entry', 'return'])) {
                $balance->increment('quantity', $validated['quantity']);
            } else {
                $balance->decrement('quantity', $validated['quantity']);
            }

            DB::commit();

            return redirect()->route('crm.stock-movements.index')
                ->with('success', 'Movimentação registrada com sucesso.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erro ao registrar movimentação: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $movement = StockMovement::with('item', 'location', 'performer')->findOrFail($id);
        return view('crm::stock.movements.show', compact('movement'));
    }

    public function destroy($id)
    {
        $movement = StockMovement::findOrFail($id);
        $movement->delete();

        return redirect()->route('crm.stock-movements.index')
            ->with('success', 'Movimentação removida com sucesso.');
    }
}
