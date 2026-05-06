@extends('layouts.app')

@section('title', $activity->title.' - Campo Aberto')
@section('page_title', 'Atividade agrícola')
@section('breadcrumb', 'Agricultura operacional')

@section('content')
<section class="card">
    <div class="actions" style="justify-content:space-between; margin-bottom:18px">
        <div>
            <h2 style="margin:0">{{ $activity->title }}</h2>
            <p class="muted" style="margin:4px 0 0">{{ $types[$activity->type] ?? $activity->type }} · <span class="badge">{{ $statuses[$activity->status] ?? $activity->status }}</span></p>
        </div>
        <div class="actions">
            <a class="btn secondary" href="{{ route('activities.edit', $activity) }}">Editar</a>
            <a class="btn secondary" href="{{ route('activities.index') }}">Voltar</a>
        </div>
    </div>

    <div class="grid cards" style="margin-bottom:18px">
        <div class="card" style="box-shadow:none"><strong>Fazenda</strong><br>{{ $activity->farm?->name ?? '—' }}</div>
        <div class="card" style="box-shadow:none"><strong>Safra</strong><br>{{ $activity->season?->name ?? '—' }}</div>
        <div class="card" style="box-shadow:none"><strong>Cultura</strong><br>{{ $activity->crop?->name ?? '—' }}</div>
        <div class="card" style="box-shadow:none"><strong>Área</strong><br>Planejada: {{ $activity->planned_area_ha ?? '—' }} ha<br>Executada: {{ $activity->actual_area_ha ?? '—' }} ha</div>
    </div>

    <p>{{ $activity->description ?: 'Sem descrição operacional.' }}</p>

    @if ($activity->status === \App\Models\Activity::STATUS_PLANNED)
        <div class="grid cards" style="margin-top:18px">
            <form class="card" method="POST" action="{{ route('activities.complete', $activity) }}" style="box-shadow:none">
                @csrf
                <h3 style="margin-top:0">Concluir atividade</h3>
                <label>Data/hora de conclusão</label><input type="datetime-local" name="completed_at">
                <label style="margin-top:10px">Área executada (ha)</label><input type="number" step="0.0001" name="actual_area_ha" value="{{ $activity->actual_area_ha }}">
                <button class="btn" type="submit" style="margin-top:12px">Concluir</button>
            </form>

            <form class="card" method="POST" action="{{ route('activities.cancel', $activity) }}" style="box-shadow:none">
                @csrf
                <h3 style="margin-top:0">Cancelar atividade</h3>
                <label>Motivo</label><input name="reason" placeholder="Opcional">
                <button class="btn danger" type="submit" style="margin-top:12px">Cancelar atividade</button>
            </form>
        </div>
    @endif
</section>

@include('activities.partials.inputs')
@endsection
