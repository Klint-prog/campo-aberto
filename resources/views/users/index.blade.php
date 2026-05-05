<!doctype html>
<html lang="pt-BR">
<head><meta charset="utf-8"><title>Usuários - Campo Aberto</title></head>
<body>
    <h1>Usuários</h1>
    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Sair</button></form>
    <ul>
        @foreach ($users as $user)
            <li><a href="{{ route('users.show', $user) }}">{{ $user->name }}</a> - {{ $user->email }}</li>
        @endforeach
    </ul>
    {{ $users->links() }}
</body>
</html>
