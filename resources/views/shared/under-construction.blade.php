@extends('layouts.app')

@section('title', $title.' - Campo Aberto')
@section('page_title', $title)
@section('breadcrumb', 'Roadmap / Em desenvolvimento')

@section('content')
    <section class="card empty">
        <div style="font-size:46px">🚧</div>
        <h2>{{ $title ?: 'Módulo em desenvolvimento' }}</h2>
        <p>Este recurso está previsto no roadmap do Campo Aberto, mas ainda não foi liberado como funcionalidade operacional nesta fase.</p>
        <p class="muted">A Fase 11 apenas organiza a navegação e evita telas quebradas para módulos futuros.</p>
        <a class="btn" href="{{ route('dashboard') }}">Voltar ao dashboard</a>
    </section>
@endsection
