@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Relatório de Sanidade Animal</h2>
    @include('reports.partials.filters')
    @include('reports.partials.totalizers')
</div>
@endsection