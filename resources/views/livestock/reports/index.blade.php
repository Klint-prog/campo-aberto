@extends('layouts.app')

@section('title', 'Relatórios pecuários - Campo Aberto')
@section('page_title', 'Relatórios pecuários')
@section('breadcrumb', 'Pecuária operacional')

@section('content')
<div class="grid">
    <section class="card">
        <div class="actions" style="justify-content:space-between"><div><h2 style="margin:0">Relatórios pecuários básicos</h2><p class="muted" style="margin:4px 0 0">Resumo operacional de animais, sanidade, vacinação, pesagens e alimentação.</p></div><div class="actions"><a class="btn secondary" href="{{ route('animal-weights.export') }}">Exportar pesagens</a><a class="btn secondary" href="{{ route('animal-health.export') }}">Exportar sanidade</a></div></div>
    </section>

    <section class="grid cards">
        <article class="card"><span class="muted">Ativos</span><h2>{{ $statusTotals['active'] ?? 0 }}</h2></article>
        <article class="card"><span class="muted">Vendidos</span><h2>{{ $statusTotals['sold'] ?? 0 }}</h2></article>
        <article class="card"><span class="muted">Mortos</span><h2>{{ $statusTotals['dead'] ?? 0 }}</h2></article>
        <article class="card"><span class="muted">Pesagens</span><h2>{{ $weightsCount }}</h2></article>
        <article class="card"><span class="muted">Vacinas aplicadas</span><h2>{{ $vaccinationsApplied }}</h2></article>
        <article class="card"><span class="muted">Vacinas pendentes</span><h2>{{ $vaccinationsPending }}</h2></article>
        <article class="card"><span class="muted">Registros sanitários</span><h2>{{ $healthCount }}</h2></article>
        <article class="card"><span class="muted">Custo alimentação</span><h2>{{ number_format((float) $feedCost, 2, ',', '.') }}</h2></article>
    </section>

    <section class="card"><h3 style="margin-top:0">Animais por espécie</h3>@if($speciesTotals->isEmpty())<p class="muted">Sem animais cadastrados.</p>@else<div class="table-wrap"><table><thead><tr><th>Espécie</th><th>Total</th></tr></thead><tbody>@foreach($speciesTotals as $species => $total)<tr><td>{{ $species }}</td><td>{{ $total }}</td></tr>@endforeach</tbody></table></div>@endif</section>

    <section class="card"><h3 style="margin-top:0">Consumo de alimentação</h3><p>Total registrado: <strong>{{ number_format((float) $feedTotal, 4, ',', '.') }}</strong> unidades agregadas. Para análise por unidade, use os exports e filtros da listagem operacional.</p></section>
</div>
@endsection
