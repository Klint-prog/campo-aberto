@extends('layouts.app')

@section('title', $config['singular'].' - Campo Aberto')
@section('page_title', $config['singular'])
@section('breadcrumb', 'Cadastros mestres rurais / '.$config['title'])

@section('content')
    <section class="card">
        <div class="actions" style="justify-content:space-between; margin-bottom:18px">
            <div>
                <h2 style="margin:0">{{ data_get($record, 'name') ?? data_get($record, 'code') ?? $config['singular'] }}</h2>
                <p class="muted" style="margin:4px 0 0">Detalhes do cadastro mestre.</p>
            </div>
            <div class="actions">
                <a class="btn secondary" href="{{ route($module.'.index') }}">Voltar</a>
                <a class="btn" href="{{ route($module.'.edit', $record) }}">Editar</a>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <tbody>
                @foreach ($columns as $column => $label)
                    <tr>
                        <th style="width:260px">{{ $label }}</th>
                        <td>
                            @if ($column === 'farm_id' && $record->relationLoaded('farm'))
                                {{ $record->farm?->name ?? '—' }}
                            @elseif ($column === 'crop_id' && $record->relationLoaded('crop'))
                                {{ $record->crop?->name ?? '—' }}
                            @elseif (is_bool(data_get($record, $column)))
                                {{ data_get($record, $column) ? 'Sim' : 'Não' }}
                            @else
                                {{ data_get($record, $column) ?? '—' }}
                            @endif
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <th>Criado em</th>
                    <td>{{ optional($record->created_at)->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <th>Atualizado em</th>
                    <td>{{ optional($record->updated_at)->format('d/m/Y H:i') }}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </section>
@endsection
