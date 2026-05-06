@extends('layouts.app')

@section('title', 'Editar atividade - Campo Aberto')
@section('page_title', 'Editar atividade agrícola')
@section('breadcrumb', 'Agricultura operacional')

@section('content')
<section class="card">
    <h2 style="margin-top:0">Editar atividade</h2>
    <form method="POST" action="{{ route('activities.update', $activity) }}">
        @method('PUT')
        @include('activities.partials.form')
    </form>
</section>
@endsection
