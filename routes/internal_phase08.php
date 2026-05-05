<?php

use App\Http\Controllers\Internal\V1\AiConsultationController;
use App\Http\Controllers\Internal\V1\DashboardController;
use App\Http\Controllers\Internal\V1\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/reports', [ReportController::class, 'index']);
Route::get('/reports/{report}', [ReportController::class, 'show']);
Route::post('/reports/{report}/export', [ReportController::class, 'export']);

Route::get('/dashboards', [DashboardController::class, 'index']);
Route::get('/dashboards/{dashboard}', [DashboardController::class, 'show']);

Route::post('/ai/consultations', [AiConsultationController::class, 'store']);
Route::post('/ai/recommendations/{recommendation}/confirm', [AiConsultationController::class, 'confirm']);
