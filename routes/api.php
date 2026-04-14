<?php

use App\Http\Controllers\Api\DeviceReadingController;
use App\Http\Controllers\Api\DeviceCommandController;
use App\Http\Controllers\Api\DeviceConfigController;
use Illuminate\Support\Facades\Route;

Route::post('/device/readings', [DeviceReadingController::class, 'store']);
Route::get('/device/commands', [DeviceCommandController::class, 'index']);
Route::post('/device/commands/{command}/ack', [DeviceCommandController::class, 'ack']);
Route::get('/device/config', [DeviceConfigController::class, 'show']);
