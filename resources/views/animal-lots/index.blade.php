@extends('layouts.app')

@section('title', 'Lotes de animais - Campo Aberto')
@section('page_title', 'Lotes de animais')
@section('breadcrumb', 'Pecuária operacional')

@section('content')
<section class="card">
    <div class="actions" style="justify-content:space-between; margin-bottom:18px">
        <div><h2 style="margin:0">Lotes de animais</h2><p class="muted" style="margin:4px 0 0">Gestão operacional de lotes, fazendas, pastagens e espécies.</p></div>
        <div class="actions"><a class="btn secondary" href="{{ route('animal-lots.export', request()->query()) }}">Exportar CSV</a><a class="btn" href="{{ route('animal-lots.create') }}">Novo lote</a></div>
    </div>

    <form method="GET" class="card" style="box-shadow:none; margin-bottom:18px; padding:14px">
        <div class="form-grid" style="max-width:none; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); align-items:end">
            <div><label>Fazenda</label><select name="farm_id"><option value="">Todas</option>@foreach($farms as $id => $name)<option value="{{ $id }}" @selected(($filters['farm_id'] ?? '') === $id)>{{ $name }}</option>@endforeach</select></div>
            <div><label>Espécie</label><select name="species"><option value="">Todas</option>@foreach($species as $value => $label)<option value="{{ $value }}" @selected(($filters['species'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
            <div><label>Status</label><select name="status"><option value="">Todos</option>@foreach($statuses as $value => $label)<option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
            <div><label>De</label><input type="date" name="start_on" value="{{ $filters['start_on'] ?? '' }}"></div>
            <div><label>Até</label><input type="date" name="end_on" value="{{ $filters['end_on'] ?? '' }}"></div>
            <div class="actions"><button class="btn" type="submit">Filtrar</button><a class="btn secondary" href="{{ route('animal-lots.index') }}">Limpar</a></div>
        </div>
    </form>

    @if ($lots->isEmpty())
        <div class="empty"><h3>Nenhum lote encontrado</h3><p>Crie o primeiro lote para organizar o rebanho.</p></div>
    @else
        <div class="table-wrap"><table><thead><tr><th>Nome</th><th>Fazenda</th><th>Pastagem</th><th>Espécie</th><th>Status</th><th>Animais</th><th>Período</th><th>Ações</th></tr></thead><tbody>
        @foreach ($lots as $lot)
            <tr><td><strong>{{ $lot->name }}</strong><br><span class="muted">{{ $lot->code ?? 'Sem código' }}</span></td><td>{{ $lot->farm?->name ?? '—' }}</td><td>{{ $lot->pasture?->name ?? '—' }}</td><td>{{ $species[$lot->species] ?? $lot->species }}</td><td><span class="badge">{{ $statuses[$lot->status] ?? $lot->status }}</span></td><td>{{ $lot->animals_count }}</td><td>{{ optional($lot->started_on)->format('d/m/Y') ?? '—' }} até {{ optional($lot->closed_on)->format('d/m/Y') ?? '—' }}</td><td><div class="actions"><a class="btn secondary" href="{{ route('animal-lots.show', $lot) }}">Ver</a><a class="btn secondary" href="{{ route('animal-lots.edit', $lot) }}">Editar</a></div></td></tr>
        @endforeach
        </tbody></table></div><div class="pagination">{{ $lots->links() }}</div>
    @endif
</section>
@endsection
