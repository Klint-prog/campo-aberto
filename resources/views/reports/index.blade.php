@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Central de Relatórios Operacionais</h1>
    <ul>
        @foreach($reports as $report)
            <li><a href="{{ route('reports.' . $report) }}">{{ ucfirst($report) }}</a></li>
        @endforeach
    </ul>
</div>
@endsection