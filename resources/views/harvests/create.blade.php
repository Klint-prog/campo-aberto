@extends('layouts.app')

@section('title', 'Nova colheita - Campo Aberto')
@section('page_title', 'Nova colheita')
@section('breadcrumb', 'Agricultura operacional')

@section('content')
<section class="card">
    <h2 style="margin-top:0">Registrar colheita</h2>
    <form method="POST" action="{{ route('harvests.store') }}">
        @include('harvests.partials.form')
    </form>
</section>
@endsection
