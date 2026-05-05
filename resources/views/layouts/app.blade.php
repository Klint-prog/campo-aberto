<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Campo Aberto')</title>
    <style>
        :root { --green:#123f2a; --green-2:#1f6b45; --bg:#f4f7f1; --line:#dce6d7; --text:#17231c; --muted:#647067; --white:#fff; --danger:#a33; }
        * { box-sizing: border-box; }
        body { margin:0; font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background:var(--bg); color:var(--text); }
        a { color:inherit; text-decoration:none; }
        .shell { display:grid; grid-template-columns:280px 1fr; min-height:100vh; }
        .sidebar { background:var(--green); color:#fff; padding:22px 18px; position:sticky; top:0; height:100vh; overflow:auto; }
        .brand { display:flex; gap:10px; align-items:center; margin-bottom:24px; }
        .brand-mark { width:42px; height:42px; border-radius:14px; background:#e2f4d8; color:var(--green); display:grid; place-items:center; font-weight:800; }
        .brand strong { display:block; font-size:18px; line-height:1.1; }
        .brand span { display:block; opacity:.78; font-size:12px; margin-top:3px; }
        .menu-group { margin:20px 0 8px; font-size:11px; letter-spacing:.08em; text-transform:uppercase; opacity:.65; }
        .menu-link { display:flex; align-items:center; justify-content:space-between; gap:8px; padding:10px 12px; border-radius:12px; color:#eef8ea; margin:3px 0; }
        .menu-link:hover, .menu-link.active { background:rgba(255,255,255,.12); }
        .menu-link.disabled { opacity:.6; }
        .main { min-width:0; }
        .topbar { min-height:70px; display:flex; justify-content:space-between; align-items:center; padding:16px 28px; background:var(--white); border-bottom:1px solid var(--line); }
        .topbar h1 { margin:0; font-size:22px; }
        .userbox { display:flex; gap:12px; align-items:center; color:var(--muted); font-size:14px; }
        .content { padding:28px; }
        .card { background:var(--white); border:1px solid var(--line); border-radius:18px; box-shadow:0 10px 30px rgba(18,63,42,.06); padding:20px; }
        .grid { display:grid; gap:18px; }
        .grid.cards { grid-template-columns:repeat(auto-fit, minmax(210px, 1fr)); }
        .muted { color:var(--muted); }
        .btn, button.btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; border:0; border-radius:12px; padding:10px 14px; font-weight:700; cursor:pointer; background:var(--green-2); color:#fff; }
        .btn.secondary { background:#eaf2e6; color:var(--green); }
        .btn.danger { background:#f7e7e4; color:var(--danger); }
        .table-wrap { overflow:auto; border:1px solid var(--line); border-radius:16px; background:#fff; }
        table { width:100%; border-collapse:collapse; min-width:720px; }
        th, td { padding:13px 14px; border-bottom:1px solid var(--line); text-align:left; vertical-align:top; }
        th { background:#f7faf5; color:#425047; font-size:13px; }
        tr:last-child td { border-bottom:0; }
        .badge { display:inline-flex; border-radius:999px; padding:4px 9px; background:#eaf2e6; color:var(--green); font-size:12px; font-weight:700; }
        .flash { border-radius:14px; padding:13px 15px; margin-bottom:18px; background:#eaf7e9; border:1px solid #cdebc7; color:#235b32; }
        .flash.error { background:#fff0ed; border-color:#f1c5be; color:#943b2f; }
        .form-grid { display:grid; gap:14px; max-width:760px; }
        label { display:block; font-weight:700; margin-bottom:6px; }
        input, select { width:100%; border:1px solid var(--line); border-radius:12px; padding:11px 12px; background:#fff; font:inherit; }
        .errors { color:var(--danger); font-size:13px; margin-top:4px; }
        .actions { display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
        .empty { text-align:center; padding:34px; color:var(--muted); }
        .pagination { margin-top:18px; }
        @media (max-width: 900px) { .shell { grid-template-columns:1fr; } .sidebar { position:relative; height:auto; } .topbar, .content { padding:18px; } }
    </style>
    @stack('head')
</head>
<body>
<div class="shell">
    @include('components.sidebar')
    <main class="main">
        @include('components.topbar')
        <section class="content">
            @include('components.flash')
            @yield('content')
        </section>
    </main>
</div>
@stack('scripts')
</body>
</html>
