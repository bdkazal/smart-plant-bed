<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\DeviceCommand;
use App\Models\SensorReading;
use App\Models\WateringLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceReadingController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_uuid' => ['required', 'uuid'],
            'temperature' => ['nullable', 'numeric'],
            'humidity' => ['nullable', 'integer', 'min:0', 'max:100'],
            'soil_moisture' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $deviceKey = $request->header('X-DEVICE-KEY');

        if (!$deviceKey) {
            return response()->json([
                'message' => 'Missing device API key.',
            ], 401);
        }

        $device = Device::with('wateringRule')
            ->where('uuid', $validated['device_uuid'])
            ->where('api_key', $deviceKey)
            ->first();

        if (!$device) {
            return response()->json([
                'message' => 'Invalid device credentials.',
            ], 401);
        }

        $reading = SensorReading::create([
            'device_id' => $device->id,
            'temperature' => $validated['temperature'] ?? null,
            'humidity' => $validated['humidity'] ?? null,
            'soil_moisture' => $validated['soil_moisture'] ?? null,
            'recorded_at' => now(),
        ]);

        $device->update([
            'last_seen_at' => now(),
        ]);

        $autoWateringTriggered = false;

        $rule = $device->wateringRule;

        if (
            $rule &&
            $rule->auto_mode_enabled &&
            !is_null($reading->soil_moisture) &&
            $reading->soil_moisture <= $rule->soil_moisture_threshold
        ) {
            $hasActiveCommand = DeviceCommand::where('device_id', $device->id)
                ->whereIn('status', ['pending', 'acknowledged'])
                ->where('command_type', 'valve_on')
                ->exists();

            $lastCompletedAutoLog = WateringLog::where('device_id', $device->id)
                ->where('trigger_type', 'auto')
                ->where('status', 'completed')
                ->latest('ended_at')
                ->first();

            $cooldownPassed = true;

            if ($lastCompletedAutoLog && $lastCompletedAutoLog->ended_at) {
                $cooldownPassed = $lastCompletedAutoLog->ended_at
                    ->addMinutes($rule->cooldown_minutes)
                    ->isPast();
            }

            if (!$hasActiveCommand && $cooldownPassed) {
                $command = DeviceCommand::create([
                    'device_id' => $device->id,
                    'command_type' => 'valve_on',
                    'payload' => [
                        'duration_seconds' => (int) $rule->max_watering_duration_seconds,
                    ],
                    'status' => 'pending',
                    'issued_at' => now(),
                ]);

                WateringLog::create([
                    'device_id' => $device->id,
                    'device_command_id' => $command->id,
                    'trigger_type' => 'auto',
                    'duration_seconds' => (int) $rule->max_watering_duration_seconds,
                    'status' => 'requested',
                    'notes' => 'Auto watering triggered by low soil moisture.',
                ]);

                $autoWateringTriggered = true;
            }
        }

        return response()->json([
            'message' => 'Reading stored successfully.',
            'device_id' => $device->id,
            'reading_id' => $reading->id,
            'auto_watering_triggered' => $autoWateringTriggered,
        ], 201);
    }
}
