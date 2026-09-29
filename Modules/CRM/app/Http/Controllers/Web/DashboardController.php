<?php

namespace Modules\CRM\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Modules\Billing\Models\Invoice;
use Modules\Core\Services\TenantContext;
use Modules\CRM\Models\Client;
use Modules\CRM\Models\Contract;
use Modules\CRM\Models\MikrotikServer;
use Modules\CRM\Models\Plan;
use Modules\CRM\Services\MikrotikService;

class DashboardController extends Controller
{
    public function index()
    {
        $scope = fn (Builder $query, bool $withBranch = true) => $this->applyScope($query, $withBranch);

        $stats = [
            'active_clients' => $scope(Client::query()->where('status', 'active'))->count(),
            'total_clients' => Client::count(),
            'active_contracts' => Contract::where('status', 'active')->whereHas('client', fn ($q) => $this->applyScope($q))->count(),
            'total_plans' => $scope(Plan::query())->count(),
            'recent_clients' => $scope(Client::query())->latest()->take(5)->get(),
        ];

        $invoices = fn () => $this->applyScope(Invoice::query());

        $stats['total_pending'] = $invoices()->where('status', 'pending')->count();
        $stats['total_overdue'] = $invoices()->where('status', 'overdue')->count();
        $stats['total_paid'] = $invoices()->where('status', 'paid')->count();
        $stats['pending_amount'] = $invoices()->where('status', 'pending')->sum('total');
        $stats['overdue_amount'] = $invoices()->where('status', 'overdue')->sum('total');
        $stats['paid_amount'] = $invoices()->where('status', 'paid')->sum('total');

        $stats['recent_overdue'] = $invoices()->with('client')
            ->where('status', 'overdue')
            ->latest('due_date')
            ->take(5)
            ->get();

        $stats['monthly_revenue'] = $invoices()->selectRaw("to_char(due_date, 'YYYY-MM') as month, sum(total) as total")
            ->where('status', 'paid')
            ->where('due_date', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();

        $invStatus = $invoices()->selectRaw('status, count(*) as total')
            ->whereIn('status', ['paid', 'pending', 'overdue'])
            ->groupBy('status')->pluck('total', 'status');
        $stats['invoice_status'] = [
            'paid' => $invStatus['paid'] ?? 0,
            'pending' => $invStatus['pending'] ?? 0,
            'overdue' => $invStatus['overdue'] ?? 0,
        ];

        $cliStatus = $this->applyScope(Client::query())->selectRaw('status, count(*) as total')
            ->groupBy('status')->pluck('total', 'status');
        $stats['client_status'] = [
            'active' => $cliStatus['active'] ?? 0,
            'suspended' => $cliStatus['suspended'] ?? 0,
            'canceled' => $cliStatus['canceled'] ?? 0,
            'inactive' => $cliStatus['inactive'] ?? 0,
        ];

        $conStatus = Contract::selectRaw('status, count(*) as total')
            ->whereHas('client', fn ($q) => $this->applyScope($q))
            ->groupBy('status')->pluck('total', 'status');
        $stats['contract_status'] = [
            'active' => $conStatus['active'] ?? 0,
            'suspended' => $conStatus['suspended'] ?? 0,
            'canceled' => $conStatus['canceled'] ?? 0,
        ];

        $mikrotikServers = $this->applyScope(MikrotikServer::query())->where('is_active', true)->get();
        $mikrotikStatus = [];
        foreach ($mikrotikServers as $mk) {
            try {
                $service = new MikrotikService;
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

    protected function applyScope(Builder $query, bool $withBranch = true): Builder
    {
        if (TenantContext::isCrossTenant()) {
            return $query;
        }

        $query->where('company_id', TenantContext::companyId());

        if ($withBranch && TenantContext::branchId()) {
            $query->where('branch_id', TenantContext::branchId());
        }

        return $query;
    }
}
