<?php

namespace Modules\CRM\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\MikrotikBackup;
use Modules\CRM\Models\MikrotikServer;
use Modules\CRM\Services\MikrotikService;

class MikrotikBackupController extends Controller
{
    public function index(Request $request)
    {
        $query = MikrotikBackup::with('server');

        if ($serverId = $request->get('server_id')) {
            $query->where('server_id', $serverId);
        }

        $backups = $query->orderByDesc('created_at')->paginate(20);
        $servers = MikrotikServer::orderBy('name')->get();

        return view('crm::mikrotik-backups.index', compact('backups', 'servers'));
    }

    public function create()
    {
        $servers = MikrotikServer::orderBy('name')->get();
        return view('crm::mikrotik-backups.create', compact('servers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'server_id' => 'required|exists:mikrotik_servers,id',
        ]);

        $server = MikrotikServer::findOrFail($validated['server_id']);

        try {
            $service = new MikrotikService();
            $service->connect($server);

            $filename = 'backup_' . $server->name . '_' . now()->format('Ymd_His') . '.rsc';

            $commands = [
                '/system backup save name=' . $filename,
                '/export file=' . str_replace('.rsc', '', $filename),
            ];

            $output = [];
            foreach ($commands as $cmd) {
                $result = $service->command($cmd);
                $output[] = $result;
            }

            $service->disconnect();

            MikrotikBackup::create([
                'server_id' => $server->id,
                'filename' => $filename,
                'content' => json_encode($output),
                'type' => 'manual',
            ]);

            return redirect()->route('crm.mikrotik-backups.index')
                ->with('success', "Backup de {$server->name} criado com sucesso.");

        } catch (\Exception $e) {
            return back()->with('error', "Erro ao criar backup: " . $e->getMessage());
        }
    }

    public function show($id)
    {
        $backup = MikrotikBackup::with('server')->findOrFail($id);
        return view('crm::mikrotik-backups.show', compact('backup'));
    }

    public function destroy($id)
    {
        $backup = MikrotikBackup::findOrFail($id);
        $backup->delete();

        return redirect()->route('crm.mikrotik-backups.index')
            ->with('success', 'Backup removido com sucesso.');
    }

    public function download($id)
    {
        $backup = MikrotikBackup::findOrFail($id);

        return response($backup->content, 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $backup->filename . '"',
        ]);
    }
}
