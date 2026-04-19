<?php

use App\Http\Controllers\DeviceClaimController;
use App\Http\Controllers\DeviceController;
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

    Route::get('/devices/{device}', [DeviceController::class, 'show'])->name('devices.show');
    Route::post('/devices/{device}/settings', [DeviceController::class, 'updateSettings'])->name('devices.settings.update');
    Route::post('/devices/{device}/water-now', [DeviceController::class, 'waterNow'])->name('devices.water-now');
    Route::post('/devices/{device}/water-stop', [DeviceController::class, 'stopWatering'])->name('devices.water-stop');
});
