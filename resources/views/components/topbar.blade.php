<header class="topbar">
    <div>
        <h1>@yield('page_title', 'Painel operacional')</h1>
        <div class="muted">@yield('breadcrumb', 'Campo Aberto Tecnologia Rural')</div>
    </div>
    <div class="userbox">
        <span>{{ auth()->user()->name }}</span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn secondary" type="submit">Sair</button>
        </form>
    </div>
</header>
