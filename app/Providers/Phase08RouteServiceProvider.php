<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class Phase08RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::middleware(['api'])
            ->prefix('api/internal/v1')
            ->group(base_path('routes/internal_phase08.php'));
    }
}
