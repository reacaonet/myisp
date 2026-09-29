<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Definir senha - MyISP</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #0f172a 100%); position: relative; overflow: hidden; }
        .card { position: relative; z-index: 1; width: 100%; max-width: 420px; padding: 0 16px; }
        .card-inner { background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); border-radius: 20px;
            padding: 40px 32px; box-shadow: 0 25px 60px rgba(0,0,0,0.3), 0 0 0 1px rgba(255,255,255,0.1); }
        .logo-icon { width: 56px; height: 56px; border-radius: 16px; background: linear-gradient(135deg, #2563eb, #4f46e5);
            display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;
            box-shadow: 0 8px 24px rgba(37,99,235,0.4); }
        .logo-icon svg { width: 28px; height: 28px; color: white; }
        h1 { font-size: 22px; font-weight: 700; color: #0f172a; letter-spacing: -0.5px; }
        .lead { font-size: 13px; color: #64748b; margin: 6px 0 24px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px; }
        input[type="password"] { width: 100%; padding: 12px 14px; border: 1.5px solid #e2e8f0; border-radius: 12px;
            font-size: 14px; color: #0f172a; background: #f8fafc; outline: none; transition: all 0.2s; }
        input[type="password"]:focus { border-color: #2563eb; background: #fff; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
        .error-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 12px 14px;
            margin-bottom: 20px; font-size: 13px; color: #dc2626; }
        .hint { font-size: 12px; color: #94a3b8; margin-top: 6px; }
        .btn-submit { width: 100%; padding: 13px; border: none; border-radius: 12px; font-size: 14px; font-weight: 600;
            color: white; cursor: pointer; background: linear-gradient(135deg, #2563eb, #4f46e5);
            box-shadow: 0 4px 14px rgba(37,99,235,0.4); }
        .btn-submit:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(37,99,235,0.5); }
        .footer { text-align: center; margin-top: 24px; font-size: 12px; color: rgba(255,255,255,0.4); }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-inner">
            <div class="logo-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <h1>Definir sua senha</h1>
            <p class="lead">Voce foi convidado(a) como administrador da {{ auth()->user()->companies()->value('name') ?? 'franquia' }}. Defina uma senha para acessar o sistema.</p>

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('PUT')

                @if($errors->any())
                <div class="error-box">{{ $errors->first() }}</div>
                @endif

                <div class="form-group">
                    <label for="current_password">Senha atual (temporaria)</label>
                    <input type="password" name="current_password" id="current_password" required autocomplete="current-password">
                    @error('current_password') <p class="hint" style="color:#dc2626">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label for="password">Nova senha</label>
                    <input type="password" name="password" id="password" required autocomplete="new-password">
                    <p class="hint">Minimo de 8 caracteres.</p>
                    @error('password') <p class="hint" style="color:#dc2626">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirmar nova senha</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password">
                </div>

                <button type="submit" class="btn-submit">Salvar e continuar</button>
            </form>
        </div>
        <div class="footer">&copy; {{ date('Y') }} MyISP &mdash; Todos os direitos reservados</div>
    </div>
</body>
</html>
