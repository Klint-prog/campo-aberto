@extends('layouts.app')

@section('title', 'Colheitas - Campo Aberto')
@section('page_title', 'Colheitas')
@section('breadcrumb', 'Agricultura operacional')

@section('content')
<section class="card">
    <div class="actions" style="justify-content:space-between; margin-bottom:18px">
        <div><h2 style="margin:0">Colheitas</h2><p class="muted" style="margin:4px 0 0">Registro de produção e produtividade por hectare.</p></div>
        <div class="actions"><a class="btn secondary" href="{{ route('harvests.export', request()->query()) }}">Exportar CSV</a><a class="btn" href="{{ route('harvests.create') }}">Nova colheita</a></div>
    </div>

    <form method="GET" class="card" style="box-shadow:none; margin-bottom:18px; padding:14px">
        <div class="form-grid" style="max-width:none; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); align-items:end">
            <div><label>Fazenda</label><select name="farm_id"><option value="">Todas</option>@foreach($farms as $id => $name)<option value="{{ $id }}" @selected(($filters['farm_id'] ?? '') === $id)>{{ $name }}</option>@endforeach</select></div>
            <div><label>Safra</label><select name="season_id"><option value="">Todas</option>@foreach($seasons as $id => $name)<option value="{{ $id }}" @selected(($filters['season_id'] ?? '') === $id)>{{ $name }}</option>@endforeach</select></div>
            <div><label>Cultura</label><select name="crop_id"><option value="">Todas</option>@foreach($crops as $id => $name)<option value="{{ $id }}" @selected(($filters['crop_id'] ?? '') === $id)>{{ $name }}</option>@endforeach</select></div>
            <div><label>De</label><input type="date" name="start_on" value="{{ $filters['start_on'] ?? '' }}"></div>
            <div><label>Até</label><input type="date" name="end_on" value="{{ $filters['end_on'] ?? '' }}"></div>
            <div class="actions"><button class="btn" type="submit">Filtrar</button><a class="btn secondary" href="{{ route('harvests.index') }}">Limpar</a></div>
        </div>
    </form>

    @if ($harvests->isEmpty())
        <div class="empty"><h3>Nenhuma colheita encontrada</h3><p>Registre colheitas para calcular produtividade por hectare.</p></div>
    @else
        <div class="table-wrap">
            <table>
                <thead><tr><th>Data</th><th>Fazenda</th><th>Safra</th><th>Cultura</th><th>Talhão</th><th>Área ha</th><th>Peso kg</th><th>Produtividade kg/ha</th><th>Ações</th></tr></thead>
                <tbody>
                @foreach ($harvests as $harvest)
                    <tr>
                        <td>{{ optional($harvest->harvested_on)->format('d/m/Y') }}</td>
                        <td>{{ $harvest->farm?->name ?? '—' }}</td>
                        <td>{{ $harvest->season?->name ?? '—' }}</td>
                        <td>{{ $harvest->crop?->name ?? '—' }}</td>
                        <td>{{ $harvest->plot?->name ?? '—' }}</td>
                        <td>{{ $harvest->harvested_area_ha }}</td>
                        <td>{{ $harvest->total_weight_kg }}</td>
                        <td><strong>{{ $harvest->productivity_kg_ha }}</strong></td>
                        <td><div class="actions"><a class="btn secondary" href="{{ route('harvests.show', $harvest) }}">Ver</a><a class="btn secondary" href="{{ route('harvests.edit', $harvest) }}">Editar</a></div></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination">{{ $harvests->links() }}</div>
    @endif
</section>
@endsection
