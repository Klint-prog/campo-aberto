@extends('layouts.app')

@section('title', $config['singular'].' - Campo Aberto')
@section('page_title', $config['singular'])
@section('breadcrumb', 'Cadastros mestres rurais / '.$config['title'])

@section('content')
    <section class="card">
        <div class="actions" style="justify-content:space-between; margin-bottom:18px">
            <div>
                <h2 style="margin:0">{{ data_get($record, 'name') ?? data_get($record, 'code') ?? $config['singular'] }}</h2>
                <p class="muted" style="margin:4px 0 0">Detalhes do cadastro operacional com escopo por tenant e fazenda.</p>
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

    @if ($module === 'inventory')
        <section class="card" style="margin-top:18px">
            <h2 style="margin-top:0">Registrar movimentação de estoque</h2>
            <form method="POST" action="{{ route('inventory.stock-movements.store', $record) }}">
                @csrf
                <div class="form-grid">
                    <div>
                        <label for="direction">Movimento</label>
                        <select id="direction" name="direction" required>
                            <option value="in">Entrada</option>
                            <option value="out">Saída</option>
                            <option value="adjustment">Ajuste de saldo</option>
                        </select>
                        @error('direction') <div class="errors">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label for="quantity">Quantidade</label>
                        <input id="quantity" name="quantity" type="number" step="0.0001" min="0.0001" required value="{{ old('quantity') }}">
                        @error('quantity') <div class="errors">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label for="unit_cost">Custo unitário</label>
                        <input id="unit_cost" name="unit_cost" type="number" step="0.0001" min="0" value="{{ old('unit_cost', $record->unit_cost) }}">
                    </div>
                    <div>
                        <label for="moved_on">Data</label>
                        <input id="moved_on" name="moved_on" type="date" required value="{{ old('moved_on', now()->toDateString()) }}">
                    </div>
                    <div style="grid-column:1 / -1">
                        <label for="reason">Motivo</label>
                        <input id="reason" name="reason" required value="{{ old('reason') }}" placeholder="Compra, consumo, ajuste de inventário...">
                    </div>
                    <div class="actions">
                        <button class="btn" type="submit">Registrar movimentação</button>
                        <a class="btn secondary" href="{{ route('stock-movements.index', ['farm_id' => $record->farm_id]) }}">Ver movimentações</a>
                    </div>
                </div>
            </form>
        </section>
    @endif

    @if ($module === 'machines')
        <section class="card" style="margin-top:18px">
            <h2 style="margin-top:0">Registrar manutenção</h2>
            <form method="POST" action="{{ route('machines.maintenance-records.store', $record) }}">
                @csrf
                <div class="form-grid">
                    <div>
                        <label for="type">Tipo</label>
                        <input id="type" name="type" required value="{{ old('type') }}" placeholder="Preventiva, corretiva...">
                    </div>
                    <div>
                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            <option value="scheduled">Agendada</option>
                            <option value="performed">Realizada</option>
                            <option value="cancelled">Cancelada</option>
                        </select>
                    </div>
                    <div>
                        <label for="scheduled_on">Agendada em</label>
                        <input id="scheduled_on" name="scheduled_on" type="date" value="{{ old('scheduled_on') }}">
                    </div>
                    <div>
                        <label for="performed_on">Realizada em</label>
                        <input id="performed_on" name="performed_on" type="date" value="{{ old('performed_on') }}">
                    </div>
                    <div>
                        <label for="cost">Custo</label>
                        <input id="cost" name="cost" type="number" step="0.01" min="0" value="{{ old('cost') }}">
                    </div>
                    <div>
                        <label for="supplier">Fornecedor</label>
                        <input id="supplier" name="supplier" value="{{ old('supplier') }}">
                    </div>
                    <div style="grid-column:1 / -1">
                        <label for="description">Descrição</label>
                        <textarea id="description" name="description" rows="3" required style="width:100%; border:1px solid var(--line); border-radius:12px; padding:11px 12px; font:inherit">{{ old('description') }}</textarea>
                    </div>
                    <div class="actions">
                        <button class="btn" type="submit">Registrar manutenção</button>
                        <a class="btn secondary" href="{{ route('maintenance.index', ['machine_id' => $record->id]) }}">Ver manutenções</a>
                    </div>
                </div>
            </form>
        </section>
    @endif
@endsection
