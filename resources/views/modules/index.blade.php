@extends('layouts.app')

@section('title', $title.' - Campo Aberto')
@section('page_title', $title)
@section('breadcrumb', 'Módulos operacionais')

@section('content')
    <section class="card">
        <div class="actions" style="justify-content:space-between; margin-bottom:18px">
            <div>
                <h2 style="margin:0">{{ $title }}</h2>
                <p class="muted" style="margin:4px 0 0">Listagem baseada no backend já existente: <code>{{ $table }}</code>.</p>
            </div>
            <span class="badge">{{ $records->total() }} registros</span>
        </div>

        @if ($records->isEmpty())
            <div class="empty">
                <h3>Nenhum registro encontrado</h3>
                <p>Cadastre dados demo ou use os endpoints internos já existentes para popular este módulo.</p>
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        @foreach ($columns as $label)
                            <th>{{ $label }}</th>
                        @endforeach
                        <th>Atualizado em</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($records as $record)
                        <tr>
                            @foreach ($columns as $column => $label)
                                <td>{{ data_get($record, $column) === true ? 'Sim' : (data_get($record, $column) === false ? 'Não' : (data_get($record, $column) ?? '—')) }}</td>
                            @endforeach
                            <td>{{ $record->updated_at ?? $record->created_at ?? '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination">{{ $records->links() }}</div>
        @endif
    </section>
@endsection
