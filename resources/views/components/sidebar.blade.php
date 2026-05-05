@php
    $groups = [
        'Principal' => [
            ['Dashboard', route('dashboard'), request()->routeIs('dashboard')],
        ],
        'Administração' => [
            ['Usuários', route('users.index'), request()->routeIs('users.*')],
            ['Papéis e permissões', route('under-construction', 'roles'), request()->is('under-construction/roles')],
        ],
        'Gestão Rural' => [
            ['Fazendas', route('farms.index'), request()->routeIs('farms.index')],
            ['Talhões', route('fields.index'), request()->routeIs('fields.index')],
            ['Pastagens', route('pastures.index'), request()->routeIs('pastures.index')],
            ['Mapa', route('map.index'), request()->routeIs('map.index') || request()->routeIs('farms.map')],
        ],
        'Agricultura' => [
            ['Culturas', route('crops.index'), request()->routeIs('crops.index')],
            ['Safras', route('seasons.index'), request()->routeIs('seasons.index')],
            ['Atividades', route('activities.index'), request()->routeIs('activities.index')],
            ['Colheitas', route('under-construction', 'harvests'), request()->is('under-construction/harvests')],
        ],
        'Pecuária' => [
            ['Animais', route('animals.index'), request()->routeIs('animals.index')],
            ['Lotes', route('animal-groups.index'), request()->routeIs('animal-groups.index')],
            ['Pesagens', route('under-construction', 'animal-weights'), request()->is('under-construction/animal-weights')],
            ['Sanidade', route('under-construction', 'animal-health'), request()->is('under-construction/animal-health')],
        ],
        'Operações' => [
            ['Estoque', route('inventory.index'), request()->routeIs('inventory.index')],
            ['Máquinas', route('machines.index'), request()->routeIs('machines.index')],
            ['Manutenção', route('under-construction', 'maintenance'), request()->is('under-construction/maintenance')],
        ],
        'Financeiro e BI' => [
            ['Financeiro', route('finance.index'), request()->routeIs('finance.index')],
            ['Relatórios', route('reports.index'), request()->routeIs('reports.index')],
            ['Dashboards BI', route('under-construction', 'bi'), request()->is('under-construction/bi')],
        ],
        'Roadmap' => [
            ['PWA', route('under-construction', 'pwa'), request()->is('under-construction/pwa')],
            ['Mobile', route('under-construction', 'mobile'), request()->is('under-construction/mobile')],
            ['INPE/NDVI', route('under-construction', 'inpe-ndvi'), request()->is('under-construction/inpe-ndvi')],
            ['IoT/RFID', route('under-construction', 'iot-rfid'), request()->is('under-construction/iot-rfid')],
            ['SaaS', route('under-construction', 'saas'), request()->is('under-construction/saas')],
        ],
    ];
@endphp

<aside class="sidebar">
    <a class="brand" href="{{ route('dashboard') }}">
        <span class="brand-mark">CA</span>
        <span>
            <strong>Campo Aberto</strong>
            <span>Tecnologia Rural</span>
        </span>
    </a>

    @foreach ($groups as $group => $items)
        <div class="menu-group">{{ $group }}</div>
        @foreach ($items as [$label, $url, $active])
            <a class="menu-link {{ $active ? 'active' : '' }}" href="{{ $url }}">
                <span>{{ $label }}</span>
                @if (str_contains($url, 'under-construction'))
                    <span title="Em desenvolvimento">•</span>
                @endif
            </a>
        @endforeach
    @endforeach
</aside>
