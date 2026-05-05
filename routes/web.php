<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FarmMapController;
use App\Http\Controllers\OperationalModuleController;
use App\Http\Controllers\UnderConstructionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login.store');
});

Route::middleware(['auth', 'tenant.scope'])->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/under-construction/{module?}', UnderConstructionController::class)->name('under-construction');

    Route::get('/map', [OperationalModuleController::class, 'map'])->name('map.index');
    Route::get('/farms/{farm}/map', FarmMapController::class)->name('farms.map');

    Route::get('/farms', fn (Illuminate\Http\Request $request) => app(OperationalModuleController::class)->index($request, 'farms'))->name('farms.index');
    Route::get('/fields', fn (Illuminate\Http\Request $request) => app(OperationalModuleController::class)->index($request, 'fields'))->name('fields.index');
    Route::get('/pastures', fn (Illuminate\Http\Request $request) => app(OperationalModuleController::class)->index($request, 'pastures'))->name('pastures.index');
    Route::get('/crops', fn (Illuminate\Http\Request $request) => app(OperationalModuleController::class)->index($request, 'crops'))->name('crops.index');
    Route::get('/seasons', fn (Illuminate\Http\Request $request) => app(OperationalModuleController::class)->index($request, 'seasons'))->name('seasons.index');
    Route::get('/activities', fn (Illuminate\Http\Request $request) => app(OperationalModuleController::class)->index($request, 'activities'))->name('activities.index');
    Route::get('/animals', fn (Illuminate\Http\Request $request) => app(OperationalModuleController::class)->index($request, 'animals'))->name('animals.index');
    Route::get('/animal-groups', fn (Illuminate\Http\Request $request) => app(OperationalModuleController::class)->index($request, 'animal-groups'))->name('animal-groups.index');
    Route::get('/inventory', fn (Illuminate\Http\Request $request) => app(OperationalModuleController::class)->index($request, 'inventory'))->name('inventory.index');
    Route::get('/machines', fn (Illuminate\Http\Request $request) => app(OperationalModuleController::class)->index($request, 'machines'))->name('machines.index');
    Route::get('/finance', fn (Illuminate\Http\Request $request) => app(OperationalModuleController::class)->index($request, 'finance'))->name('finance.index');
    Route::get('/reports', fn (Illuminate\Http\Request $request) => app(OperationalModuleController::class)->index($request, 'reports'))->name('reports.index');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});
