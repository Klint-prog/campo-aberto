@extends('layouts.app')

@section('title', 'Novo '.$config['singular'].' - Campo Aberto')
@section('page_title', 'Novo '.$config['singular'])
@section('breadcrumb', 'Cadastros mestres rurais / '.$config['title'])

@section('content')
    <section class="card">
        <h2 style="margin-top:0">Novo {{ $config['singular'] }}</h2>
        <form method="POST" action="{{ route($module.'.store') }}">
            @include('master-data._form')
        </form>
    </section>
@endsection
