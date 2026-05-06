@extends('layouts.app')

@section('title', 'Manutenções - Campo Aberto')
@section('page_title', 'Manutenções')
@section('breadcrumb', 'Máquinas / Manutenções')

@section('content')
    <section class="card">
        <div class="actions" style="justify-content:space-between; margin-bottom:18px">
            <div>
                <h2 style="margin:0">Manutenções</h2>
                <p class="muted" style="margin:4px 0 0">Manutenções por período, máquina e status.</p>
            </div>
            <div class="actions">
                <a class="btn secondary" href="{{ route('maintenance.export', request()->query()) }}">Exportar CSV</a>
                <a class="btn" href="{{ route('machines.index') }}">Máquinas</a>
            </div>
        </div>

        <form method="GET" class="card" style="box-shadow:none; margin-bottom:18px; padding:14px">
            <div class="form-grid" style="max-width:none; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); align-items:end">
                <div>
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">Todos</option>
                        <option value="scheduled" @selected(($filters['status'] ?? '') === 'scheduled')>Agendada</option>
                        <option value="performed" @selected(($filters['status'] ?? '') === 'performed')>Realizada</option>
                        <option value="cancelled" @selected(($filters['status'] ?? '') === 'cancelled')>Cancelada</option>
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
                    <a class="btn secondary" href="{{ route('maintenance.index') }}">Limpar</a>
                </div>
            </div>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Agendada</th>
                    <th>Máquina</th>
                    <th>Fazenda</th>
                    <th>Tipo</th>
                    <th>Status</th>
                    <th>Custo</th>
                    <th>Fornecedor</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($records as $record)
                    <tr>
                        <td>{{ optional($record->scheduled_on)->format('d/m/Y') ?? '—' }}</td>
                        <td>{{ $record->machine?->name ?? '—' }}</td>
                        <td>{{ $record->farm?->name ?? '—' }}</td>
                        <td>{{ $record->type }}</td>
                        <td>{{ $record->status }}</td>
                        <td>{{ $record->cost ?? '—' }}</td>
                        <td>{{ $record->supplier ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7">Nenhuma manutenção encontrada.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination">{{ $records->links() }}</div>
    </section>
@endsection
