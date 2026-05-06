@extends('layouts.app')

@section('title', 'Animais - Campo Aberto')
@section('page_title', 'Animais')
@section('breadcrumb', 'Pecuária operacional')

@section('content')
<section class="card">
    <div class="actions" style="justify-content:space-between; margin-bottom:18px">
        <div><h2 style="margin:0">Animais</h2><p class="muted" style="margin:4px 0 0">Cadastro, compra, venda, mortalidade e registros operacionais.</p></div>
        <div class="actions"><a class="btn secondary" href="{{ route('animals.export', request()->query()) }}">Exportar CSV</a><a class="btn" href="{{ route('animals.create') }}">Novo animal</a></div>
    </div>

    <form method="GET" class="card" style="box-shadow:none; margin-bottom:18px; padding:14px">
        <div class="form-grid" style="max-width:none; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); align-items:end">
            <div><label>Fazenda</label><select name="farm_id"><option value="">Todas</option>@foreach($farms as $id => $name)<option value="{{ $id }}" @selected(($filters['farm_id'] ?? '') === $id)>{{ $name }}</option>@endforeach</select></div>
            <div><label>Lote</label><select name="animal_lot_id"><option value="">Todos</option>@foreach($lots as $id => $name)<option value="{{ $id }}" @selected(($filters['animal_lot_id'] ?? '') === $id)>{{ $name }}</option>@endforeach</select></div>
            <div><label>Espécie</label><select name="species"><option value="">Todas</option>@foreach($species as $value => $label)<option value="{{ $value }}" @selected(($filters['species'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
            <div><label>Status</label><select name="status"><option value="">Todos</option>@foreach($statuses as $value => $label)<option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
            <div><label>De</label><input type="date" name="start_on" value="{{ $filters['start_on'] ?? '' }}"></div>
            <div><label>Até</label><input type="date" name="end_on" value="{{ $filters['end_on'] ?? '' }}"></div>
            <div class="actions"><button class="btn" type="submit">Filtrar</button><a class="btn secondary" href="{{ route('animals.index') }}">Limpar</a></div>
        </div>
    </form>

    @if ($animals->isEmpty())
        <div class="empty"><h3>Nenhum animal encontrado</h3><p>Cadastre o primeiro animal para iniciar os fluxos pecuários.</p></div>
    @else
        <div class="table-wrap"><table><thead><tr><th>Animal</th><th>Fazenda</th><th>Lote</th><th>Espécie</th><th>Sexo</th><th>Status</th><th>Aquisição</th><th>Ações</th></tr></thead><tbody>
        @foreach ($animals as $animal)
            <tr><td><strong>{{ $animal->internal_code }}</strong><br><span class="muted">{{ $animal->ear_tag ?? $animal->name ?? 'Sem identificação extra' }}</span></td><td>{{ $animal->farm?->name ?? '—' }}</td><td>{{ $animal->lot?->name ?? '—' }}</td><td>{{ $species[$animal->species] ?? $animal->species }}</td><td>{{ $sexes[$animal->sex] ?? $animal->sex }}</td><td><span class="badge">{{ $statuses[$animal->status] ?? $animal->status }}</span></td><td>{{ optional($animal->acquired_on)->format('d/m/Y') ?? '—' }}</td><td><div class="actions"><a class="btn secondary" href="{{ route('animals.show', $animal) }}">Ver</a><a class="btn secondary" href="{{ route('animals.edit', $animal) }}">Editar</a></div></td></tr>
        @endforeach
        </tbody></table></div><div class="pagination">{{ $animals->links() }}</div>
    @endif
</section>
@endsection
