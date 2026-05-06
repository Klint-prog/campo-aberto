<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantScope
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->tenant_id || ! $user->tenant?->is_active) {
            abort(Response::HTTP_FORBIDDEN, 'Tenant inválido ou inativo.');
        }

        $routeUser = $request->route('user');
        if ($this->hasTenant($routeUser) && $routeUser->tenant_id !== $user->tenant_id) {
            abort(Response::HTTP_FORBIDDEN, 'Usuário fora do tenant atual.');
        }

        $routeFarm = $request->route('farm');
        if ($this->hasTenant($routeFarm) && $routeFarm->tenant_id !== $user->tenant_id) {
            abort(Response::HTTP_FORBIDDEN, 'Fazenda fora do tenant atual.');
        }

        app()->instance('currentTenant', $user->tenant);

        return $next($request);
    }

    private function hasTenant(mixed $routeParameter): bool
    {
        return is_object($routeParameter) && isset($routeParameter->tenant_id);
    }
}
