<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mapa - {{ $farm->name }} | Campo Aberto</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIINfQAx6z3w9M4z3SqM9EH3rIsnhkD9PA=" crossorigin="">
    <style>
        body { margin: 0; font-family: Arial, sans-serif; color: #123; background: #f5f7f2; }
        header { padding: 16px 24px; background: #123f2a; color: #fff; display: flex; justify-content: space-between; align-items: center; }
        header h1 { margin: 0; font-size: 20px; }
        header p { margin: 4px 0 0; opacity: .85; }
        main { display: grid; grid-template-columns: 320px 1fr; min-height: calc(100vh - 73px); }
        aside { padding: 20px; background: #fff; border-right: 1px solid #dde5d8; }
        aside h2 { margin-top: 0; font-size: 16px; }
        aside ul { padding-left: 18px; line-height: 1.7; }
        #map { width: 100%; min-height: calc(100vh - 73px); }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 999px; background: #e8f4e4; color: #123f2a; font-size: 12px; font-weight: bold; }
        .error { margin-top: 12px; color: #8a1f11; }
        @media (max-width: 800px) { main { grid-template-columns: 1fr; } aside { border-right: 0; border-bottom: 1px solid #dde5d8; } #map { min-height: 70vh; } }
    </style>
</head>
<body>
<header>
    <div>
        <h1>Mapa da fazenda: {{ $farm->name }}</h1>
        <p>{{ $farm->city }} {{ $farm->state ? '/ '.$farm->state : '' }}</p>
    </div>
    <span class="badge">Leaflet + OpenStreetMap</span>
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
        <div id="map-error" class="error"></div>
    </aside>
    <div id="map"></div>
</main>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    const map = L.map('map').setView([-8.05, -34.9], 6);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

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
            }
        })
        .catch(error => {
            document.getElementById('map-error').innerText = error.message;
        });
</script>
</body>
</html>
