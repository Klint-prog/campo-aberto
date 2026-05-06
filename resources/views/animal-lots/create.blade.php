@extends('layouts.app')

@section('title', 'Novo lote de animais - Campo Aberto')
@section('page_title', 'Novo lote de animais')
@section('breadcrumb', 'Pecuária operacional')

@section('content')
<section class="card"><h2 style="margin-top:0">Cadastrar lote</h2><form method="POST" action="{{ route('animal-lots.store') }}">@include('animal-lots.partials.form')</form></section>
@endsection
