@extends('layouts.app')

@section('title', 'Editar animal - Campo Aberto')
@section('page_title', 'Editar animal')
@section('breadcrumb', 'Pecuária operacional')

@section('content')
<section class="card"><h2 style="margin-top:0">Editar {{ $animal->internal_code }}</h2><form method="POST" action="{{ route('animals.update', $animal) }}">@method('PUT')@include('animals.partials.form')</form></section>
@endsection
