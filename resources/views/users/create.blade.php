@extends('layouts.app')

@section('title', 'Novo usuário - Campo Aberto')
@section('page_title', 'Novo usuário')
@section('breadcrumb', 'Administração / Usuários / Novo')

@section('content')
    <section class="card">
        <form class="form-grid" method="POST" action="{{ route('users.store') }}">
            @csrf

            <div>
                <label for="name">Nome</label>
                <input id="name" name="name" value="{{ old('name') }}" required>
                @error('name')<div class="errors">{{ $message }}</div>@enderror
            </div>

            <div>
                <label for="email">E-mail</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required>
                @error('email')<div class="errors">{{ $message }}</div>@enderror
            </div>

            <div class="grid cards">
                <div>
                    <label for="password">Senha</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password">
                    @error('password')<div class="errors">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label for="password_confirmation">Confirmar senha</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
                </div>
            </div>

            <label>
                <input style="width:auto" type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                Usuário ativo
            </label>

            <div class="actions">
                <button class="btn" type="submit">Salvar usuário</button>
                <a class="btn secondary" href="{{ route('users.index') }}">Cancelar</a>
            </div>
        </form>
    </section>
@endsection
