<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mapa - {{ $farm->name }} | Campo Aberto</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIINfQAx6z3w9M4z3SqM9EH3rIsnhkD9PA=" crossorigin="">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; color: #123; background: #f5f7f2; }
        a { color: inherit; }
        header { padding: 14px 24px; background: #123f2a; color: #fff; display: flex; justify-content: space-between; gap: 16px; align-items: center; flex-wrap: wrap; }
        header h1 { margin: 0; font-size: 20px; }
        header p { margin: 4px 0 0; opacity: .85; }
        .header-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .nav-link { display: inline-flex; align-items: center; gap: 6px; padding: 8px 10px; border-radius: 8px; background: rgba(255,255,255,.12); color: #fff; text-decoration: none; font-size: 13px; font-weight: 700; }
        .nav-link:hover { background: rgba(255,255,255,.2); }
        main { display: grid; grid-template-columns: 320px minmax(0, 1fr); height: calc(100vh - 76px); min-height: 560px; }
        aside { padding: 20px; background: #fff; border-right: 1px solid #dde5d8; overflow-y: auto; }
        aside h2 { margin-top: 0; font-size: 16px; }
        aside ul { padding-left: 18px; line-height: 1.7; }
        #map { width: 100%; height: 100%; background: #dbe7d3; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 999px; background: #e8f4e4; color: #123f2a; font-size: 12px; font-weight: bold; }
        .error { margin-top: 12px; color: #8a1f11; line-height: 1.4; }
        .notice { margin-top: 12px; padding: 10px; border-radius: 10px; background: #eef7ec; color: #123f2a; font-size: 13px; line-height: 1.45; }
        .map-shell { position: relative; min-width: 0; height: 100%; }
        .map-mode-control { position: absolute; top: 16px; right: 16px; z-index: 1000; background: #fff; border-radius: 12px; padding: 8px; box-shadow: 0 8px 24px rgba(0,0,0,.18); display: flex; gap: 6px; align-items: center; }
        .map-mode-control button { border: 0; padding: 8px 12px; cursor: pointer; border-radius: 8px; background: #eef2eb; color: #123f2a; font-weight: 700; }
        .map-mode-control button.active { background: #166534; color: #fff; }
        .map-mode-control button:disabled { cursor: not-allowed; opacity: .55; }
        .map-source-indicator { position: absolute; bottom: 20px; left: 20px; z-index: 1000; background: rgba(255,255,255,.96); border-radius: 10px; padding: 8px 12px; box-shadow: 0 4px 16px rgba(0,0,0,.16); font-size: 13px; color: #123f2a; max-width: min(420px, calc(100% - 40px)); }
        .map-source-indicator strong { display: block; font-size: 12px; text-transform: uppercase; letter-spacing: .04em; color: #4a5c50; }
        @media (max-width: 900px) { main { grid-template-columns: 1fr; height: auto; } aside { border-right: 0; border-bottom: 1px solid #dde5d8; } .map-shell { height: 70vh; min-height: 460px; } }
    </style>
</head>
<body>
<header>
    <div>
        <h1>Mapa da fazenda: {{ $farm->name }}</h1>
        <p>{{ $farm->city }} {{ $farm->state ? '/ '.$farm->state : '' }}</p>
    </div>
    <div class="header-actions" aria-label="Navegação da plataforma">
        <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
        <a class="nav-link" href="{{ route('farms.index') }}">Fazendas</a>
        <a class="nav-link" href="{{ route('map.index') }}">Mapas</a>
        <a class="nav-link" href="javascript:history.back()">Voltar</a>
        <span class="badge">Leaflet + OpenStreetMap</span>
    </div>
</header>
<main>
    <aside>
        <h2>Camadas carregadas</h2>
        <ul>
            <li>Talhões</li>
            <li>Pastagens</li>
            <li>Áreas/campos territoriais</li>
            <li>Pontos de interesse</li>
        </ul>
        <p>Os dados são consumidos do endpoint GeoJSON interno da fazenda e respeitam tenant e autorização de acesso.</p>
        <p><strong>Endpoint:</strong><br><code>{{ route('api.internal.v1.farms.geojson', $farm) }}</code></p>
        <p><strong>Status offline:</strong><br><span id="offline-status">Verificando pacote offline...</span></p>
        <div class="notice">Use <strong>Online</strong> para navegar pelo mapa base público. Use <strong>Offline</strong> apenas quando existir pacote local completo para esta fazenda.</div>
        <div id="map-error" class="error"></div>
    </aside>
    <section class="map-shell">
        <div class="map-mode-control" aria-label="Selecionar fonte do mapa">
            <button type="button" id="onlineMapBtn" class="active">Online</button>
            <button type="button" id="offlineMapBtn" disabled>Offline</button>
        </div>
        <div id="map-source-indicator" class="map-source-indicator">
            <strong>Fonte do mapa</strong>
            <span id="map-source-text">Online — OpenStreetMap</span>
        </div>
        <div id="map"></div>
    </section>
</main>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    const farmId = @json((string) $farm->id);
    const map = L.map('map', {
        preferCanvas: true,
        zoomControl: true,
        worldCopyJump: true
    }).setView([-8.05, -34.9], 6);

    const mapError = document.getElementById('map-error');
    const offlineStatus = document.getElementById('offline-status');
    const mapSourceText = document.getElementById('map-source-text');
    const onlineBtn = document.getElementById('onlineMapBtn');
    const offlineBtn = document.getElementById('offlineMapBtn');
    const storageKey = `campo_aberto_map_mode_${farmId}`;

    const onlineLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        maxNativeZoom: 19,
        updateWhenIdle: true,
        keepBuffer: 4,
        detectRetina: false,
        attribution: '&copy; OpenStreetMap contributors'
    });

    let offlineLayer = null;
    let offlineAvailable = false;
    let offlineMessage = 'Mapa offline ainda não disponível para esta fazenda.';
    let offlineTileErrors = 0;

    onlineLayer.addTo(map);

    window.addEventListener('load', () => setTimeout(() => map.invalidateSize(), 150));
    window.addEventListener('resize', () => map.invalidateSize());

    function clearMapError() {
        mapError.innerText = '';
    }

    function setMapMode(mode) {
        clearMapError();

        if (mode === 'offline' && !offlineAvailable) {
            mapError.innerText = offlineMessage;
            return;
        }

        if (mode === 'offline') {
            if (map.hasLayer(onlineLayer)) map.removeLayer(onlineLayer);
            if (offlineLayer && !map.hasLayer(offlineLayer)) offlineLayer.addTo(map);
            offlineTileErrors = 0;
            offlineBtn.classList.add('active');
            onlineBtn.classList.remove('active');
            mapSourceText.innerText = 'Offline — pacote local da fazenda';
            localStorage.setItem(storageKey, 'offline');
            setTimeout(() => map.invalidateSize(), 80);
            return;
        }

        if (offlineLayer && map.hasLayer(offlineLayer)) map.removeLayer(offlineLayer);
        if (!map.hasLayer(onlineLayer)) onlineLayer.addTo(map);
        onlineBtn.classList.add('active');
        offlineBtn.classList.remove('active');
        mapSourceText.innerText = 'Online — OpenStreetMap';
        localStorage.setItem(storageKey, 'online');
        setTimeout(() => map.invalidateSize(), 80);
    }

    onlineBtn.addEventListener('click', () => setMapMode('online'));
    offlineBtn.addEventListener('click', () => setMapMode('offline'));

    window.addEventListener('offline', () => {
        if (offlineAvailable) {
            setMapMode('offline');
        } else {
            mapError.innerText = 'Conexão perdida e não há pacote offline pronto para esta fazenda.';
        }
    });

    fetch(@json(route('farms.map.offline.status', $farm)))
        .then(response => response.json())
        .then(({ data }) => {
            offlineAvailable = Boolean(data.available);
            offlineMessage = data.message;
            offlineStatus.innerText = data.message;
            offlineBtn.disabled = !offlineAvailable;

            if (offlineAvailable && data.tile_url_template) {
                offlineLayer = L.tileLayer(data.tile_url_template, {
                    maxZoom: data.package?.max_zoom || 18,
                    maxNativeZoom: data.package?.max_zoom || 18,
                    minZoom: data.package?.min_zoom || 0,
                    updateWhenIdle: true,
                    keepBuffer: 4,
                    attribution: 'Mapa offline local'
                });

                offlineLayer.on('tileerror', () => {
                    offlineTileErrors++;
                    if (offlineTileErrors >= 3) {
                        setMapMode('online');
                        mapError.innerText = 'O pacote offline desta área parece incompleto. Voltei para o mapa online para evitar mosaico quebrado.';
                    }
                });

                const savedMode = localStorage.getItem(storageKey) || 'online';
                if (savedMode === 'offline' && offlineAvailable) {
                    setMapMode('offline');
                }
            } else {
                localStorage.setItem(storageKey, 'online');
            }
        })
        .catch(() => {
            offlineStatus.innerText = 'Não foi possível verificar o pacote offline.';
            localStorage.setItem(storageKey, 'online');
        });

    function layerStyle(feature) {
        const layer = feature?.properties?.layer || 'default';
        const styles = {
            plot: { weight: 2, fillOpacity: 0.25 },
            pasture: { weight: 2, fillOpacity: 0.20, dashArray: '5, 5' },
            field: { weight: 3, fillOpacity: 0.10 },
            map_feature: { weight: 2, fillOpacity: 0.35 }
        };
        return styles[layer] || { weight: 2, fillOpacity: 0.15 };
    }

    function popupContent(feature) {
        const props = feature.properties || {};
        return `<strong>${props.name || 'Sem nome'}</strong><br>` +
            `Camada: ${props.layer || '-'}<br>` +
            `Área: ${props.area_ha || '-'} ha<br>` +
            `Perímetro: ${props.perimeter_m || '-'} m`;
    }

    fetch(@json(route('api.internal.v1.farms.geojson', $farm)))
        .then(response => {
            if (!response.ok) throw new Error('Falha ao carregar GeoJSON autorizado.');
            return response.json();
        })
        .then(data => {
            const geoJsonLayer = L.geoJSON(data, {
                style: layerStyle,
                pointToLayer: (feature, latlng) => L.marker(latlng),
                onEachFeature: (feature, layer) => layer.bindPopup(popupContent(feature))
            }).addTo(map);

            if (geoJsonLayer.getLayers().length > 0) {
                map.fitBounds(geoJsonLayer.getBounds(), { padding: [24, 24] });
                setTimeout(() => map.invalidateSize(), 120);
            }
        })
        .catch(error => {
            mapError.innerText = error.message;
        });
</script>
</body>
</html>
