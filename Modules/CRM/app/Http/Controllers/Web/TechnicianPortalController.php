<?php

namespace Modules\CRM\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\CRM\Models\ServiceOrder;

class TechnicianPortalController extends Controller
{
    public function loginForm()
    {
        return view('crm::technician.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'senha' => 'required|string',
        ]);

        if (Auth::guard('technician')->attempt([
            'login' => $credentials['login'],
            'password' => $credentials['senha'],
        ], $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('technician.portal.dashboard'));
        }

        return back()->withErrors(['login' => 'Credenciais invalidas.'])->onlyInput('login');
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

    public function serviceOrders(Request $request)
    {
        $technician = Auth::guard('technician')->user();

        $query = $technician->serviceOrders()->with(['client', 'contract.plan']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
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
            'status' => 'nullable|in:active,in_progress,resolved,closed,canceled',
            'encerrado' => 'nullable|boolean',
            'diagnostico' => 'nullable|string',
            'solucao' => 'nullable|string',
            'preco' => 'nullable|numeric|min:0',
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
            'current_senha' => 'required|string',
            'new_senha' => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($validated['current_senha'], $technician->senha)) {
            return back()->withErrors(['current_senha' => 'Senha atual incorreta.']);
        }

        $technician->update(['senha' => $validated['new_senha']]);

        return redirect()->route('technician.portal.profile')
            ->with('success', 'Senha alterada com sucesso.');
    }
}