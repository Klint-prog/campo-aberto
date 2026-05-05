<!doctype html>
<html lang="pt-BR">
<head><meta charset="utf-8"><title>{{ $user->name }} - Campo Aberto</title></head>
<body>
    <h1>{{ $user->name }}</h1>
    <p>{{ $user->email }}</p>
    <p>Status: {{ $user->is_active ? 'ativo' : 'inativo' }}</p>
    <a href="{{ route('users.index') }}">Voltar</a>
</body>
</html>
