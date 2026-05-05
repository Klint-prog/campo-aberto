<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bindings globais serão adicionados conforme as fases do projeto.
    }

    public function boot(): void
    {
        // Bootstrapping de domínio será adicionado quando os módulos forem criados.
    }
}
