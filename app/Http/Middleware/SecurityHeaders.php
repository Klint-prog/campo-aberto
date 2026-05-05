<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        foreach (config('phase09.security_headers', []) as $header => $value) {
            if ($header === 'Strict-Transport-Security' && ! $request->isSecure() && ! app()->environment('production')) {
                continue;
            }

            $response->headers->set($header, $value);
        }

        return $response;
    }
}
