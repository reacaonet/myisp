<?php

namespace Modules\PortalInfra\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Modules\Core\Services\TenantContext;
use Modules\CRM\Models\Olt;

class OltController extends Controller
{
    public function index()
    {
        $olts = $this->scopedQuery()->latest()->paginate(15);

        return view('infra::olts.index', compact('olts'));
    }

    /**
     * Toda leitura/escrita de uma OLT passa por aqui. Sem isso o grupo
     * `franqueados` (que tem a permissao `olts`) abria, alterava e apagava
     * OLT de outra loja so pelo id, ja que `findOrFail($id)` nao checa
     * empresa. `firstOrFail` responde 404 em vez de vazar a existencia.
     */
    private function findScoped(int $id): Olt
    {
        return $this->scopedQuery()->whereKey($id)->firstOrFail();
    }

private function scopedQuery(): Builder
    {
        return Olt::scoped();
    }


    public function create()
    {
        return view('infra::olts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'ip' => 'required|max:45',
            'subnet_mask' => 'nullable|string|max:10',
            'type' => 'nullable|in:gpon,epon',
            'pon_ports' => 'nullable|integer|min:1|max:128',
            'used_pon_ports' => 'nullable|integer|min:0|max:128',
            'mgmt_login' => 'nullable|string|max:255',
            'mgmt_password' => 'nullable|string|min:3',
            'snmp_port' => 'nullable|integer|min:1|max:65535',
            'snmp_community' => 'nullable|string|max:255',
            'olt_region' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        Olt::create($validated);

        return redirect()->route('infra.olts.index')
            ->with('success', 'OLT cadastrada com sucesso.');
    }

    public function edit($id)
    {
        $olt = $this->findScoped((int) $id);

        return view('infra::olts.edit', compact('olt'));
    }

    public function update(Request $request, $id)
    {
        $olt = $this->findScoped((int) $id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'ip' => 'required|max:45',
            'subnet_mask' => 'nullable|string|max:10',
            'type' => 'nullable|in:gpon,epon',
            'pon_ports' => 'nullable|integer|min:1|max:128',
            'used_pon_ports' => 'nullable|integer|min:0|max:128',
            'mgmt_login' => 'nullable|string|max:255',
            'mgmt_password' => 'nullable|string|min:3',
            'snmp_port' => 'nullable|integer|min:1|max:65535',
            'snmp_community' => 'nullable|string|max:255',
            'olt_region' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        if (empty($validated['mgmt_password'])) {
            unset($validated['mgmt_password']);
        }

        $olt->update($validated);

        return redirect()->route('infra.olts.index')
            ->with('success', 'OLT atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $olt = $this->findScoped((int) $id);
        $olt->delete();

        return redirect()->route('infra.olts.index')
            ->with('success', 'OLT excluída com sucesso.');
    }
}
