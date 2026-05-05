<?php

use App\Http\Controllers\Internal\AlertController;
use App\Http\Controllers\Internal\WeatherController;
use Illuminate\Support\Facades\Route;

Route::prefix('internal/v1')->group(function (): void {
    Route::get('farms/weather/forecast', [WeatherController::class, 'forecast']);
    Route::get('farms/weather/history', [WeatherController::class, 'history']);
    Route::post('farms/weather/manual-rain', [WeatherController::class, 'storeManualRain']);

    Route::get('alerts', [AlertController::class, 'index']);
    Route::post('alerts', [AlertController::class, 'store']);
    Route::get('notifications', [AlertController::class, 'notifications']);
});
