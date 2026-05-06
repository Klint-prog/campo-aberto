@extends('layouts.app')

@section('title', 'Colheita - Campo Aberto')
@section('page_title', 'Colheita')
@section('breadcrumb', 'Agricultura operacional')

@section('content')
<section class="card">
    <div class="actions" style="justify-content:space-between; margin-bottom:18px">
        <div><h2 style="margin:0">Colheita de {{ $harvest->crop?->name ?? 'cultura' }}</h2><p class="muted" style="margin:4px 0 0">{{ optional($harvest->harvested_on)->format('d/m/Y') }}</p></div>
        <div class="actions"><a class="btn secondary" href="{{ route('harvests.edit', $harvest) }}">Editar</a><a class="btn secondary" href="{{ route('harvests.index') }}">Voltar</a></div>
    </div>

    <div class="grid cards">
        <div class="card" style="box-shadow:none"><strong>Fazenda</strong><br>{{ $harvest->farm?->name ?? '—' }}</div>
        <div class="card" style="box-shadow:none"><strong>Safra</strong><br>{{ $harvest->season?->name ?? '—' }}</div>
        <div class="card" style="box-shadow:none"><strong>Talhão</strong><br>{{ $harvest->plot?->name ?? '—' }}</div>
        <div class="card" style="box-shadow:none"><strong>Área colhida</strong><br>{{ $harvest->harvested_area_ha }} ha</div>
        <div class="card" style="box-shadow:none"><strong>Peso total</strong><br>{{ $harvest->total_weight_kg }} kg</div>
        <div class="card" style="box-shadow:none"><strong>Produtividade</strong><br>{{ $harvest->productivity_kg_ha }} kg/ha</div>
    </div>

    <p style="margin-top:18px"><strong>Qualidade:</strong> {{ $harvest->quality_grade ?: 'Não informada' }}</p>
    <p><strong>Atividade relacionada:</strong> {{ $harvest->activity?->title ?? 'Não vinculada' }}</p>
</section>
@endsection
