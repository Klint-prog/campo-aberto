@extends('layouts.app')

@section('title', 'Editar usuário - Campo Aberto')
@section('page_title', 'Editar usuário')
@section('breadcrumb', 'Administração / Usuários / Editar')

@section('content')
    <section class="card">
        <form class="form-grid" method="POST" action="{{ route('users.update', $user) }}">
            @csrf
            @method('PUT')

            <div>
                <label for="name">Nome</label>
                <input id="name" name="name" value="{{ old('name', $user->name) }}" required>
                @error('name')<div class="errors">{{ $message }}</div>@enderror
            </div>

            <div>
                <label for="email">E-mail</label>
                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required>
                @error('email')<div class="errors">{{ $message }}</div>@enderror
            </div>

            <div class="grid cards">
                <div>
                    <label for="password">Nova senha</label>
                    <input id="password" type="password" name="password" autocomplete="new-password">
                    @error('password')<div class="errors">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label for="password_confirmation">Confirmar nova senha</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password">
                </div>
            </div>

            <label>
                <input style="width:auto" type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active))>
                Usuário ativo
            </label>

            <div class="actions">
                <button class="btn" type="submit">Salvar alterações</button>
                <a class="btn secondary" href="{{ route('users.show', $user) }}">Cancelar</a>
            </div>
        </form>
    </section>
@endsection
