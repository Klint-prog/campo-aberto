<?php

use App\Http\Controllers\Internal\V1\HealthcheckController;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:healthcheck'])->group(function (): void {
    Route::get('/health', HealthcheckController::class)->name('internal.v1.health');
});
