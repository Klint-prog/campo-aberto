<?php

namespace App\Http\Middleware;

use App\Models\Farm;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFarmScope
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        $farmId = $request->route('farm')?->getKey() ?? $request->query('farm_id') ?? $request->input('farm_id');

        if ($farmId) {
            $farm = Farm::query()
                ->where('tenant_id', $user->tenant_id)
                ->findOrFail($farmId);

            if (! $user->canAccessFarm($farm)) {
                abort(Response::HTTP_FORBIDDEN, 'Usuário sem acesso à fazenda informada.');
            }

            app()->instance('currentFarm', $farm);
        }

        return $next($request);
    }
}
