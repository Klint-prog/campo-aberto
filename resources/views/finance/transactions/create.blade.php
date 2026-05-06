@extends('layouts.app')

@section('title', 'Nova transação financeira - Campo Aberto')
@section('page_title', 'Nova transação financeira')
@section('breadcrumb', 'Financeiro / Transações / Nova')

@section('content')
    <section class="card">
        <div class="actions" style="justify-content:space-between; margin-bottom:18px">
            <div>
                <h2 style="margin:0">Nova transação financeira</h2>
                <p class="muted" style="margin:4px 0 0">Cadastro simples de receita ou despesa do MVP financeiro.</p>
            </div>
            <a class="btn secondary" href="{{ route('finance.transactions.index') }}">Voltar</a>
        </div>

        <form method="POST" action="{{ route('finance.transactions.store') }}" class="form-grid">
            @csrf

            <div>
                <label for="farm_id">Fazenda</label>
                <select id="farm_id" name="farm_id" required>
                    <option value="">Selecione</option>
                    @foreach ($farms as $id => $name)
                        <option value="{{ $id }}" @selected(old('farm_id') === $id)>{{ $name }}</option>
                    @endforeach
                </select>
                @error('farm_id') <div class="errors">{{ $message }}</div> @enderror
            </div>

            <div>
                <label for="financial_account_id">Conta</label>
                <select id="financial_account_id" name="financial_account_id" required>
                    <option value="">Selecione</option>
                    @foreach ($accounts as $id => $name)
                        <option value="{{ $id }}" @selected(old('financial_account_id') === $id)>{{ $name }}</option>
                    @endforeach
                </select>
                @error('financial_account_id') <div class="errors">{{ $message }}</div> @enderror
            </div>

            <div>
                <label for="type">Tipo</label>
                <select id="type" name="type" required>
                    <option value="">Selecione</option>
                    <option value="revenue" @selected(old('type') === 'revenue')>Receita</option>
                    <option value="expense" @selected(old('type') === 'expense')>Despesa</option>
                </select>
                @error('type') <div class="errors">{{ $message }}</div> @enderror
            </div>

            <div>
                <label for="financial_category_id">Categoria</label>
                <select id="financial_category_id" name="financial_category_id" required>
                    <option value="">Selecione</option>
                    @foreach ($categories as $id => $name)
                        <option value="{{ $id }}" @selected(old('financial_category_id') === $id)>{{ $name }}</option>
                    @endforeach
                </select>
                @error('financial_category_id') <div class="errors">{{ $message }}</div> @enderror
            </div>

            <div>
                <label for="description">Descrição</label>
                <input id="description" name="description" type="text" value="{{ old('description') }}" required maxlength="255">
                @error('description') <div class="errors">{{ $message }}</div> @enderror
            </div>

            <div>
                <label for="amount">Valor</label>
                <input id="amount" name="amount" type="number" min="0.01" step="0.01" value="{{ old('amount') }}" required>
                @error('amount') <div class="errors">{{ $message }}</div> @enderror
            </div>

            <div>
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="pending" @selected(old('status', 'pending') === 'pending')>Pendente</option>
                    <option value="paid" @selected(old('status') === 'paid')>Pago</option>
                    <option value="overdue" @selected(old('status') === 'overdue')>Vencido</option>
                    <option value="cancelled" @selected(old('status') === 'cancelled')>Cancelado</option>
                </select>
                @error('status') <div class="errors">{{ $message }}</div> @enderror
            </div>

            <div>
                <label for="due_on">Vencimento</label>
                <input id="due_on" name="due_on" type="date" value="{{ old('due_on', now()->toDateString()) }}" required>
                @error('due_on') <div class="errors">{{ $message }}</div> @enderror
            </div>

            <div>
                <label for="paid_on">Pagamento</label>
                <input id="paid_on" name="paid_on" type="date" value="{{ old('paid_on') }}">
                @error('paid_on') <div class="errors">{{ $message }}</div> @enderror
            </div>

            <div class="actions">
                <button class="btn" type="submit">Salvar transação</button>
                <a class="btn secondary" href="{{ route('finance.transactions.index') }}">Cancelar</a>
            </div>
        </form>
    </section>
@endsection
