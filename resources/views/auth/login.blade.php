<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Campo Aberto</title>
</head>
<body>
    <main>
        <h1>Campo Aberto</h1>
        <form method="POST" action="{{ route('login.store') }}">
            @csrf

            <label for="email">E-mail</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            @error('email')
                <p>{{ $message }}</p>
            @enderror

            <label for="password">Senha</label>
            <input id="password" name="password" type="password" required autocomplete="current-password">
            @error('password')
                <p>{{ $message }}</p>
            @enderror

            <label>
                <input type="checkbox" name="remember" value="1">
                Manter conectado
            </label>

            <button type="submit">Entrar</button>
        </form>
    </main>
</body>
</html>
