<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Campo Aberto</title>
    <style>
        :root { --green:#123f2a; --green-2:#1f6b45; --bg:#edf4e8; --line:#dce6d7; --text:#17231c; --muted:#647067; --danger:#9b2f22; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; display:grid; place-items:center; font-family:Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background:linear-gradient(135deg,#eff7eb,#d9ead1); color:var(--text); padding:24px; }
        main { width:min(980px, 100%); display:grid; grid-template-columns:1fr 420px; background:#fff; border:1px solid var(--line); border-radius:28px; overflow:hidden; box-shadow:0 30px 80px rgba(18,63,42,.16); }
        .hero { background:var(--green); color:#fff; padding:48px; display:flex; flex-direction:column; justify-content:space-between; min-height:560px; }
        .logo { width:58px; height:58px; border-radius:18px; background:#e2f4d8; color:var(--green); display:grid; place-items:center; font-weight:900; font-size:22px; }
        h1 { margin:24px 0 12px; font-size:38px; line-height:1.05; }
        .hero p { color:#dcebd7; font-size:17px; line-height:1.6; }
        .panel { padding:42px; display:flex; flex-direction:column; justify-content:center; }
        h2 { margin:0 0 8px; font-size:26px; }
        .muted { color:var(--muted); margin-top:0; }
        label { display:block; font-weight:700; margin:18px 0 7px; }
        input[type=email], input[type=password] { width:100%; border:1px solid var(--line); border-radius:13px; padding:13px 14px; font:inherit; }
        .remember { display:flex; gap:8px; align-items:center; font-weight:500; margin:16px 0; color:var(--muted); }
        .remember input { width:auto; }
        button { width:100%; border:0; border-radius:13px; background:var(--green-2); color:#fff; font-weight:800; padding:13px; cursor:pointer; font:inherit; }
        .error { color:var(--danger); font-size:13px; margin:6px 0 0; }
        .demo { margin-top:22px; border-radius:14px; padding:13px; background:#f4f8f1; color:var(--muted); font-size:14px; }
        @media (max-width: 820px) { main { grid-template-columns:1fr; } .hero { min-height:auto; padding:32px; } .panel { padding:28px; } }
    </style>
</head>
<body>
<main>
    <section class="hero">
        <div>
            <div class="logo">CA</div>
            <h1>Campo Aberto Tecnologia Rural</h1>
            <p>Gestão agrícola e pecuária open source com fazendas, mapas, operações, estoque, financeiro e relatórios em uma plataforma única.</p>
        </div>
        <p>Ambiente administrativo protegido por autenticação, tenant e políticas de acesso.</p>
    </section>

    <section class="panel">
        <h2>Acessar plataforma</h2>
        <p class="muted">Entre para visualizar o dashboard e os módulos operacionais disponíveis.</p>

        <form method="POST" action="{{ route('login.store') }}">
            @csrf

            <label for="email">E-mail</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            @error('email')<p class="error">{{ $message }}</p>@enderror

            <label for="password">Senha</label>
            <input id="password" name="password" type="password" required autocomplete="current-password">
            @error('password')<p class="error">{{ $message }}</p>@enderror

            <label class="remember">
                <input type="checkbox" name="remember" value="1">
                Manter conectado
            </label>

            <button type="submit">Entrar</button>
        </form>

        <div class="demo">
            Demo local: <strong>admin@campoaberto.local</strong><br>
            Senha: <strong>password</strong>
        </div>
    </section>
</main>
</body>
</html>
