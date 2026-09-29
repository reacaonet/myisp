<?php

namespace Modules\PortalInfra\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Services\TenantContext;
use Modules\CRM\Models\Equipment;
use Modules\CRM\Models\MikrotikBackup;
use Modules\CRM\Models\MikrotikServer;
use Modules\CRM\Models\ProvisioningRecord;
use Modules\CRM\Models\UptimeMonitor;
use Modules\PortalInfra\Models\CaixaEmenda;
use Modules\PortalInfra\Models\Cto;
use Modules\PortalInfra\Models\FtthProject;

class DashboardController extends Controller
{
    public function index()
    {
        $crossTenant = TenantContext::isCrossTenant();

        $serversQuery = $this->applyScope(MikrotikServer::query());
        $projectsQuery = $this->applyScope(FtthProject::query());

        $servers = $serversQuery->orderBy('name')->get();
        $serverIds = $crossTenant ? null : $servers->pluck('id');
        $projectIds = $crossTenant ? null : (clone $projectsQuery)->pluck('id');

        $provisionsQuery = fn () => ProvisioningRecord::query()
            ->when($serverIds !== null, fn ($q) => $q->whereIn('mikrotik_server_id', $serverIds));
        $backupsQuery = fn () => MikrotikBackup::query()
            ->when($serverIds !== null, fn ($q) => $q->whereIn('server_id', $serverIds));
        $uptimeQuery = fn () => UptimeMonitor::query()
            ->when($serverIds !== null, fn ($q) => $q->whereIn('server_id', $serverIds));
        $ctosQuery = fn () => Cto::query()
            ->when($projectIds !== null, fn ($q) => $q->whereIn('ftth_project_id', $projectIds));
        $caixasQuery = fn () => CaixaEmenda::query()
            ->when($projectIds !== null, fn ($q) => $q->whereIn('ftth_project_id', $projectIds));

        $recentProvisions = $provisionsQuery()->with(['mikrotikServer', 'client'])->latest()->take(8)->get();
        $recentBackups = $backupsQuery()->with('server')->latest()->take(6)->get();
        $recentCtos = $ctosQuery()->with('caixaEmenda')->latest()->take(6)->get();
        $recentCaixas = $caixasQuery()->withCount('ctos')->latest()->take(6)->get();

        $stats = [
            'servers_total' => $servers->count(),
            'servers_active' => $servers->where('is_active', true)->count(),
            'provisions_total' => $provisionsQuery()->count(),
            'provisions_ok' => $provisionsQuery()->where('success', true)->count(),
            'provisions_failed' => $provisionsQuery()->where('success', false)->count(),
            'backups_total' => $backupsQuery()->count(),
            'uptime_total' => $uptimeQuery()->count(),
            'uptime_up' => $uptimeQuery()->where('is_up', true)->count(),
            'equipment_total' => Equipment::count(),
            'projects_total' => $projectsQuery->count(),
            'ctos_total' => $ctosQuery()->count(),
            'ctos_used_ports' => $ctosQuery()->sum('used_ports'),
            'ctos_capacity' => $ctosQuery()->sum('capacity'),
            'caixas_total' => $caixasQuery()->count(),
        ];

        return view('infra::dashboard', compact('stats', 'servers', 'recentProvisions', 'recentBackups', 'recentCtos', 'recentCaixas'));
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
