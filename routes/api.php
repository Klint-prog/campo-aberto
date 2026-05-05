<?php

use App\Http\Controllers\Api\Internal\V1\ActivityController;
use App\Http\Controllers\Api\Internal\V1\ActivityInputController;
use App\Http\Controllers\Api\Internal\V1\CropController;
use App\Http\Controllers\Api\Internal\V1\CropVarietyController;
use App\Http\Controllers\Api\Internal\V1\CurrentTenantController;
use App\Http\Controllers\Api\Internal\V1\FarmController;
use App\Http\Controllers\Api\Internal\V1\FarmGeoJsonController;
use App\Http\Controllers\Api\Internal\V1\HarvestController;
use App\Http\Controllers\Api\Internal\V1\ImportPlotGeoJsonController;
use App\Http\Controllers\Api\Internal\V1\MeController;
use App\Http\Controllers\Api\Internal\V1\PermissionController;
use App\Http\Controllers\Api\Internal\V1\SeasonController;
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

        Route::get('/crops', [CropController::class, 'index'])->name('crops.index');
        Route::post('/crops', [CropController::class, 'store'])->name('crops.store');
        Route::put('/crops/{crop}', [CropController::class, 'update'])->name('crops.update');
        Route::post('/crops/{crop}/varieties', [CropVarietyController::class, 'store'])->name('crops.varieties.store');

        Route::get('/farms/{farm}/seasons', [SeasonController::class, 'index'])->name('farms.seasons.index');
        Route::post('/farms/{farm}/seasons', [SeasonController::class, 'store'])->name('farms.seasons.store');
        Route::get('/farms/{farm}/activities', [ActivityController::class, 'index'])->name('farms.activities.index');
        Route::post('/farms/{farm}/activities', [ActivityController::class, 'store'])->name('farms.activities.store');
        Route::post('/farms/{farm}/activities/{activity}/complete', [ActivityController::class, 'complete'])->name('farms.activities.complete');
        Route::post('/farms/{farm}/activities/{activity}/cancel', [ActivityController::class, 'cancel'])->name('farms.activities.cancel');
        Route::post('/farms/{farm}/activities/{activity}/inputs', [ActivityInputController::class, 'store'])->name('farms.activities.inputs.store');
        Route::get('/farms/{farm}/harvests', [HarvestController::class, 'index'])->name('farms.harvests.index');
        Route::post('/farms/{farm}/harvests', [HarvestController::class, 'store'])->name('farms.harvests.store');

        Route::get('/permissions', PermissionController::class)->name('permissions.index');
    });
