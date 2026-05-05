<?php

namespace App\Providers;

use App\Models\Farm;
use App\Models\User;
use App\Policies\FarmPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bindings globais serão adicionados conforme as fases do projeto.
    }

    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Farm::class, FarmPolicy::class);

        Gate::define('permission', fn (User $user, string $permission): bool => $user->hasPermission($permission));
        Gate::define('role', fn (User $user, string $role): bool => $user->hasRole($role));
        Gate::define('access-farm', fn (User $user, Farm|string $farm): bool => $user->canAccessFarm($farm));
    }
}
