<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Campo Aberto')</title>
    <style>
        :root { --green:#123f2a; --green-2:#1f6b45; --bg:#f4f7f1; --line:#dce6d7; --text:#17231c; --muted:#647067; --white:#fff; --danger:#a33; --sidebar-width:280px; }
        * { box-sizing: border-box; }
        body { margin:0; font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background:var(--bg); color:var(--text); }
        a { color:inherit; text-decoration:none; }
        .shell { display:grid; grid-template-columns:var(--sidebar-width) 1fr; min-height:100vh; transition:grid-template-columns .18s ease; }
        body.sidebar-collapsed { --sidebar-width:78px; }
        .sidebar { background:var(--green); color:#fff; padding:18px 12px; position:sticky; top:0; height:100vh; overflow:auto; scrollbar-width:thin; }
        .sidebar-head { display:flex; align-items:center; justify-content:space-between; gap:8px; margin-bottom:18px; }
        .brand { min-width:0; display:flex; gap:10px; align-items:center; }
        .brand-mark { flex:0 0 auto; width:42px; height:42px; border-radius:14px; background:#e2f4d8; color:var(--green); display:grid; place-items:center; font-weight:800; }
        .brand-text { min-width:0; }
        .brand strong { display:block; font-size:18px; line-height:1.1; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .brand span span { display:block; opacity:.78; font-size:12px; margin-top:3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .sidebar-toggle { flex:0 0 auto; width:32px; height:32px; border:0; border-radius:10px; cursor:pointer; background:rgba(255,255,255,.12); color:#fff; font-size:22px; line-height:1; display:grid; place-items:center; }
        .sidebar-toggle:hover { background:rgba(255,255,255,.20); }
        .toggle-collapsed { display:none; }
        .menu-group { margin:18px 0 8px; padding:0 10px; font-size:11px; letter-spacing:.08em; text-transform:uppercase; opacity:.65; }
        .menu-link { display:flex; align-items:center; justify-content:flex-start; gap:10px; padding:10px 12px; border-radius:12px; color:#eef8ea; margin:3px 0; min-height:42px; }
        .menu-link:hover, .menu-link.active { background:rgba(255,255,255,.12); }
        .menu-link.disabled { opacity:.6; }
        .menu-icon { flex:0 0 28px; width:28px; height:24px; display:inline-grid; place-items:center; font-weight:800; font-size:15px; }
        .menu-label { min-width:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .menu-status { margin-left:auto; }
        body.sidebar-collapsed .sidebar { padding-left:10px; padding-right:10px; overflow-x:hidden; }
        body.sidebar-collapsed .sidebar-head { justify-content:center; flex-direction:column; }
        body.sidebar-collapsed .brand-text, body.sidebar-collapsed .menu-label, body.sidebar-collapsed .menu-group, body.sidebar-collapsed .menu-status, body.sidebar-collapsed .toggle-expanded { display:none; }
        body.sidebar-collapsed .toggle-collapsed { display:inline; }
        body.sidebar-collapsed .brand { justify-content:center; }
        body.sidebar-collapsed .menu-link { justify-content:center; padding:10px 8px; }
        body.sidebar-collapsed .menu-icon { flex-basis:auto; }
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
        @media (max-width: 900px) { body.sidebar-collapsed { --sidebar-width:1fr; } .shell { grid-template-columns:1fr; } .sidebar { position:relative; height:auto; max-height:70vh; } body.sidebar-collapsed .brand-text, body.sidebar-collapsed .menu-label, body.sidebar-collapsed .menu-group, body.sidebar-collapsed .menu-status { display:initial; } body.sidebar-collapsed .sidebar-head { justify-content:space-between; flex-direction:row; } body.sidebar-collapsed .menu-link { justify-content:flex-start; padding:10px 12px; } .topbar, .content { padding:18px; } }
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
<script>
(function () {
    const collapsedKey = 'campo_aberto_sidebar_collapsed';
    const scrollKey = 'campo_aberto_sidebar_scroll_top';
    const sidebar = document.getElementById('appSidebar');
    const toggle = document.getElementById('sidebarToggle');

    if (!sidebar) return;

    if (localStorage.getItem(collapsedKey) === '1') {
        document.body.classList.add('sidebar-collapsed');
    }

    const savedScrollTop = Number(localStorage.getItem(scrollKey) || 0);
    if (savedScrollTop > 0) {
        requestAnimationFrame(() => { sidebar.scrollTop = savedScrollTop; });
    }

    sidebar.addEventListener('scroll', () => {
        localStorage.setItem(scrollKey, String(sidebar.scrollTop));
    }, { passive: true });

    document.querySelectorAll('[data-sidebar-link]').forEach((link) => {
        link.addEventListener('click', () => {
            localStorage.setItem(scrollKey, String(sidebar.scrollTop));
        });
    });

    if (toggle) {
        toggle.addEventListener('click', () => {
            document.body.classList.toggle('sidebar-collapsed');
            localStorage.setItem(collapsedKey, document.body.classList.contains('sidebar-collapsed') ? '1' : '0');
            localStorage.setItem(scrollKey, String(sidebar.scrollTop));
        });
    }
})();
</script>
@stack('scripts')
</body>
</html>
