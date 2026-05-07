@php
    $groups = [
        'Principal' => [
            ['Dashboard', route('dashboard'), request()->routeIs('dashboard'), '⌂'],
        ],
        'Administração' => [
            ['Usuários', route('users.index'), request()->routeIs('users.*'), '👤'],
            ['Papéis e permissões', route('under-construction', 'roles'), request()->is('under-construction/roles'), '🔐'],
        ],
        'Gestão Rural' => [
            ['Fazendas', route('farms.index'), request()->routeIs('farms.*'), '▣'],
            ['Talhões', route('fields.index'), request()->routeIs('fields.*'), '▤'],
            ['Pastagens', route('pastures.index'), request()->routeIs('pastures.*'), '♧'],
            ['Mapa', route('map.index'), request()->routeIs('map.index') || request()->routeIs('farms.map'), '🗺'],
        ],
        'Agricultura' => [
            ['Culturas', route('crops.index'), request()->routeIs('crops.*'), '🌱'],
            ['Variedades', route('crop-varieties.index'), request()->routeIs('crop-varieties.*'), '✤'],
            ['Safras', route('seasons.index'), request()->routeIs('seasons.*'), '☷'],
            ['Atividades', route('activities.index'), request()->routeIs('activities.*'), '✓'],
            ['Colheitas', route('harvests.index'), request()->routeIs('harvests.*'), '☀'],
            ['Relatório agrícola', route('reports.agriculture'), request()->routeIs('reports.agriculture'), '▥'],
        ],
        'Pecuária' => [
            ['Animais', route('animals.index'), request()->routeIs('animals.*'), '🐄'],
            ['Lotes', route('animal-lots.index'), request()->routeIs('animal-lots.*'), '▦'],
            ['Relatório pecuário', route('reports.livestock'), request()->routeIs('reports.livestock'), '▧'],
        ],
        'Operações' => [
            ['Estoque', route('inventory.index'), request()->routeIs('inventory.index'), '▨'],
            ['Máquinas', route('machines.index'), request()->routeIs('machines.index'), '⚙'],
            ['Manutenção', route('under-construction', 'maintenance'), request()->is('under-construction/maintenance'), '🛠'],
        ],
        'Financeiro e BI' => [
            ['Financeiro', route('finance.index'), request()->routeIs('finance.index'), 'R$'],
            ['Relatórios', route('reports.index'), request()->routeIs('reports.index'), '▩'],
            ['Dashboards BI', route('under-construction', 'bi'), request()->is('under-construction/bi'), '◈'],
        ],
        'Roadmap' => [
            ['PWA', route('under-construction', 'pwa'), request()->is('under-construction/pwa'), '⬡'],
            ['Mobile', route('under-construction', 'mobile'), request()->is('under-construction/mobile'), '▯'],
            ['INPE/NDVI', route('under-construction', 'inpe-ndvi'), request()->is('under-construction/inpe-ndvi'), '☁'],
            ['IoT/RFID', route('under-construction', 'iot-rfid'), request()->is('under-construction/iot-rfid'), '⌁'],
            ['SaaS', route('under-construction', 'saas'), request()->is('under-construction/saas'), '☍'],
        ],
    ];
@endphp

<aside class="sidebar" id="appSidebar">
    <div class="sidebar-head">
        <a class="brand" href="{{ route('dashboard') }}" title="Campo Aberto">
            <span class="brand-mark">CA</span>
            <span class="brand-text">
                <strong>Campo Aberto</strong>
                <span>Tecnologia Rural</span>
            </span>
        </a>
        <button type="button" class="sidebar-toggle" id="sidebarToggle" title="Minimizar menu" aria-label="Minimizar menu">
            <span class="toggle-expanded">‹</span>
            <span class="toggle-collapsed">›</span>
        </button>
    </div>

    <nav class="sidebar-nav" id="sidebarNav" aria-label="Menu principal">
        @foreach ($groups as $group => $items)
            <div class="menu-group">{{ $group }}</div>
            @foreach ($items as [$label, $url, $active, $icon])
                <a class="menu-link {{ $active ? 'active' : '' }}" href="{{ $url }}" title="{{ $label }}" data-sidebar-link>
                    <span class="menu-icon" aria-hidden="true">{{ $icon }}</span>
                    <span class="menu-label">{{ $label }}</span>
                    @if (str_contains($url, 'under-construction'))
                        <span class="menu-status" title="Em desenvolvimento">•</span>
                    @endif
                </a>
            @endforeach
        @endforeach
    </nav>
</aside>
