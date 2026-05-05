<?php

use App\Http\Controllers\Api\Internal\V1\CurrentTenantController;
use App\Http\Controllers\Api\Internal\V1\FarmController;
use App\Http\Controllers\Api\Internal\V1\FarmGeoJsonController;
use App\Http\Controllers\Api\Internal\V1\ImportPlotGeoJsonController;
use App\Http\Controllers\Api\Internal\V1\MeController;
use App\Http\Controllers\Api\Internal\V1\PermissionController;
use Illuminate\Support\Facades\Route;

Route::prefix('internal/v1')
    ->as('api.internal.v1.')
    ->middleware(['auth', 'tenant.scope'])
    ->group(function (): void {
        Route::get('/me', MeController::class)->name('me');
        Route::get('/tenants/current', CurrentTenantController::class)->name('tenants.current');
        Route::get('/farms', FarmController::class)->middleware('farm.scope')->name('farms.index');
        Route::get('/farms/{farm}', [FarmController::class, 'show'])->name('farms.show');
        Route::get('/farms/{farm}/geojson', FarmGeoJsonController::class)->name('farms.geojson');
        Route::post('/farms/{farm}/plots/import-geojson', ImportPlotGeoJsonController::class)->name('farms.plots.import-geojson');
        Route::get('/permissions', PermissionController::class)->name('permissions.index');
    });
