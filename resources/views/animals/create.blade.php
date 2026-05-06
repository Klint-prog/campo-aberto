@extends('layouts.app')

@section('title', 'Novo animal - Campo Aberto')
@section('page_title', 'Novo animal')
@section('breadcrumb', 'Pecuária operacional')

@section('content')
<section class="card"><h2 style="margin-top:0">Cadastrar animal</h2><form method="POST" action="{{ route('animals.store') }}">@include('animals.partials.form')</form></section>
@endsection
