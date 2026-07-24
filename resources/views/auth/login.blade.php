<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MyISP</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #0f172a 100%);
            position: relative; overflow: hidden; }
        body::before { content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
            background: radial-gradient(circle at 30% 50%, rgba(59,130,246,0.15) 0%, transparent 50%),
                        radial-gradient(circle at 70% 80%, rgba(99,102,241,0.1) 0%, transparent 50%);
            animation: float 20s ease-in-out infinite; }
        @keyframes float { 0%, 100% { transform: translate(0, 0); } 50% { transform: translate(-2%, 2%); } }
        .login-card { position: relative; z-index: 1; width: 100%; max-width: 400px; padding: 0 16px; }
        .card-inner { background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); border-radius: 20px;
            padding: 40px 32px; box-shadow: 0 25px 60px rgba(0,0,0,0.3), 0 0 0 1px rgba(255,255,255,0.1); }
        .logo { text-align: center; margin-bottom: 32px; }
        .logo-icon { width: 56px; height: 56px; border-radius: 16px; background: linear-gradient(135deg, #2563eb, #4f46e5);
            display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;
            box-shadow: 0 8px 24px rgba(37,99,235,0.4); }
        .logo-icon svg { width: 28px; height: 28px; color: white; }
        .logo h1 { font-size: 24px; font-weight: 700; color: #0f172a; letter-spacing: -0.5px; }
        .logo h1 span { color: #2563eb; }
        .logo p { font-size: 13px; color: #64748b; margin-top: 4px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px; }
        .input-wrap { position: relative; }
        .input-wrap svg { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px;
            color: #94a3b8; pointer-events: none; transition: color 0.2s; }
        .input-wrap input { width: 100%; padding: 12px 14px 12px 44px; border: 1.5px solid #e2e8f0; border-radius: 12px;
            font-size: 14px; color: #0f172a; background: #f8fafc; outline: none; transition: all 0.2s; }
        .input-wrap input:focus { border-color: #2563eb; background: #fff; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
        .input-wrap input:focus + svg,
        .input-wrap input:focus ~ svg { color: #2563eb; }
        .input-wrap input.error { border-color: #ef4444; }
        .error-msg { font-size: 12px; color: #ef4444; margin-top: 4px; }
        .error-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 12px 14px;
            margin-bottom: 20px; font-size: 13px; color: #dc2626; display: flex; align-items: center; gap: 8px; }
        .error-box svg { width: 18px; height: 18px; flex-shrink: 0; }
        .remember-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
        .remember-row label { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #64748b; cursor: pointer; }
        .remember-row input[type="checkbox"] { width: 16px; height: 16px; border-radius: 4px; border: 1.5px solid #cbd5e1;
            accent-color: #2563eb; cursor: pointer; }
        .btn-submit { width: 100%; padding: 13px; border: none; border-radius: 12px; font-size: 14px; font-weight: 600;
            color: white; cursor: pointer; transition: all 0.2s;
            background: linear-gradient(135deg, #2563eb, #4f46e5);
            box-shadow: 0 4px 14px rgba(37,99,235,0.4); }
        .btn-submit:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(37,99,235,0.5); }
        .btn-submit:active { transform: translateY(0); }
        .footer { text-align: center; margin-top: 24px; font-size: 12px; color: rgba(255,255,255,0.4); }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="card-inner">
            <div class="logo">
                <div class="logo-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <h1>My<span>ISP</span></h1>
                <p>Painel Administrativo</p>
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                @if($errors->any())
                <div class="error-box">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $errors->first() }}
                </div>
                @endif

                <div class="form-group">
                    <label for="email">E-mail</label>
                    <div class="input-wrap">
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus placeholder="seu@email.com" @error('email') class="error" @enderror>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                    </div>
                    @error('email') <p class="error-msg">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label for="password">Senha</label>
                    <div class="input-wrap">
                        <input type="password" name="password" id="password" required placeholder="Sua senha">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                </div>

                <div class="remember-row">
                    <label>
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        Lembrar-me
                    </label>
                </div>

                <button type="submit" class="btn-submit">Entrar</button>
            </form>
        </div>
        <div class="footer">&copy; {{ date('Y') }} MyISP &mdash; Todos os direitos reservados</div>
    </div>
</body>
</html>
