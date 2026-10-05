<?php

namespace Modules\CRM\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\PortalInfra\Models\CaixaEmenda;
use Modules\PortalInfra\Models\Cto;
use Modules\PortalInfra\Models\FtthFusion;
use Modules\PortalInfra\Models\FtthProject;
use Modules\PortalInfra\Services\FtthMapData;

class TechnicianPortalController extends Controller
{
    public function loginForm()
    {
        return view('crm::technician.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::guard('technician')->attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], $request->boolean('remember'))) {
            $user = Auth::guard('technician')->user();

            if (! $user->group || $user->group->slug !== 'tecnico') {
                Auth::guard('technician')->logout();

                return back()->withErrors(['email' => 'Acesso nao permitido. Apenas tecnicos podem acessar este portal.'])->onlyInput('email');
            }

            if (! $user->is_active) {
                Auth::guard('technician')->logout();

                return back()->withErrors(['email' => 'Usuario inativo.'])->onlyInput('email');
            }

            $request->session()->regenerate();

            return redirect()->intended(route('technician.portal.dashboard'));
        }

        return back()->withErrors(['email' => 'Credenciais invalidas.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('technician')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('technician.portal.login');
    }

    public function dashboard()
    {
        $technician = Auth::guard('technician')->user();
        $serviceOrders = $technician->serviceOrders()->with(['client', 'contract.plan'])->latest('emissao')->get();

        $stats = [
            'total_assigned' => $serviceOrders->count(),
            'open' => $serviceOrders->where('situacao', 'O')->count(),
            'in_progress' => $serviceOrders->where('situacao', 'A')->count(),
            'completed_today' => $serviceOrders->where('situacao', 'F')->where('updated_at', '>=', now()->startOfDay())->count(),
        ];

        return view('crm::technician.dashboard', compact('technician', 'serviceOrders', 'stats'));
    }

    public function ftthNetwork(Request $request)
    {
        $projectId = $request->input('project');

        // `scoped()` resolve a partir do `ftth_project_id` do proprio tecnico
        // (via `TenantContext`, que ja enxerga o guard `technician`). Sem isso o
        // tecnico lia e alterava o desenho de rede de qualquer franquia so
        // iterando id.
        $ctoQuery = Cto::scoped()->with('ftthProject')->withCount('fusions');
        $caixaQuery = CaixaEmenda::scoped()->with('ftthProject')->withCount(['ctos', 'fusions']);

        if ($projectId) {
            $ctoQuery->where('ftth_project_id', $projectId);
            $caixaQuery->where('ftth_project_id', $projectId);
        }

        $ctos = $ctoQuery->latest('id')->limit(500)->get();
        $caixas = $caixaQuery->latest('id')->limit(500)->get();
        $projects = FtthProject::scoped()->orderBy('name')->get();

        $stats = [
            'total_ctos' => $ctos->count(),
            'active_ctos' => $ctos->where('status', 'active')->count(),
            'total_caixas' => $caixas->count(),
            'total_pending_fusions' => $ctos->sum('fusions_count') + $caixas->sum('fusions_count'),
        ];

        return view('crm::technician.ftth.index', compact('ctos', 'caixas', 'projects', 'projectId', 'stats'));
    }

    /**
     * Mapa da rede. O Leaflet pede os marcadores por JSON, entao a tela e os
     * dados sao dois endpoints: um renderiza o container, o outro devolve a
     * projecao compartilhada com o mapa do admin.
     */
    public function ftthMap(Request $request)
    {
        $projectId = $request->integer('project') ?: null;

        return view('crm::technician.ftth.map', [
            'projects' => FtthProject::scoped()->orderBy('name')->get(),
            'projectId' => $projectId,
        ]);
    }

    public function ftthMapData(Request $request)
    {
        $projectId = $request->integer('project') ?: null;

        return response()->json(
            app(FtthMapData::class)->build($request->string('city')->toString() ?: null, $projectId)
        );
    }

    public function ftthCtoShow($id)
    {
        $cto = Cto::scoped()->with(['caixaEmenda', 'ftthProject', 'fusions' => fn ($q) => $q->orderBy('fiber_number')])->findOrFail($id);
        $pendingCount = $cto->fusions->where('status', 'pending')->count();
        $doneCount = $cto->fusions->where('status', 'done')->count();

        return view('crm::technician.ftth.cto', compact('cto', 'pendingCount', 'doneCount'));
    }

    public function ftthCaixaShow($id)
    {
        $caixa = CaixaEmenda::scoped()->with(['ftthProject', 'fusions' => fn ($q) => $q->orderBy('fiber_number')])->withCount('ctos')->findOrFail($id);
        $pendingCount = $caixa->fusions->where('status', 'pending')->count();
        $doneCount = $caixa->fusions->where('status', 'done')->count();

        return view('crm::technician.ftth.caixa', compact('caixa', 'pendingCount', 'doneCount'));
    }

    public function ftthFusionDone($id)
    {
        $fusion = FtthFusion::findScopedOrFail((int) $id);
        $fusion->update(['status' => 'done']);

        $redirect = $fusion->cto_id
            ? route('technician.portal.ftth.ctos.show', $fusion->cto_id)
            : route('technician.portal.ftth.caixas.show', $fusion->caixa_emenda_id);

        return redirect($redirect)->with('success', 'Fusao marcada como executada.');
    }

    public function ftthFusionUpdate(Request $request, $id)
    {
        $fusion = FtthFusion::findScopedOrFail((int) $id);

        $validated = $request->validate([
            'fiber_number' => 'nullable|string|max:20',
            'olt_port' => 'nullable|string|max:50',
            'tube' => 'nullable|string|max:20',
            'destination' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $fusion->update($validated);

        $redirect = $fusion->cto_id
            ? route('technician.portal.ftth.ctos.show', $fusion->cto_id)
            : route('technician.portal.ftth.caixas.show', $fusion->caixa_emenda_id);

        return redirect($redirect)->with('success', 'Dados da fusao atualizados.');
    }

    public function ftthCtoActivate($id)
    {
        $cto = Cto::findScopedOrFail((int) $id);
        $cto->update(['status' => 'active']);

        return redirect()->route('technician.portal.ftth.ctos.show', $cto)
            ->with('success', 'CTO ativada. O escritorio ja pode ver a alteracao.');
    }

    public function ftthCaixaActivate($id)
    {
        $caixa = CaixaEmenda::findScopedOrFail((int) $id);
        $caixa->update(['status' => 'active']);

        return redirect()->route('technician.portal.ftth.caixas.show', $caixa)
            ->with('success', 'Caixa de Emenda ativada. O escritorio ja pode ver a alteracao.');
    }

    public function ftthCtoUpdateNotes(Request $request, $id)
    {
        $cto = Cto::findScopedOrFail((int) $id);
        $validated = $request->validate([
            'technician_notes' => 'nullable|string|max:2000',
        ]);
        $cto->update($validated);

        return redirect()->route('technician.portal.ftth.ctos.show', $cto)
            ->with('success', 'Observacoes salvas.');
    }

    public function ftthCaixaUpdateNotes(Request $request, $id)
    {
        $caixa = CaixaEmenda::findScopedOrFail((int) $id);
        $validated = $request->validate([
            'technician_notes' => 'nullable|string|max:2000',
        ]);
        $caixa->update($validated);

        return redirect()->route('technician.portal.ftth.caixas.show', $caixa)
            ->with('success', 'Observacoes salvas.');
    }

    public function serviceOrders(Request $request)
    {
        $technician = Auth::guard('technician')->user();

        $query = $technician->serviceOrders()->with(['client', 'contract.plan']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($situacao = $request->input('situacao')) {
            $query->where('situacao', $situacao);
        }

        $serviceOrders = $query->latest('emissao')->paginate(20);

        return view('crm::technician.service_orders.index', compact('serviceOrders'));
    }

    public function serviceOrderShow($id)
    {
        $technician = Auth::guard('technician')->user();
        $serviceOrder = $technician->serviceOrders()->with(['client.addresses', 'contract.plan', 'technician'])->findOrFail($id);

        return view('crm::technician.service_orders.show', compact('serviceOrder'));
    }

    public function updateServiceOrder(Request $request, $id)
    {
        $technician = Auth::guard('technician')->user();
        $order = $technician->serviceOrders()->findOrFail($id);

        $validated = $request->validate([
            'situacao' => 'nullable|in:O,A,F,C',
            // Alinhado ao enum real da coluna `status`
            // (2026_07_21_140409_create_service_orders_table): aceitar
            // `in_progress`/`resolved` fazia o select gravar valor inexistente e
            // o proprio select da tela quebrava em modo estrito.
            'status' => 'nullable|in:active,closed,canceled',
            'encerrado' => 'nullable|boolean',
            'diagnostico' => 'nullable|string',
            'solucao' => 'nullable|string',
            // `preco` fica de fora de proposito: e campo comercial e o tecnico
            // nao tem por que mexer no valor da OS.
        ]);

        if (isset($validated['encerrado'])) {
            $validated['encerrado'] = (bool) $validated['encerrado'];
        }

        $order->update($validated);

        return redirect()->route('technician.portal.service-orders.show', $order)
            ->with('success', 'Ordem de servico atualizada com sucesso.');
    }

    public function profile()
    {
        $technician = Auth::guard('technician')->user();

        return view('crm::technician.profile.index', compact('technician'));
    }

    public function updateProfile(Request $request)
    {
        $technician = Auth::guard('technician')->user();

        $validated = $request->validate([
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'cellphone' => 'nullable|string|max:20',
        ]);

        $technician->update($validated);

        return redirect()->route('technician.portal.profile')
            ->with('success', 'Dados atualizados com sucesso.');
    }

    public function changePassword(Request $request)
    {
        $technician = Auth::guard('technician')->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if (! Hash::check($validated['current_password'], $technician->password)) {
            return back()->withErrors(['current_password' => 'Senha atual incorreta.']);
        }

        $technician->update(['password' => bcrypt($validated['new_password'])]);

        return redirect()->route('technician.portal.profile')
            ->with('success', 'Senha alterada com sucesso.');
    }

    public function startServiceOrder($id)
    {
        $technician = Auth::guard('technician')->user();
        $order = $technician->serviceOrders()->findOrFail($id);

        $order->update([
            'situacao' => 'A',
            'status' => 'active',
            'saida' => now()->toDateString(),
        ]);

        return redirect()->route('technician.portal.service-orders.show', $order)
            ->with('success', 'OS iniciada com sucesso.');
    }

    public function completeServiceOrder(Request $request, $id)
    {
        $technician = Auth::guard('technician')->user();
        $order = $technician->serviceOrders()->findOrFail($id);

        $validated = $request->validate([
            'diagnostico' => 'nullable|string',
            'solucao' => 'nullable|string',
        ]);

        $order->update(array_merge([
            'situacao' => 'F',
            'status' => 'closed',
            'encerrado' => true,
        ], $validated));

        return redirect()->route('technician.portal.service-orders.show', $order)
            ->with('success', 'OS concluida com sucesso.');
    }
}
