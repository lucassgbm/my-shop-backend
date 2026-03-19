<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin — StreetFit</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #0f172a; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { background: #1e293b; border: 1px solid #334155; border-radius: 1rem; padding: 2.5rem; width: 100%; max-width: 400px; margin: 1rem; }
        .brand { text-align: center; margin-bottom: 2rem; }
        .brand h1 { font-size: 2rem; font-weight: 800; letter-spacing: .15em; color: #f1f5f9; }
        .brand h1 span { color: #7c3aed; }
        .brand p { color: #64748b; font-size: .875rem; margin-top: .25rem; }
        .field { margin-bottom: 1.25rem; }
        label { display: block; font-size: .8rem; font-weight: 600; color: #94a3b8; margin-bottom: .5rem; text-transform: uppercase; letter-spacing: .05em; }
        input[type=email], input[type=password] { width: 100%; background: #0f172a; border: 1px solid #334155; color: #f1f5f9; border-radius: .625rem; padding: .75rem 1rem; font-size: .9rem; outline: none; transition: border-color .2s; }
        input[type=email]:focus, input[type=password]:focus { border-color: #7c3aed; }
        .error { background: #450a0a; border: 1px solid #7f1d1d; color: #fca5a5; border-radius: .625rem; padding: .75rem 1rem; font-size: .875rem; margin-bottom: 1.25rem; }
        button { width: 100%; background: #7c3aed; color: #fff; border: none; border-radius: .625rem; padding: .875rem; font-size: .9rem; font-weight: 600; cursor: pointer; transition: background .2s; margin-top: .5rem; }
        button:hover { background: #6d28d9; }
        button:disabled { opacity: .6; cursor: not-allowed; }
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">
            <h1>STREET<span>FIT</span></h1>
            <p>Painel Administrativo</p>
        </div>

        @if($errors->any())
        <div class="error">{{ $errors->first() }}</div>
        @endif

        @if(session('error'))
        <div class="error">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('filament.admin.auth.login') }}">
            @csrf
            <div class="field">
                <label>E-mail</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="admin@streetfit.com.br">
            </div>
            <div class="field">
                <label>Senha</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>
            <button type="submit">Entrar no Painel</button>
        </form>
    </div>
</body>
</html>
