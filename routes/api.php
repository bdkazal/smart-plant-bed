<?php

use App\Http\Controllers\Api\DeviceCommandController;
use App\Http\Controllers\Api\DeviceConfigController;
use App\Http\Controllers\Api\DeviceHeartbeatController;
use App\Http\Controllers\Api\DeviceReadingController;
use Illuminate\Support\Facades\Route;

Route::get('/device/config', [DeviceConfigController::class, 'show']);
Route::post('/device/readings', [DeviceReadingController::class, 'store']);

Route::get('/device/commands', [DeviceCommandController::class, 'index']);
Route::post('/device/commands/{command}/ack', [DeviceCommandController::class, 'ack']);

Route::post('/device/heartbeat', [DeviceHeartbeatController::class, 'store']);
