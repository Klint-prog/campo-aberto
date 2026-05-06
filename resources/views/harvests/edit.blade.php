@extends('layouts.app')

@section('title', 'Editar colheita - Campo Aberto')
@section('page_title', 'Editar colheita')
@section('breadcrumb', 'Agricultura operacional')

@section('content')
<section class="card">
    <h2 style="margin-top:0">Editar colheita</h2>
    <form method="POST" action="{{ route('harvests.update', $harvest) }}">
        @method('PUT')
        @include('harvests.partials.form')
    </form>
</section>
@endsection
