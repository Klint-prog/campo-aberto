@extends('layouts.app')

@section('title', 'Relatórios agrícolas - Campo Aberto')
@section('page_title', 'Relatórios agrícolas')
@section('breadcrumb', 'Agricultura operacional')

@section('content')
<section class="card">
    <div class="actions" style="justify-content:space-between; margin-bottom:18px">
        <div><h2 style="margin:0">Relatórios agrícolas básicos</h2><p class="muted" style="margin:4px 0 0">Atividades, atrasos, insumos consumidos, colheitas e produtividade.</p></div>
    </div>

    <form method="GET" class="card" style="box-shadow:none; margin-bottom:18px; padding:14px">
        <div class="form-grid" style="max-width:none; grid-template-columns:repeat(auto-fit, minmax(170px, 1fr)); align-items:end">
            <div><label>Fazenda</label><select name="farm_id"><option value="">Todas</option>@foreach($farms as $id => $name)<option value="{{ $id }}" @selected(($filters['farm_id'] ?? '') === $id)>{{ $name }}</option>@endforeach</select></div>
            <div><label>Safra</label><select name="season_id"><option value="">Todas</option>@foreach($seasons as $id => $name)<option value="{{ $id }}" @selected(($filters['season_id'] ?? '') === $id)>{{ $name }}</option>@endforeach</select></div>
            <div><label>Cultura</label><select name="crop_id"><option value="">Todas</option>@foreach($crops as $id => $name)<option value="{{ $id }}" @selected(($filters['crop_id'] ?? '') === $id)>{{ $name }}</option>@endforeach</select></div>
            <div><label>Status</label><select name="status"><option value="">Todos</option>@foreach($statuses as $value => $label)<option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="actions"><button class="btn" type="submit">Filtrar</button><a class="btn secondary" href="{{ route('reports.agriculture') }}">Limpar</a></div>
        </div>
    </form>

    <div class="grid cards" style="margin-bottom:18px">
        <div class="card" style="box-shadow:none"><strong>Atividades</strong><br>{{ $activityTotals['total'] }} totais<br>{{ $activityTotals['planned'] }} planejadas · {{ $activityTotals['completed'] }} concluídas</div>
        <div class="card" style="box-shadow:none"><strong>Atividades atrasadas</strong><br>{{ $activityTotals['late'] }}</div>
        <div class="card" style="box-shadow:none"><strong>Insumos consumidos</strong><br>{{ $inputTotals['count'] }} registros<br>Custo total: {{ number_format($inputTotals['cost'], 2, ',', '.') }}</div>
        <div class="card" style="box-shadow:none"><strong>Colheitas</strong><br>{{ $harvestTotals['count'] }} registros<br>{{ number_format((float) $harvestTotals['area'], 4, ',', '.') }} ha</div>
        <div class="card" style="box-shadow:none"><strong>Produtividade média</strong><br>@php($avg = (float) $harvestTotals['area'] > 0 ? (float) $harvestTotals['weight'] / (float) $harvestTotals['area'] : 0){{ number_format($avg, 4, ',', '.') }} kg/ha</div>
    </div>

    <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); align-items:start">
        <section class="card" style="box-shadow:none">
            <h3 style="margin-top:0">Insumos consumidos por atividade</h3>
            @if ($recentInputs->isEmpty())
                <p class="muted">Nenhum consumo encontrado.</p>
            @else
                <div class="table-wrap"><table><thead><tr><th>Atividade</th><th>Insumo</th><th>Quantidade</th><th>Total</th></tr></thead><tbody>@foreach($recentInputs as $input)<tr><td>{{ $input->activity?->title ?? '—' }}</td><td>{{ $input->input_name }}</td><td>{{ $input->quantity }} {{ $input->unit }}</td><td>{{ $input->total_cost ?? '—' }}</td></tr>@endforeach</tbody></table></div>
            @endif
        </section>

        <section class="card" style="box-shadow:none">
            <h3 style="margin-top:0">Colheitas por safra/cultura/talhão</h3>
            @if ($recentHarvests->isEmpty())
                <p class="muted">Nenhuma colheita encontrada.</p>
            @else
                <div class="table-wrap"><table><thead><tr><th>Data</th><th>Cultura</th><th>Talhão</th><th>Produtividade</th></tr></thead><tbody>@foreach($recentHarvests as $harvest)<tr><td>{{ optional($harvest->harvested_on)->format('d/m/Y') }}</td><td>{{ $harvest->crop?->name ?? '—' }}</td><td>{{ $harvest->plot?->name ?? '—' }}</td><td>{{ $harvest->productivity_kg_ha }} kg/ha</td></tr>@endforeach</tbody></table></div>
            @endif
        </section>
    </div>
</section>
@endsection
