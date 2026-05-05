@extends('layouts.app')

@section('title', $user->name.' - Campo Aberto')
@section('page_title', $user->name)
@section('breadcrumb', 'Administração / Usuários / Detalhe')

@section('content')
    <section class="card">
        <div class="actions" style="justify-content:space-between; margin-bottom:18px">
            <div>
                <h2 style="margin:0">{{ $user->name }}</h2>
                <p class="muted" style="margin:4px 0 0">{{ $user->email }}</p>
            </div>
            <div class="actions">
                <a class="btn secondary" href="{{ route('users.index') }}">Voltar</a>
                @can('update', $user)
                    <a class="btn" href="{{ route('users.edit', $user) }}">Editar</a>
                @endcan
            </div>
        </div>

        <div class="grid cards">
            <div>
                <strong>Status</strong>
                <p><span class="badge">{{ $user->is_active ? 'Ativo' : 'Inativo' }}</span></p>
            </div>
            <div>
                <strong>Tenant</strong>
                <p>{{ $user->tenant?->name ?? $user->tenant_id }}</p>
            </div>
            <div>
                <strong>Criado em</strong>
                <p>{{ $user->created_at?->format('d/m/Y H:i') ?? '—' }}</p>
            </div>
            <div>
                <strong>Atualizado em</strong>
                <p>{{ $user->updated_at?->format('d/m/Y H:i') ?? '—' }}</p>
            </div>
        </div>

        @can('delete', $user)
            <form method="POST" action="{{ route('users.destroy', $user) }}" style="margin-top:22px" onsubmit="return confirm('Confirma a inativação/remoção deste usuário?')">
                @csrf
                @method('DELETE')
                <button class="btn danger" type="submit">Inativar/remover usuário</button>
            </form>
        @endcan
    </section>
@endsection
