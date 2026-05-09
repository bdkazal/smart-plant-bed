<?php

use App\Http\Controllers\DeviceClaimController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\SmartFountainSceneController;
use App\Http\Controllers\SmartFountainScheduleController;
use App\Http\Controllers\SmartFountainStatusController;
use App\Http\Controllers\WateringScheduleController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('devices.index')
        : redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/devices', [DeviceController::class, 'index'])->name('devices.index');

    Route::get('/devices/add', [DeviceClaimController::class, 'create'])->name('devices.add');
    Route::post('/devices/claim', [DeviceClaimController::class, 'store'])->name('devices.claim');

    Route::get('/claim/{code}', [DeviceClaimController::class, 'show'])->name('devices.claim.qr');
    Route::post('/claim/{code}', [DeviceClaimController::class, 'confirm'])->name('devices.claim.confirm');

    Route::get('/devices/{device}/setup', [DeviceClaimController::class, 'setup'])->name('devices.setup');

    Route::get('/devices/{device}', [DeviceController::class, 'show'])->name('devices.show');
    Route::get('/devices/{device}/status', [DeviceController::class, 'status'])->name('devices.status');
    Route::get('/devices/{device}/smart-fountain/status', SmartFountainStatusController::class)->name('devices.smart-fountain.status');
    Route::get('/devices/{device}/automation', [DeviceController::class, 'automation'])->name('devices.automation');
    Route::get('/devices/{device}/history', [DeviceController::class, 'history'])->name('devices.history');

    Route::get('/devices/{device}/smart-fountain/scenes', [SmartFountainSceneController::class, 'index'])
        ->name('devices.smart-fountain.scenes.index');
    Route::get('/devices/{device}/smart-fountain/scenes/create', [SmartFountainSceneController::class, 'create'])
        ->name('devices.smart-fountain.scenes.create');
    Route::post('/devices/{device}/smart-fountain/scenes', [SmartFountainSceneController::class, 'store'])
        ->name('devices.smart-fountain.scenes.store');
    Route::get('/devices/{device}/smart-fountain/scenes/{scene}/edit', [SmartFountainSceneController::class, 'edit'])
        ->name('devices.smart-fountain.scenes.edit');
    Route::put('/devices/{device}/smart-fountain/scenes/{scene}', [SmartFountainSceneController::class, 'update'])
        ->name('devices.smart-fountain.scenes.update');
    Route::delete('/devices/{device}/smart-fountain/scenes/{scene}', [SmartFountainSceneController::class, 'destroy'])
        ->name('devices.smart-fountain.scenes.destroy');
    Route::post('/devices/{device}/smart-fountain/scenes/{scene}/apply', [SmartFountainSceneController::class, 'apply'])
        ->name('devices.smart-fountain.scenes.apply');

    Route::get('/devices/{device}/smart-fountain/schedules', [SmartFountainScheduleController::class, 'index'])
        ->name('devices.smart-fountain.schedules.index');
    Route::get('/devices/{device}/smart-fountain/schedules/create', [SmartFountainScheduleController::class, 'create'])
        ->name('devices.smart-fountain.schedules.create');
    Route::post('/devices/{device}/smart-fountain/schedules', [SmartFountainScheduleController::class, 'store'])
        ->name('devices.smart-fountain.schedules.store');
    Route::get('/devices/{device}/smart-fountain/schedules/{schedule}/edit', [SmartFountainScheduleController::class, 'edit'])
        ->name('devices.smart-fountain.schedules.edit');
    Route::put('/devices/{device}/smart-fountain/schedules/{schedule}', [SmartFountainScheduleController::class, 'update'])
        ->name('devices.smart-fountain.schedules.update');
    Route::patch('/devices/{device}/smart-fountain/schedules/{schedule}/toggle', [SmartFountainScheduleController::class, 'toggle'])
        ->name('devices.smart-fountain.schedules.toggle');
    Route::delete('/devices/{device}/smart-fountain/schedules/{schedule}', [SmartFountainScheduleController::class, 'destroy'])
        ->name('devices.smart-fountain.schedules.destroy');

    Route::post('/devices/{device}/settings', [DeviceController::class, 'updateSettings'])->name('devices.settings.update');
    Route::post('/devices/{device}/water-now', [DeviceController::class, 'waterNow'])->name('devices.water-now');
    Route::post('/devices/{device}/water-stop', [DeviceController::class, 'stopWatering'])->name('devices.water-stop');
    Route::post('/devices/{device}/outputs/{output}/set', [DeviceController::class, 'setOutput'])->name('devices.outputs.set');

    Route::get('/devices/{device}/schedules', [WateringScheduleController::class, 'index'])
        ->name('devices.schedules.index');
    Route::get('/devices/{device}/schedules/create', [WateringScheduleController::class, 'create'])
        ->name('devices.schedules.create');
    Route::post('/devices/{device}/schedules', [WateringScheduleController::class, 'store'])
        ->name('devices.schedules.store');
    Route::get('/devices/{device}/schedules/{schedule}/edit', [WateringScheduleController::class, 'edit'])
        ->name('devices.schedules.edit');
    Route::put('/devices/{device}/schedules/{schedule}', [WateringScheduleController::class, 'update'])
        ->name('devices.schedules.update');
    Route::patch('/devices/{device}/schedules/{schedule}/toggle', [WateringScheduleController::class, 'toggle'])
        ->name('devices.schedules.toggle');
    Route::delete('/devices/{device}/schedules/{schedule}', [WateringScheduleController::class, 'destroy'])
        ->name('devices.schedules.destroy');
});
