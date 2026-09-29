<?php

namespace Modules\PortalInfra\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Modules\Core\Services\TenantContext;
use Modules\CRM\Models\MikrotikBackup;
use Modules\CRM\Models\MikrotikServer;
use Modules\CRM\Services\MikrotikService;

class MikrotikBackupController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->scopedBackups()->with('server');

        if ($serverId = $request->get('server_id')) {
            $query->where('server_id', $serverId);
        }

        $backups = $query->orderByDesc('created_at')->paginate(20);
        $servers = MikrotikServer::scoped()->orderBy('name')->get();

        return view('infra::mikrotik-backups.index', compact('backups', 'servers'));
    }

    private function scopedBackups(): Builder
    {
        if (TenantContext::isCrossTenant()) {
            return MikrotikBackup::query();
        }

        return MikrotikBackup::forCompany(TenantContext::companyId());
    }

    private function findBackup($id): MikrotikBackup
    {
        return $this->scopedBackups()->findOrFail($id);
    }

    public function create()
    {
        $servers = MikrotikServer::scoped()->orderBy('name')->get();

        return view('infra::mikrotik-backups.create', compact('servers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'server_id' => 'required|exists:mikrotik_servers,id',
        ]);

        $server = MikrotikServer::findScopedOrFail($validated['server_id']);

        try {
            $service = new MikrotikService;
            $service->connect($server);

            $filename = 'backup_'.$server->name.'_'.now()->format('Ymd_His').'.rsc';

            $commands = [
                '/system backup save name='.$filename,
                '/export file='.str_replace('.rsc', '', $filename),
            ];

            $output = [];
            foreach ($commands as $cmd) {
                $result = $service->command($cmd);
                $output[] = $result;
            }

            $service->disconnect();

            MikrotikBackup::create([
                'company_id' => $server->company_id,
                'server_id' => $server->id,
                'filename' => $filename,
                'content' => json_encode($output),
                'type' => 'manual',
            ]);

            return redirect()->route('infra.mikrotik-backups.index')
                ->with('success', "Backup de {$server->name} criado com sucesso.");

        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao criar backup: '.$e->getMessage());
        }
    }

    public function show($id)
    {
        $backup = $this->findBackup($id);

        return view('infra::mikrotik-backups.show', compact('backup'));
    }

    public function destroy($id)
    {
        $backup = $this->findBackup($id);
        $backup->delete();

        return redirect()->route('infra.mikrotik-backups.index')
            ->with('success', 'Backup removido com sucesso.');
    }

    public function download($id)
    {
        $backup = $this->findBackup($id);

        return response($backup->content, 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="'.$backup->filename.'"',
        ]);
    }
}
