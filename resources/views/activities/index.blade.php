@extends('layouts.app')

@section('title', 'Atividades agrícolas - Campo Aberto')
@section('page_title', 'Atividades agrícolas')
@section('breadcrumb', 'Agricultura operacional')

@section('content')
<section class="card">
    <div class="actions" style="justify-content:space-between; margin-bottom:18px">
        <div>
            <h2 style="margin:0">Atividades agrícolas</h2>
            <p class="muted" style="margin:4px 0 0">Planejamento, execução, cancelamento e consumo de insumos.</p>
        </div>
        <div class="actions">
            <a class="btn secondary" href="{{ route('activities.export', request()->query()) }}">Exportar CSV</a>
            <a class="btn" href="{{ route('activities.create') }}">Nova atividade</a>
        </div>
    </div>

    <form method="GET" class="card" style="box-shadow:none; margin-bottom:18px; padding:14px">
        <div class="form-grid" style="max-width:none; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); align-items:end">
            <div><label>Fazenda</label><select name="farm_id"><option value="">Todas</option>@foreach($farms as $id => $name)<option value="{{ $id }}" @selected(($filters['farm_id'] ?? '') === $id)>{{ $name }}</option>@endforeach</select></div>
            <div><label>Safra</label><select name="season_id"><option value="">Todas</option>@foreach($seasons as $id => $name)<option value="{{ $id }}" @selected(($filters['season_id'] ?? '') === $id)>{{ $name }}</option>@endforeach</select></div>
            <div><label>Cultura</label><select name="crop_id"><option value="">Todas</option>@foreach($crops as $id => $name)<option value="{{ $id }}" @selected(($filters['crop_id'] ?? '') === $id)>{{ $name }}</option>@endforeach</select></div>
            <div><label>Status</label><select name="status"><option value="">Todos</option>@foreach($statuses as $value => $label)<option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
            <div><label>Tipo</label><select name="type"><option value="">Todos</option>@foreach($types as $value => $label)<option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
            <div><label>De</label><input type="date" name="start_on" value="{{ $filters['start_on'] ?? '' }}"></div>
            <div><label>Até</label><input type="date" name="end_on" value="{{ $filters['end_on'] ?? '' }}"></div>
            <div class="actions"><button class="btn" type="submit">Filtrar</button><a class="btn secondary" href="{{ route('activities.index') }}">Limpar</a></div>
        </div>
    </form>

    @if ($activities->isEmpty())
        <div class="empty"><h3>Nenhuma atividade encontrada</h3><p>Planeje a primeira atividade agrícola para iniciar o fluxo operacional.</p></div>
    @else
        <div class="table-wrap">
            <table>
                <thead><tr><th>Título</th><th>Fazenda</th><th>Safra</th><th>Cultura</th><th>Tipo</th><th>Status</th><th>Período</th><th>Ações</th></tr></thead>
                <tbody>
                @foreach ($activities as $activity)
                    <tr>
                        <td><strong>{{ $activity->title }}</strong></td>
                        <td>{{ $activity->farm?->name ?? '—' }}</td>
                        <td>{{ $activity->season?->name ?? '—' }}</td>
                        <td>{{ $activity->crop?->name ?? '—' }}</td>
                        <td>{{ $types[$activity->type] ?? $activity->type }}</td>
                        <td><span class="badge">{{ $statuses[$activity->status] ?? $activity->status }}</span></td>
                        <td>{{ optional($activity->planned_start_on)->format('d/m/Y') ?? '—' }} até {{ optional($activity->planned_end_on)->format('d/m/Y') ?? '—' }}</td>
                        <td><div class="actions"><a class="btn secondary" href="{{ route('activities.show', $activity) }}">Ver</a><a class="btn secondary" href="{{ route('activities.edit', $activity) }}">Editar</a></div></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination">{{ $activities->links() }}</div>
    @endif
</section>
@endsection
