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

@endsection
