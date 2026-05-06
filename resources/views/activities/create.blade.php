@extends('layouts.app')

@section('title', 'Nova atividade - Campo Aberto')
@section('page_title', 'Nova atividade agrícola')
@section('breadcrumb', 'Agricultura operacional')

@section('content')
<section class="card">
    <h2 style="margin-top:0">Planejar atividade</h2>
    <form method="POST" action="{{ route('activities.store') }}">
        @include('activities.partials.form')
    </form>
</section>
@endsection
