@extends('layouts.app')

@section('title', 'Editar lote de animais - Campo Aberto')
@section('page_title', 'Editar lote de animais')
@section('breadcrumb', 'Pecuária operacional')

@section('content')
<section class="card"><h2 style="margin-top:0">Editar {{ $animalLot->name }}</h2><form method="POST" action="{{ route('animal-lots.update', $animalLot) }}">@method('PUT')@include('animal-lots.partials.form')</form></section>
@endsection
