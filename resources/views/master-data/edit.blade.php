@extends('layouts.app')

@section('title', 'Editar '.$config['singular'].' - Campo Aberto')
@section('page_title', 'Editar '.$config['singular'])
@section('breadcrumb', 'Cadastros mestres rurais / '.$config['title'])

@section('content')
    <section class="card">
        <h2 style="margin-top:0">Editar {{ $config['singular'] }}</h2>
        <form method="POST" action="{{ route($module.'.update', $record) }}">
            @method('PUT')
            @include('master-data._form')
        </form>
    </section>
@endsection
