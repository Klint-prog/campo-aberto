@extends('layouts.app')

@section('title', 'Movimentações de estoque - Campo Aberto')
@section('page_title', 'Movimentações de estoque')
@section('breadcrumb', 'Estoque / Movimentações')

@section('content')
    <section class="card">
        <div class="actions" style="justify-content:space-between; margin-bottom:18px">
            <div>
                <h2 style="margin:0">Movimentações de estoque</h2>
                <p class="muted" style="margin:4px 0 0">Entradas, saídas e ajustes com filtros por fazenda, direção e período.</p>
            </div>
            <div class="actions">
                <a class="btn secondary" href="{{ route('stock-movements.export', request()->query()) }}">Exportar CSV</a>
                <a class="btn" href="{{ route('inventory.index') }}">Itens de estoque</a>
            </div>
        </div>

        <form method="GET" class="card" style="box-shadow:none; margin-bottom:18px; padding:14px">
            <div class="form-grid" style="max-width:none; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); align-items:end">
                <div>
                    <label for="direction">Direção</label>
                    <select id="direction" name="direction">
                        <option value="">Todas</option>
                        <option value="in" @selected(($filters['direction'] ?? '') === 'in')>Entrada</option>
                        <option value="out" @selected(($filters['direction'] ?? '') === 'out')>Saída</option>
                    </select>
                </div>
                <div>
                    <label for="from">De</label>
                    <input id="from" name="from" type="date" value="{{ $filters['from'] ?? '' }}">
                </div>
                <div>
                    <label for="to">Até</label>
                    <input id="to" name="to" type="date" value="{{ $filters['to'] ?? '' }}">
                </div>
                <div class="actions">
                    <button class="btn" type="submit">Filtrar</button>
                    <a class="btn secondary" href="{{ route('stock-movements.index') }}">Limpar</a>
                </div>
            </div>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Data</th>
                    <th>Item</th>
                    <th>Fazenda</th>
                    <th>Direção</th>
                    <th>Motivo</th>
                    <th>Quantidade</th>
                    <th>Custo total</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($movements as $movement)
                    <tr>
                        <td>{{ optional($movement->moved_on)->format('d/m/Y') }}</td>
                        <td>{{ $movement->item?->name ?? '—' }}</td>
                        <td>{{ $movement->farm?->name ?? '—' }}</td>
                        <td>{{ $movement->direction === 'out' ? 'Saída' : 'Entrada' }}</td>
                        <td>{{ $movement->reason }}</td>
                        <td>{{ $movement->quantity }} {{ $movement->unit }}</td>
                        <td>{{ $movement->total_cost ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7">Nenhuma movimentação encontrada.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination">{{ $movements->links() }}</div>
    </section>
@endsection
