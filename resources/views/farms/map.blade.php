<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mapa - {{ $farm->name }} | Campo Aberto</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; color: #122033; background: #f5f7f2; }
        header { min-height: 76px; padding: 14px 24px; background: #123f2a; color: #fff; display: flex; justify-content: space-between; gap: 16px; align-items: center; flex-wrap: wrap; }
        h1 { margin: 0; font-size: 20px; }
        header p { margin: 4px 0 0; opacity: .85; }
        main { display: grid; grid-template-columns: 340px minmax(0, 1fr); height: calc(100vh - 76px); min-height: 560px; }
        aside { padding: 20px; background: #fff; border-right: 1px solid #dde5d8; overflow-y: auto; }
        code { overflow-wrap: anywhere; }
        label { display: block; margin-top: 10px; font-size: 12px; font-weight: 700; color: #334155; }
        input, textarea, select { width: 100%; margin-top: 4px; padding: 8px 9px; border: 1px solid #cbd5c6; border-radius: 8px; font: inherit; }
        textarea { min-height: 58px; resize: vertical; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
        .nav-link { padding: 8px 10px; border-radius: 8px; background: rgba(255,255,255,.12); color: #fff; text-decoration: none; font-size: 13px; font-weight: 700; }
        .badge { padding: 5px 9px; border-radius: 999px; background: #e8f4e4; color: #123f2a; font-size: 12px; font-weight: 700; }
        .error { margin-top: 12px; color: #8a1f11; line-height: 1.4; }
        .sidebar-section { margin-top: 0; padding-top: 0; }
        .sidebar-section h2 { margin-top: 0; }
        .sidebar-help { margin: 6px 0 12px; color: #475569; font-size: 13px; line-height: 1.45; }
        .button-row { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 10px; }
        .sidebar-button { border: 0; border-radius: 8px; padding: 8px 10px; cursor: pointer; background: #166534; color: #fff; font-weight: 700; font-size: 13px; }
        .sidebar-button.secondary { background: #e2e8dc; color: #123f2a; }
        .sidebar-button.danger { background: #991b1b; color: #fff; }
        .quick-point-list { margin-top: 10px; display: grid; gap: 8px; }
        .quick-point-card { border: 1px solid #e2e8dc; border-radius: 10px; padding: 9px; background: #f8faf6; }
        .quick-point-card strong { display: block; color: #123f2a; }
        .quick-point-card small { display: block; color: #64748b; margin-top: 2px; }
        .quick-point-card .button-row { margin-top: 8px; }
        .map-shell { position: relative; min-width: 0; height: 100%; }
        #map { width: 100%; height: 100%; background-color: #dfead7; background-image: linear-gradient(rgba(18,63,42,.10) 1px, transparent 1px), linear-gradient(90deg, rgba(18,63,42,.10) 1px, transparent 1px); background-size: 64px 64px; }
        .map-mode-control { position: absolute; top: 16px; right: 16px; z-index: 1000; background: #fff; border-radius: 12px; padding: 8px; box-shadow: 0 8px 24px rgba(0,0,0,.18); display: flex; gap: 6px; }
        .map-mode-control button { border: 0; padding: 8px 12px; cursor: pointer; border-radius: 8px; background: #eef2eb; color: #123f2a; font-weight: 700; }
        .map-mode-control button.active { background: #166534; color: #fff; }
        .map-mode-control button:disabled { cursor: not-allowed; opacity: .55; }
        .map-source-indicator { position: absolute; bottom: 20px; left: 20px; z-index: 1000; background: rgba(255,255,255,.96); border-radius: 10px; padding: 8px 12px; box-shadow: 0 4px 16px rgba(0,0,0,.16); font-size: 13px; color: #123f2a; max-width: min(460px, calc(100% - 40px)); }
        .map-source-indicator strong { display: block; font-size: 12px; text-transform: uppercase; letter-spacing: .04em; color: #4a5c50; }
        .fallback-dot { width: 18px; height: 18px; border-radius: 50%; background: #166534; border: 3px solid #fff; box-shadow: 0 2px 8px rgba(0,0,0,.35); }
        .quick-point-dot { width: 22px; height: 22px; border-radius: 50%; background: #f97316; border: 3px solid #fff; box-shadow: 0 2px 8px rgba(0,0,0,.35); }
        @media (max-width: 900px) { main { grid-template-columns: 1fr; height: auto; } aside { border-right: 0; border-bottom: 1px solid #dde5d8; } .map-shell { height: 70vh; min-height: 460px; } }
    </style>
</head>
<body>
<header>
    <div>
        <h1>Mapa da fazenda: {{ $farm->name }}</h1>
        <p>{{ $farm->city }} {{ $farm->state ? '/ '.$farm->state : '' }}</p>
    </div>
    <div class="actions" aria-label="Navegação da plataforma">
        <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
        <a class="nav-link" href="{{ route('farms.index') }}">Fazendas</a>
        <a class="nav-link" href="{{ route('map.index') }}">Mapas</a>
        <a class="nav-link" href="{{ route('farms.index') }}">Voltar</a>
        <span class="badge">Leaflet + OpenStreetMap</span>
    </div>
</header>
<main>
    <aside>
        <section class="sidebar-section" aria-labelledby="quick-points-title">
            <h2 id="quick-points-title">Pontos rápidos</h2>
            <p class="sidebar-help">Marque locais de acesso rápido no mapa. Estes pontos ficam salvos neste navegador para esta fazenda.</p>
            <label for="quickPointName">Nome do ponto</label>
            <input id="quickPointName" type="text" placeholder="Ex.: Porteira principal, curral, bomba d'água">
            <label for="quickPointType">Tipo</label>
            <select id="quickPointType">
                <option value="Acesso">Acesso</option>
                <option value="Água">Água</option>
                <option value="Curral">Curral</option>
                <option value="Máquina">Máquina</option>
                <option value="Observação">Observação</option>
            </select>
            <label for="quickPointNotes">Observações</label>
            <textarea id="quickPointNotes" placeholder="Informações úteis para campo"></textarea>
            <label for="quickPointLat">Latitude</label>
            <input id="quickPointLat" type="number" step="any" placeholder="Clique no mapa ou use o centro atual">
            <label for="quickPointLng">Longitude</label>
            <input id="quickPointLng" type="number" step="any" placeholder="Clique no mapa ou use o centro atual">
            <div class="button-row">
                <button class="sidebar-button secondary" type="button" id="useMapCenterBtn">Usar centro do mapa</button>
                <button class="sidebar-button" type="button" id="saveQuickPointBtn">Salvar ponto</button>
                <button class="sidebar-button secondary" type="button" id="clearQuickPointFormBtn">Limpar</button>
            </div>
            <div class="button-row">
                <button class="sidebar-button secondary" type="button" id="enablePointClickBtn">Marcar clicando no mapa</button>
                <button class="sidebar-button danger" type="button" id="clearAllQuickPointsBtn">Apagar todos</button>
            </div>
            <div id="quickPointList" class="quick-point-list"></div>
        </section>

        <div id="map-error" class="error"></div>
        <span id="offline-status" hidden>Verificando pacote offline...</span>
    </aside>
    <section class="map-shell">
        <div class="map-mode-control"><button id="localMapBtn" class="active">Local</button><button id="onlineMapBtn">Online</button><button id="offlineMapBtn" disabled>Offline</button></div>
        <div class="map-source-indicator"><strong>Fonte do mapa</strong><span id="map-source-text">Local — base própria</span></div>
        <div id="map"></div>
    </section>
</main>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const farmId = @json((string) $farm->id);
const farmName = @json($farm->name);
const center = @json(config('maps.default_center'));
const tiles = @json(config('maps.online_tiles'));
const map = L.map('map').setView([center.latitude, center.longitude], center.zoom);
const mapError = document.getElementById('map-error');
const offlineStatus = document.getElementById('offline-status');
const mapSourceText = document.getElementById('map-source-text');
const localBtn = document.getElementById('localMapBtn');
const onlineBtn = document.getElementById('onlineMapBtn');
const offlineBtn = document.getElementById('offlineMapBtn');
const key = 'campo_aberto_map_mode_' + farmId;
const quickPointKey = 'campo_aberto_quick_points_' + farmId;
const onlineLayer = L.tileLayer(tiles.url, {maxZoom: tiles.max_zoom, maxNativeZoom: tiles.max_native_zoom, attribution: tiles.attribution});
let offlineLayer = null;
let offlineOk = false;
let onlineErrors = 0;
let offlineErrors = 0;
let quickPointLayer = L.layerGroup().addTo(map);
let clickToMark = false;
function msg(t){ mapError.innerText = t || ''; }
function off(){ if(map.hasLayer(onlineLayer)) map.removeLayer(onlineLayer); if(offlineLayer && map.hasLayer(offlineLayer)) map.removeLayer(offlineLayer); }
function buttons(m){ localBtn.classList.toggle('active', m==='local'); onlineBtn.classList.toggle('active', m==='online'); offlineBtn.classList.toggle('active', m==='offline'); }
function setMode(m){ msg(''); off(); if(m==='online'){ onlineErrors=0; onlineLayer.addTo(map); buttons('online'); mapSourceText.innerText='Online — OpenStreetMap'; localStorage.setItem(key,'online'); return; } if(m==='offline' && offlineOk && offlineLayer){ offlineErrors=0; offlineLayer.addTo(map); buttons('offline'); mapSourceText.innerText='Offline — pacote local'; localStorage.setItem(key,'offline'); return; } buttons('local'); mapSourceText.innerText='Local — base própria'; localStorage.setItem(key,'local'); }
onlineLayer.on('tileerror', function(){ onlineErrors++; if(onlineErrors>=3){ setMode('local'); msg('A base online falhou. Mantive a base local para evitar mosaico quebrado.'); } });
localBtn.onclick = function(){ setMode('local'); };
onlineBtn.onclick = function(){ setMode('online'); };
offlineBtn.onclick = function(){ setMode('offline'); };
window.addEventListener('load', function(){ setTimeout(function(){ map.invalidateSize(); }, 150); });
fetch(@json(route('farms.map.offline.status', $farm)), {headers:{Accept:'application/json'}}).then(function(r){ return r.json(); }).then(function(j){ const d=j.data; offlineOk=!!d.available; offlineStatus.innerText=d.message; offlineBtn.disabled=!offlineOk; if(offlineOk && d.tile_url_template){ offlineLayer=L.tileLayer(d.tile_url_template,{maxZoom:d.package.max_zoom||18,maxNativeZoom:d.package.max_zoom||18,attribution:'Mapa offline local'}); offlineLayer.on('tileerror',function(){ offlineErrors++; if(offlineErrors>=3){ setMode('local'); msg('O pacote offline parece incompleto. Mantive a base local.'); }}); } setMode(localStorage.getItem(key)||'local'); }).catch(function(){ offlineStatus.innerText='Não foi possível verificar o pacote offline.'; setMode('local'); });
function style(f){ const l=(f.properties||{}).layer||'default'; const c={plot:'#166534',pasture:'#65a30d',field:'#92400e',map_feature:'#0369a1'}[l]||'#166534'; return {color:c,weight:2,fillOpacity:.22}; }
function popup(f){ const p=f.properties||{}; return 'Nome: '+(p.name||'Sem nome')+'\nCamada: '+(p.layer||'-'); }
function fallback(t){ const icon=L.divIcon({className:'fallback-dot',iconSize:[18,18]}); L.marker([center.latitude,center.longitude],{icon:icon}).addTo(map).bindPopup(farmName+' - '+t); map.setView([center.latitude,center.longitude],10); }
fetch(@json(route('farms.map.geojson', $farm)), {headers:{Accept:'application/json'}}).then(function(r){ if(!r.ok) throw new Error('GeoJSON HTTP '+r.status); const type=r.headers.get('content-type')||''; if(type.indexOf('application/json')<0) throw new Error('GeoJSON não retornou JSON. Faça login novamente e limpe o cache.'); return r.json(); }).then(function(data){ const layer=L.geoJSON(data,{style:style,onEachFeature:function(f,l){l.bindPopup(popup(f));}}).addTo(map); if(layer.getLayers().length){ map.fitBounds(layer.getBounds(),{padding:[24,24],maxZoom:16}); } else { fallback('Nenhuma geometria cadastrada.'); msg('Nenhuma geometria encontrada. Cadastre ou importe talhões, pastagens ou pontos.'); } }).catch(function(e){ fallback('Não foi possível carregar as geometrias internas.'); msg(e.message); });
function getQuickPoints(){ try { return JSON.parse(localStorage.getItem(quickPointKey) || '[]'); } catch(e) { return []; } }
function saveQuickPoints(points){ localStorage.setItem(quickPointKey, JSON.stringify(points)); }
function quickPointIcon(){ return L.divIcon({className:'quick-point-dot', iconSize:[22,22], iconAnchor:[11,11]}); }
function renderQuickPoints(){ const points=getQuickPoints(); quickPointLayer.clearLayers(); const list=document.getElementById('quickPointList'); list.innerHTML=''; points.forEach(function(p){ L.marker([p.lat,p.lng],{icon:quickPointIcon()}).addTo(quickPointLayer).bindPopup('<strong>'+p.name+'</strong><br>Tipo: '+p.type+'<br>'+((p.notes||'').replace(/</g,'&lt;'))); const card=document.createElement('div'); card.className='quick-point-card'; card.innerHTML='<strong>'+p.name+'</strong><small>'+p.type+' · '+Number(p.lat).toFixed(6)+', '+Number(p.lng).toFixed(6)+'</small><div class="button-row"><button class="sidebar-button secondary" data-focus="'+p.id+'">Ir</button><button class="sidebar-button danger" data-remove="'+p.id+'">Remover</button></div>'; list.appendChild(card); }); if(!points.length){ list.innerHTML='<small>Nenhum ponto rápido salvo.</small>'; } list.querySelectorAll('[data-focus]').forEach(function(btn){ btn.onclick=function(){ const p=getQuickPoints().find(x=>x.id===btn.dataset.focus); if(p){ map.setView([p.lat,p.lng],17); } }; }); list.querySelectorAll('[data-remove]').forEach(function(btn){ btn.onclick=function(){ saveQuickPoints(getQuickPoints().filter(x=>x.id!==btn.dataset.remove)); renderQuickPoints(); }; }); }
function fillPointFields(latlng){ document.getElementById('quickPointLat').value=latlng.lat.toFixed(7); document.getElementById('quickPointLng').value=latlng.lng.toFixed(7); }
document.getElementById('useMapCenterBtn').onclick=function(){ fillPointFields(map.getCenter()); };
document.getElementById('clearQuickPointFormBtn').onclick=function(){ ['quickPointName','quickPointNotes','quickPointLat','quickPointLng'].forEach(function(id){ document.getElementById(id).value=''; }); };
document.getElementById('enablePointClickBtn').onclick=function(){ clickToMark=!clickToMark; this.innerText=clickToMark?'Clique no mapa...':'Marcar clicando no mapa'; this.classList.toggle('secondary', !clickToMark); msg(clickToMark?'Clique no mapa para preencher latitude e longitude.':''); };
map.on('click', function(e){ if(clickToMark){ fillPointFields(e.latlng); clickToMark=false; document.getElementById('enablePointClickBtn').innerText='Marcar clicando no mapa'; msg('Coordenadas preenchidas. Informe o nome e salve o ponto.'); } });
document.getElementById('saveQuickPointBtn').onclick=function(){ const name=(document.getElementById('quickPointName').value||'').trim(); const lat=parseFloat(document.getElementById('quickPointLat').value); const lng=parseFloat(document.getElementById('quickPointLng').value); if(!name || isNaN(lat) || isNaN(lng)){ msg('Informe nome, latitude e longitude para salvar o ponto rápido.'); return; } const points=getQuickPoints(); points.push({id:String(Date.now()),name:name,type:document.getElementById('quickPointType').value,notes:document.getElementById('quickPointNotes').value,lat:lat,lng:lng}); saveQuickPoints(points); renderQuickPoints(); map.setView([lat,lng],17); msg('Ponto rápido salvo.'); };
document.getElementById('clearAllQuickPointsBtn').onclick=function(){ if(confirm('Apagar todos os pontos rápidos desta fazenda neste navegador?')){ saveQuickPoints([]); renderQuickPoints(); } };
renderQuickPoints();
</script>
</body>
</html>
