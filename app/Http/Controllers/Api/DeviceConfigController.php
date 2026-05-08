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

        if (! $deviceKey) {
            return response()->json([
                'message' => 'Missing device API key.',
            ], 401);
        }

        $device = Device::with([
            'wateringRule',
            'wateringSchedules' => fn($query) => $query
                ->orderBy('day_of_week')
                ->orderBy('time_of_day'),
            'capabilities',
            'outputs',
        ])
            ->where('uuid', $validated['device_uuid'])
            ->where('api_key', $deviceKey)
            ->first();

        if (! $device) {
            return response()->json([
                'message' => 'Invalid device credentials.',
            ], 401);
        }

        $device->update([
            'status' => in_array($device->status, ['claimed', 'claimed_pending_wifi'], true) ? 'active' : $device->status,
            'last_seen_at' => now(),
        ]);

        if ($device->isSmartFountain()) {
            return response()->json([
                'message' => 'Device config fetched successfully.',
                'config' => $this->smartFountainConfig($device->fresh(['capabilities', 'outputs'])),
            ]);
        }

        return response()->json([
            'message' => 'Device config fetched successfully.',
            'config' => $this->plantBedConfig($device),
        ]);
    }

    private function plantBedConfig(Device $device): array
    {
        $rule = $device->wateringRule;

        return [
            'device_uuid' => $device->uuid,
            'device_name' => $device->name,
            'device_type' => $device->device_type,
            'timezone' => $device->timezone ?? 'Asia/Dhaka',
            'watering_mode' => $rule?->watering_mode ?? 'schedule',
            'soil_moisture_threshold' => $rule?->soil_moisture_threshold,
            'max_watering_duration_seconds' => $rule?->max_watering_duration_seconds ?? 30,
            'cooldown_minutes' => $rule?->cooldown_minutes ?? 60,
            'local_manual_duration_seconds' => $rule?->local_manual_duration_seconds ?? 30,
            'schedules' => $device->wateringSchedules->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'is_enabled' => (bool) $schedule->is_enabled,
                    'day_of_week' => $schedule->day_of_week,
                    'time_of_day' => $schedule->time_of_day,
                    'duration_seconds' => $schedule->duration_seconds,
                ];
            })->values(),
        ];
    }

    private function smartFountainConfig(Device $device): array
    {
        return [
            'device_uuid' => $device->uuid,
            'device_name' => $device->name,
            'device_type' => $device->device_type,
            'timezone' => $device->timezone ?? 'Asia/Dhaka',
            'behavior_type' => 'persistent_state',
            'capabilities' => $device->capabilities->mapWithKeys(function ($capability) {
                return [
                    $capability->capability => [
                        'config' => $capability->config ?? [],
                        'state' => $capability->state ?? [],
                    ],
                ];
            }),
            'outputs' => $device->outputs->mapWithKeys(function ($output) {
                return [
                    $output->key => [
                        'type' => $output->type,
                        'name' => $output->name,
                        'config' => $output->config ?? [],
                        'state' => $output->state ?? [],
                        'last_changed_source' => $output->last_changed_source,
                        'last_changed_at' => $output->last_changed_at?->format('Y-m-d H:i:s'),
                    ],
                ];
            }),
            'safety' => [
                'pump_requires_water_level_ok' => true,
                'water_low_should_force_pump_off' => true,
            ],
            'commands' => [
                'supported' => [
                    'output_set',
                    'scene_apply',
                ],
                'execution_meaning' => 'executed means requested state was applied, not that the output finished running',
            ],
        ];
    }
}
