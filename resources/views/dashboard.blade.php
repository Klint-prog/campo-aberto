@extends('layouts.app')

@section('title', 'Dashboard - Campo Aberto')
@section('page_title', 'Dashboard')
@section('breadcrumb', 'Visão geral operacional')

@section('content')
    <div class="grid cards">
        @foreach ($cards as $card)
            <a class="card" href="{{ $card['available'] && Route::has($card['route']) ? route($card['route']) : route('under-construction', str($card['title'])->slug()->toString()) }}">
                <div style="font-size:28px">{{ $card['icon'] }}</div>
                <h2 style="margin:12px 0 4px">{{ $card['title'] }}</h2>
                @if ($card['available'])
                    <strong style="font-size:30px">{{ $card['count'] }}</strong>
                    <p class="muted">registros no tenant atual</p>
                @else
                    <span class="badge">Em desenvolvimento</span>
                    <p class="muted">Backend/tabela ainda indisponível.</p>
                @endif
            </a>
        @endforeach
    </div>

    <div class="grid" style="grid-template-columns: 1.3fr .7fr; margin-top:22px">
        <section class="card">
            <h2>Atalhos rápidos</h2>
            <p class="muted">Acesse as áreas principais já expostas na interface administrativa.</p>
            <div class="actions">
                @foreach ($quickLinks as $link)
                    @if (Route::has($link['route']))
                        <a class="btn secondary" href="{{ route($link['route']) }}">{{ $link['label'] }}</a>
                    @endif
                @endforeach
            </div>
        </section>

        <section class="card">
            <h2>Status do MVP</h2>
            <p>A plataforma já possui navegação autenticada, cards por módulo e fallback controlado para itens do roadmap.</p>
            <span class="badge">Fase 11</span>
        </section>
    </div>
@endsection
