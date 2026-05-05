<?php

namespace App\Providers;

use App\Models\Farm;
use App\Models\User;
use App\Policies\FarmPolicy;
use App\Policies\UserPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bindings globais serão adicionados conforme as fases do projeto.
    }

    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $email = Str::lower((string) $request->input('email'));

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Farm::class, FarmPolicy::class);

        Gate::define('permission', fn (User $user, string $permission): bool => $user->hasPermission($permission));
        Gate::define('role', fn (User $user, string $role): bool => $user->hasRole($role));
        Gate::define('access-farm', fn (User $user, Farm|string $farm): bool => $user->canAccessFarm($farm));
    }
}
