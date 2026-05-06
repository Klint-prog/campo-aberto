@extends('layouts.app')

@section('title', 'Transações financeiras - Campo Aberto')
@section('page_title', 'Transações financeiras')
@section('breadcrumb', 'Financeiro / Transações')

@section('content')
    <section class="card">
        <div class="actions" style="justify-content:space-between; margin-bottom:18px">
            <div>
                <h2 style="margin:0">Transações financeiras</h2>
                <p class="muted" style="margin:4px 0 0">Receitas e despesas simples do MVP financeiro.</p>
            </div>
            <div class="actions">
                <a class="btn secondary" href="{{ route('finance.transactions.export', request()->query()) }}">Exportar CSV</a>
                <a class="btn secondary" href="{{ route('finance.accounts.index') }}">Contas</a>
                <a class="btn secondary" href="{{ route('finance.categories.index') }}">Categorias</a>
                <a class="btn" href="{{ route('finance.transactions.create') }}">Nova transação</a>
            </div>
        </div>

        <form method="GET" class="card" style="box-shadow:none; margin-bottom:18px; padding:14px">
            <div class="form-grid" style="max-width:none; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); align-items:end">
                <div>
                    <label for="farm_id">Fazenda</label>
                    <select id="farm_id" name="farm_id">
                        <option value="">Todas</option>
                        @foreach ($farms as $id => $name)
                            <option value="{{ $id }}" @selected(($filters['farm_id'] ?? '') === $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="type">Tipo</label>
                    <select id="type" name="type">
                        <option value="">Todos</option>
                        <option value="revenue" @selected(($filters['type'] ?? '') === 'revenue')>Receita</option>
                        <option value="expense" @selected(($filters['type'] ?? '') === 'expense')>Despesa</option>
                    </select>
                </div>
                <div>
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">Todos</option>
                        <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Pendente</option>
                        <option value="paid" @selected(($filters['status'] ?? '') === 'paid')>Pago</option>
                        <option value="overdue" @selected(($filters['status'] ?? '') === 'overdue')>Vencido</option>
                        <option value="cancelled" @selected(($filters['status'] ?? '') === 'cancelled')>Cancelado</option>
                    </select>
                </div>
                <div>
                    <label for="financial_account_id">Conta</label>
                    <select id="financial_account_id" name="financial_account_id">
                        <option value="">Todas</option>
                        @foreach ($accounts as $id => $name)
                            <option value="{{ $id }}" @selected(($filters['financial_account_id'] ?? '') === $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="financial_category_id">Categoria</label>
                    <select id="financial_category_id" name="financial_category_id">
                        <option value="">Todas</option>
                        @foreach ($categories as $id => $name)
                            <option value="{{ $id }}" @selected(($filters['financial_category_id'] ?? '') === $id)>{{ $name }}</option>
                        @endforeach
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
                    <a class="btn secondary" href="{{ route('finance.transactions.index') }}">Limpar</a>
                </div>
            </div>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Vencimento</th>
                    <th>Descrição</th>
                    <th>Fazenda</th>
                    <th>Conta</th>
                    <th>Categoria</th>
                    <th>Tipo</th>
                    <th>Status</th>
                    <th>Valor</th>
                    <th>Pagamento</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($transactions as $transaction)
                    <tr>
                        <td>{{ optional($transaction->due_on)->format('d/m/Y') ?? '—' }}</td>
                        <td>{{ $transaction->description }}</td>
                        <td>{{ $transaction->farm?->name ?? '—' }}</td>
                        <td>{{ $transaction->account?->name ?? '—' }}</td>
                        <td>{{ $transaction->category?->name ?? '—' }}</td>
                        <td><span class="badge">{{ $transaction->type === 'revenue' ? 'Receita' : 'Despesa' }}</span></td>
                        <td>{{ $transaction->status }}</td>
                        <td>R$ {{ number_format((float) $transaction->amount, 2, ',', '.') }}</td>
                        <td>{{ optional($transaction->paid_on)->format('d/m/Y') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="empty">Nenhuma transação financeira encontrada.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">{{ $transactions->links() }}</div>
    </section>
@endsection
