<?php

namespace App\Providers;

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class Phase09ProductionServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configureRateLimiting();
        $this->registerMiddleware();
        $this->registerRoutes();
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('healthcheck', function (Request $request): Limit {
            return Limit::perMinute(60)->by($request->ip());
        });
    }

    private function registerMiddleware(): void
    {
        $router = $this->app['router'];

        $router->pushMiddlewareToGroup('api', SecurityHeaders::class);
        $router->pushMiddlewareToGroup('web', SecurityHeaders::class);
    }

    private function registerRoutes(): void
    {
        foreach (glob(base_path('routes/internal_phase*.php')) ?: [] as $routesFile) {
            Route::prefix('api/internal/v1')
                ->middleware('api')
                ->group($routesFile);
        }
    }
}
