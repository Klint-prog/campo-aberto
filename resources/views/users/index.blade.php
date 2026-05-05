@extends('layouts.app')

@section('title', 'Usuários - Campo Aberto')
@section('page_title', 'Usuários')
@section('breadcrumb', 'Administração / Usuários')

@section('content')
    <section class="card">
        <div class="actions" style="justify-content:space-between; margin-bottom:18px">
            <div>
                <h2 style="margin:0">Usuários do tenant</h2>
                <p class="muted" style="margin:4px 0 0">Controle visual de usuários respeitando policies e tenant_id.</p>
            </div>
            @can('create', App\Models\User::class)
                <a class="btn" href="{{ route('users.create') }}">Novo usuário</a>
            @endcan
        </div>

        @if ($users->isEmpty())
            <div class="empty">Nenhum usuário encontrado.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Status</th>
                        <th>Criado em</th>
                        <th>Ações</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td><strong>{{ $user->name }}</strong></td>
                            <td>{{ $user->email }}</td>
                            <td><span class="badge">{{ $user->is_active ? 'Ativo' : 'Inativo' }}</span></td>
                            <td>{{ $user->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="actions">
                                <a class="btn secondary" href="{{ route('users.show', $user) }}">Ver</a>
                                @can('update', $user)
                                    <a class="btn secondary" href="{{ route('users.edit', $user) }}">Editar</a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination">{{ $users->links() }}</div>
        @endif
    </section>
@endsection
