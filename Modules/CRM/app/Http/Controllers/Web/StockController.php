<?php

namespace Modules\CRM\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Modules\CRM\Models\StockItem;
use Modules\CRM\Models\StockCategory;
use Modules\CRM\Models\StockLocation;

class StockController extends Controller
{
    public function dashboard()
    {
        $totalItems = StockItem::count();
        $totalCategories = StockCategory::count();
        $totalLocations = StockLocation::count();

        $lowStockItems = StockItem::with('category', 'balances')
            ->get()
            ->filter(fn ($item) => $item->isLowStock())
            ->values();

        $recentMovements = \Modules\CRM\Models\StockMovement::with('item', 'location', 'performer')
            ->latest()
            ->limit(10)
            ->get();

        $totalValue = StockItem::with('balances')->get()->sum(fn ($item) => $item->totalStock());

        return view('crm::stock.dashboard', compact(
            'totalItems',
            'totalCategories',
            'totalLocations',
            'lowStockItems',
            'recentMovements',
            'totalValue'
        ));
    }
}
