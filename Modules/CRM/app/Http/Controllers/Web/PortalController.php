<?php

namespace Modules\CRM\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\CRM\Models\Client;

class PortalController extends Controller
{
    public function loginForm()
    {
        return view('crm::portal.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'senha' => 'required|string',
        ]);

        if (Auth::guard('client')->attempt([
            'login' => $credentials['login'],
            'password' => $credentials['senha'],
        ], $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('crm.portal.dashboard'));
        }

        return back()->withErrors(['login' => 'Credenciais invalidas.'])->onlyInput('login');
    }

    public function logout(Request $request)
    {
        Auth::guard('client')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('crm.portal.login');
    }

    public function dashboard()
    {
        $client = Auth::guard('client')->user()->load([
            'addresses',
            'contracts.plan',
            'invoices' => fn($q) => $q->latest(),
            'serviceOrders' => fn($q) => $q->latest(),
        ]);

        return view('crm::portal.dashboard', compact('client'));
    }
}
