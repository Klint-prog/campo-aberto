@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Relatório de Fazendas</h2>
    @include('reports.partials.filters')
    @include('reports.partials.totalizers')
</div>
@endsection