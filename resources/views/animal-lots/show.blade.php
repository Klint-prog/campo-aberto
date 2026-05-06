@extends('layouts.app')

@section('title', 'Lote de animais - Campo Aberto')
@section('page_title', $animalLot->name)
@section('breadcrumb', 'Pecuária operacional')

@section('content')
<div class="grid">
    <section class="card">
        <div class="actions" style="justify-content:space-between"><div><h2 style="margin:0">{{ $animalLot->name }}</h2><p class="muted">{{ $animalLot->farm?->name }} · {{ $species[$animalLot->species] ?? $animalLot->species }} · {{ $statuses[$animalLot->status] ?? $animalLot->status }}</p></div><a class="btn secondary" href="{{ route('animal-lots.edit', $animalLot) }}">Editar lote</a></div>
        <p><strong>Código:</strong> {{ $animalLot->code ?? '—' }} · <strong>Pastagem:</strong> {{ $animalLot->pasture?->name ?? '—' }} · <strong>Finalidade:</strong> {{ $animalLot->purpose ?? '—' }}</p>
        <p><strong>Período:</strong> {{ optional($animalLot->started_on)->format('d/m/Y') ?? '—' }} até {{ optional($animalLot->closed_on)->format('d/m/Y') ?? '—' }}</p>
    </section>

    <section class="card"><h3 style="margin-top:0">Animais do lote</h3>@if($animalLot->animals->isEmpty())<p class="muted">Nenhum animal vinculado.</p>@else<div class="table-wrap"><table><thead><tr><th>Código</th><th>Brinco</th><th>Espécie</th><th>Status</th><th>Ação</th></tr></thead><tbody>@foreach($animalLot->animals as $animal)<tr><td>{{ $animal->internal_code }}</td><td>{{ $animal->ear_tag ?? '—' }}</td><td>{{ $animal->species }}</td><td><span class="badge">{{ $animal->status }}</span></td><td><a class="btn secondary" href="{{ route('animals.show', $animal) }}">Ver</a></td></tr>@endforeach</tbody></table></div>@endif</section>
</div>
@endsection
