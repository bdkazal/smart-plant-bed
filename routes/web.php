<?php

use App\Http\Controllers\DeviceClaimController;
use App\Http\Controllers\DeviceController;
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
    Route::post('/devices/{device}/water-now', [DeviceController::class, 'waterNow'])->name('devices.water-now');
});
