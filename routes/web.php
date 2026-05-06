<?php

use App\Http\Controllers\ActivityInputWebController;
use App\Http\Controllers\ActivityWebController;
use App\Http\Controllers\AgricultureReportWebController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CropVarietyWebController;
use App\Http\Controllers\CropWebController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FarmMapController;
use App\Http\Controllers\FarmWebController;
use App\Http\Controllers\FieldWebController;
use App\Http\Controllers\HarvestWebController;
use App\Http\Controllers\OperationalModuleController;
use App\Http\Controllers\PastureWebController;
use App\Http\Controllers\SeasonWebController;
use App\Http\Controllers\UnderConstructionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
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

    Route::get('/farms/export.csv', [FarmWebController::class, 'export'])->name('farms.export');
    Route::resource('farms', FarmWebController::class);

    Route::get('/fields/export.csv', [FieldWebController::class, 'export'])->name('fields.export');
    Route::resource('fields', FieldWebController::class);

    Route::get('/pastures/export.csv', [PastureWebController::class, 'export'])->name('pastures.export');
    Route::resource('pastures', PastureWebController::class);

    Route::get('/crops/export.csv', [CropWebController::class, 'export'])->name('crops.export');
    Route::resource('crops', CropWebController::class);

    Route::get('/crop-varieties/export.csv', [CropVarietyWebController::class, 'export'])->name('crop-varieties.export');
    Route::resource('crop-varieties', CropVarietyWebController::class);

    Route::get('/seasons/export.csv', [SeasonWebController::class, 'export'])->name('seasons.export');
    Route::resource('seasons', SeasonWebController::class);

    Route::get('/activities/export.csv', [ActivityWebController::class, 'export'])->name('activities.export');
    Route::post('/activities/{activity}/complete', [ActivityWebController::class, 'complete'])->name('activities.complete');
    Route::post('/activities/{activity}/cancel', [ActivityWebController::class, 'cancel'])->name('activities.cancel');
    Route::post('/activities/{activity}/inputs', [ActivityInputWebController::class, 'store'])->name('activities.inputs.store');
    Route::delete('/activities/{activity}/inputs/{input}', [ActivityInputWebController::class, 'destroy'])->name('activities.inputs.destroy');
    Route::resource('activities', ActivityWebController::class)->except('destroy');

    Route::get('/harvests/export.csv', [HarvestWebController::class, 'export'])->name('harvests.export');
    Route::resource('harvests', HarvestWebController::class)->except('destroy');

    Route::get('/reports/agriculture', AgricultureReportWebController::class)->name('reports.agriculture');

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
