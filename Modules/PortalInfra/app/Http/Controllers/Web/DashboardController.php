<?php

namespace Modules\PortalInfra\Http\Controllers\Web;

use App\Http\Controllers\Controller;
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
        $servers = MikrotikServer::orderBy('name')->get();
        $recentProvisions = ProvisioningRecord::with(['mikrotikServer', 'client'])
            ->latest()->take(8)->get();
        $recentBackups = MikrotikBackup::with('server')->latest()->take(6)->get();
        $recentCtos = Cto::with('caixaEmenda')->latest()->take(6)->get();
        $recentCaixas = CaixaEmenda::withCount('ctos')->latest()->take(6)->get();

        $stats = [
            'servers_total' => MikrotikServer::count(),
            'servers_active' => $servers->where('is_active', true)->count(),
            'provisions_total' => ProvisioningRecord::count(),
            'provisions_ok' => ProvisioningRecord::where('success', true)->count(),
            'provisions_failed' => ProvisioningRecord::where('success', false)->count(),
            'backups_total' => MikrotikBackup::count(),
            'uptime_total' => UptimeMonitor::count(),
            'uptime_up' => UptimeMonitor::where('is_up', true)->count(),
            'equipment_total' => Equipment::count(),
            'projects_total' => FtthProject::count(),
            'ctos_total' => Cto::count(),
            'ctos_used_ports' => Cto::sum('used_ports'),
            'ctos_capacity' => Cto::sum('capacity'),
            'caixas_total' => CaixaEmenda::count(),
        ];

        return view('infra::dashboard', compact('stats', 'servers', 'recentProvisions', 'recentBackups', 'recentCtos', 'recentCaixas'));
    }
}