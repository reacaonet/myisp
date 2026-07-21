<?php

namespace Modules\CRM\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\CRM\Models\Plan;
use Modules\Core\Models\Server;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $query = Plan::query();

        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $plans = $query->with('server')->latest()->paginate(15);

        return view('crm::plans.index', compact('plans'));
    }

    public function create()
    {
        $servers = Server::where('is_active', true)->orderBy('name')->get();
        return view('crm::plans.create', compact('servers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'download_speed' => 'required|integer',
            'upload_speed' => 'required|integer',
            'price' => 'required|numeric',
            'setup_fee' => 'nullable|numeric',
            'billing_cycle' => 'required|in:monthly,quarterly,semiannual,annual',
            'max_simultaneous' => 'nullable|integer',
            'max_session_time' => 'nullable|integer',
            'has_pppoe' => 'boolean',
            'has_hotspot' => 'boolean',
            'pool' => 'nullable|string',
            'address_list' => 'nullable|string',
            'url_advertise' => 'nullable|string',
            'advertise_intervalo' => 'nullable|integer',
            'police_in' => 'nullable|string',
            'police_out' => 'nullable|string',
            'tipo_servidor' => 'nullable|string',
            'interface' => 'nullable|string',
            'server_id' => 'nullable|exists:servers,id',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        Plan::create($validated);

        return redirect()->route('crm.plans.index')
            ->with('success', 'Plano criado com sucesso.');
    }

    public function edit($id)
    {
        $plan = Plan::findOrFail($id);
        $servers = Server::where('is_active', true)->orderBy('name')->get();
        return view('crm::plans.edit', compact('plan', 'servers'));
    }

    public function update(Request $request, $id)
    {
        $plan = Plan::findOrFail($id);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'download_speed' => 'integer',
            'upload_speed' => 'integer',
            'price' => 'numeric',
            'setup_fee' => 'nullable|numeric',
            'billing_cycle' => 'in:monthly,quarterly,semiannual,annual',
            'max_simultaneous' => 'nullable|integer',
            'max_session_time' => 'nullable|integer',
            'has_pppoe' => 'boolean',
            'has_hotspot' => 'boolean',
            'pool' => 'nullable|string',
            'address_list' => 'nullable|string',
            'url_advertise' => 'nullable|string',
            'advertise_intervalo' => 'nullable|integer',
            'police_in' => 'nullable|string',
            'police_out' => 'nullable|string',
            'tipo_servidor' => 'nullable|string',
            'interface' => 'nullable|string',
            'server_id' => 'nullable|exists:servers,id',
            'is_active' => 'boolean',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $plan->update($validated);

        return redirect()->route('crm.plans.index')
            ->with('success', 'Plano atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $plan = Plan::findOrFail($id);
        $plan->delete();

        return redirect()->route('crm.plans.index')
            ->with('success', 'Plano removido com sucesso.');
    }
}
