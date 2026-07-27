<?php

namespace Modules\PortalInfra\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InfraLoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('infra.dashboard');
        }

        return view('infra::auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (!Auth::attempt($credentials, $remember)) {
            return back()
                ->withErrors(['email' => 'Credenciais invalidas.'])
                ->onlyInput('email');
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return back()
                ->withErrors(['email' => 'Sua conta esta desativada.'])
                ->onlyInput('email');
        }

        $allowedGroups = ['superadmin', 'admin', 'gerente', 'operador', 'tecnico'];

        if (!$user->group || !in_array($user->group->slug, $allowedGroups)) {
            Auth::logout();
            return back()
                ->withErrors(['email' => 'Voce nao tem acesso ao Portal de Infraestrutura.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('infra.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('infra.login');
    }
}
