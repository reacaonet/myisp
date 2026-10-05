<?php

namespace Modules\PortalInfra\Http\Controllers\Web;

use App\Http\Controllers\Controller;
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

        // Os models jaknow o corte por filial; o dashboard so combina os ids
        // para as tabelas que dependem de outro (provisionamento, backup,
        // uptime e os itens do desenho que apontam para um projeto).
        $serversQuery = fn () => MikrotikServer::scoped();
        $projectsQuery = fn () => FtthProject::scoped();

        $servers = $serversQuery()->orderBy('name')->get();
        $serverIds = $crossTenant ? null : $servers->pluck('id');

        $provisionsQuery = fn () => ProvisioningRecord::query()
            ->when($serverIds !== null, fn ($q) => $q->whereIn('mikrotik_server_id', $serverIds));
        $backupsQuery = fn () => MikrotikBackup::query()
            ->when($serverIds !== null, fn ($q) => $q->whereIn('server_id', $serverIds));
        $uptimeQuery = fn () => UptimeMonitor::query()
            ->when($serverIds !== null, fn ($q) => $q->whereIn('server_id', $serverIds));
        $ctosQuery = fn () => Cto::scoped();
        $caixasQuery = fn () => CaixaEmenda::scoped();

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
            'projects_total' => $projectsQuery()->count(),
            'ctos_total' => $ctosQuery()->count(),
            'ctos_used_ports' => $ctosQuery()->sum('used_ports'),
            'ctos_capacity' => $ctosQuery()->sum('capacity'),
            'caixas_total' => $caixasQuery()->count(),
        ];

        return view('infra::dashboard', compact('stats', 'servers', 'recentProvisions', 'recentBackups', 'recentCtos', 'recentCaixas'));
    }
}
