@extends('layouts.app')

@section('title', 'Mapa - Campo Aberto')
@section('page_title', 'Mapa operacional')
@section('breadcrumb', 'Gestão Rural / Mapa')

@section('content')
    <section class="card empty">
        <div style="font-size:42px">🗺️</div>
        <h2>Nenhuma fazenda cadastrada</h2>
        <p>Cadastre uma fazenda ou rode os seeders demo para visualizar o mapa operacional.</p>
        <a class="btn" href="{{ route('dashboard') }}">Voltar ao dashboard</a>
    </section>
@endsection
