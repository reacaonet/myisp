<?php

namespace Modules\CRM\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\MikrotikServer;
use Modules\CRM\Services\MikrotikService;

class MikrotikServerController extends Controller
{
    public function index()
    {
        $servers = MikrotikServer::latest()->paginate(15);
        return view('crm::mikrotik-servers.index', compact('servers'));
    }

    public function create()
    {
        return view('crm::mikrotik-servers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip' => 'required|max:45',
            'port' => 'required|integer|min:1|max:65535',
            'login' => 'required|string|max:255',
            'senha' => 'required|string|min:3',
            'type' => 'required|in:pppoe,hotspot,both',
            'notes' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        MikrotikServer::create($validated);

        return redirect()->route('crm.mikrotik-servers.index')
            ->with('success', 'Servidor MikroTik cadastrado com sucesso.');
    }

    public function edit($id)
    {
        $server = MikrotikServer::findOrFail($id);
        return view('crm::mikrotik-servers.edit', compact('server'));
    }

    public function update(Request $request, $id)
    {
        $server = MikrotikServer::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip' => 'required|max:45',
            'port' => 'required|integer|min:1|max:65535',
            'login' => 'required|string|max:255',
            'senha' => 'nullable|string|min:3',
            'type' => 'required|in:pppoe,hotspot,both',
            'notes' => 'nullable|string',
        ]);

        if (empty($validated['senha'])) {
            unset($validated['senha']);
        }

        $validated['is_active'] = $request->boolean('is_active');

        $server->update($validated);

        return redirect()->route('crm.mikrotik-servers.index')
            ->with('success', 'Servidor MikroTik atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $server = MikrotikServer::findOrFail($id);
        $server->delete();

        return redirect()->route('crm.mikrotik-servers.index')
            ->with('success', 'Servidor MikroTik removido com sucesso.');
    }

    public function testConnection($id)
    {
        $server = MikrotikServer::findOrFail($id);
        $service = new MikrotikService();
        $result = $service->testConnection($server);

        if ($result['success']) {
            return back()->with('success', "Conexao OK! Identidade: {$result['identity']}");
        }

        return back()->with('error', "Falha na conexao: {$result['error']}");
    }
}
