<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceConfigController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_uuid' => ['required', 'uuid'],
        ]);

        $deviceKey = $request->header('X-DEVICE-KEY');

        if (!$deviceKey) {
            return response()->json([
                'message' => 'Missing device API key.',
            ], 401);
        }

        $device = Device::with([
            'wateringRule',
            'wateringSchedules' => fn($query) => $query
                ->orderBy('day_of_week')
                ->orderBy('time_of_day'),
        ])
            ->where('uuid', $validated['device_uuid'])
            ->where('api_key', $deviceKey)
            ->first();

        if (!$device) {
            return response()->json([
                'message' => 'Invalid device credentials.',
            ], 401);
        }

        return response()->json([
            'message' => 'Device config fetched successfully.',
            'config' => [
                'device_uuid' => $device->uuid,
                'device_name' => $device->name,
                'timezone' => $device->timezone ?? 'Asia/Dhaka',
                'auto_mode_enabled' => $device->wateringRule?->auto_mode_enabled ?? false,
                'soil_moisture_threshold' => $device->wateringRule?->soil_moisture_threshold,
                'max_watering_duration_seconds' => $device->wateringRule?->max_watering_duration_seconds ?? 30,
                'cooldown_minutes' => $device->wateringRule?->cooldown_minutes ?? 60,
                'local_manual_duration_seconds' => $device->wateringRule?->local_manual_duration_seconds ?? 30,
                'schedules' => $device->wateringSchedules->map(function ($schedule) {
                    return [
                        'id' => $schedule->id,
                        'is_enabled' => (bool) $schedule->is_enabled,
                        'day_of_week' => $schedule->day_of_week,
                        'time_of_day' => $schedule->time_of_day,
                        'duration_seconds' => $schedule->duration_seconds,
                    ];
                })->values(),
            ],
        ]);
    }
}
