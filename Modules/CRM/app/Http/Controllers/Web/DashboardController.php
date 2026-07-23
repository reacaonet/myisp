<?php

namespace Modules\CRM\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Modules\CRM\Models\Client;
use Modules\CRM\Models\Contract;
use Modules\CRM\Models\Plan;
use Modules\CRM\Models\MikrotikServer;
use Modules\CRM\Services\MikrotikService;
use Modules\Billing\Models\Invoice;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'active_clients' => Client::where('status', 'active')->count(),
            'total_clients' => Client::count(),
            'active_contracts' => Contract::where('status', 'active')->count(),
            'total_plans' => Plan::count(),
            'recent_clients' => Client::latest()->take(5)->get(),
        ];

        $stats['total_pending'] = Invoice::where('status', 'pending')->count();
        $stats['total_overdue'] = Invoice::where('status', 'overdue')->count();
        $stats['total_paid'] = Invoice::where('status', 'paid')->count();
        $stats['pending_amount'] = Invoice::where('status', 'pending')->sum('total');
        $stats['overdue_amount'] = Invoice::where('status', 'overdue')->sum('total');
        $stats['paid_amount'] = Invoice::where('status', 'paid')->sum('total');

        $stats['recent_overdue'] = Invoice::with('client')
            ->where('status', 'overdue')
            ->latest('due_date')
            ->take(5)
            ->get();

        $stats['monthly_revenue'] = Invoice::selectRaw("to_char(due_date, 'YYYY-MM') as month, sum(total) as total")
            ->where('status', 'paid')
            ->where('due_date', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();

        $mikrotikServers = MikrotikServer::where('is_active', true)->get();
        $mikrotikStatus = [];
        foreach ($mikrotikServers as $mk) {
            try {
                $service = new MikrotikService();
                $service->connect($mk);
                $resources = $service->getSystemResources();
                $active = $service->getActiveUsers();
                $service->disconnect();

                $res = $resources[0] ?? [];
                $mikrotikStatus[] = [
                    'server' => $mk,
                    'online' => true,
                    'cpu' => $res['cpu'] ?? 0,
                    'uptime' => $res['uptime'] ?? 'N/A',
                    'memory_free' => $res['free-memory'] ?? 0,
                    'board' => $res['board-name'] ?? 'N/A',
                    'pppoe_count' => count($active['pppoe'] ?? []),
                    'hotspot_count' => count($active['hotspot'] ?? []),
                ];
            } catch (\Exception $e) {
                $mikrotikStatus[] = [
                    'server' => $mk,
                    'online' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }
        $stats['mikrotik_status'] = $mikrotikStatus;

        return view('crm::dashboard.index', $stats);
    }
}
