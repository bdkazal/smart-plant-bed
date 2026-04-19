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

        if (! $deviceKey) {
            return response()->json([
                'message' => 'Missing device API key.',
            ], 401);
        }

        $device = Device::with('wateringRule')
            ->where('uuid', $validated['device_uuid'])
            ->where('api_key', $deviceKey)
            ->first();

        if (! $device) {
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
            $rule->watering_mode === 'auto' &&
            ! is_null($reading->soil_moisture) &&
            ! is_null($rule->soil_moisture_threshold) &&
            $reading->soil_moisture <= $rule->soil_moisture_threshold
        ) {
            $this->expireStalePendingCommands($device);

            $hasActiveCommand = DeviceCommand::where('device_id', $device->id)
                ->where('command_type', 'valve_on')
                ->whereIn('status', ['pending', 'acknowledged'])
                ->exists();

            $lastCompletedAutoLog = WateringLog::where('device_id', $device->id)
                ->where('trigger_type', 'auto')
                ->where('status', 'completed')
                ->latest('ended_at')
                ->first();

            $cooldownPassed = true;

            if ($lastCompletedAutoLog && $lastCompletedAutoLog->ended_at) {
                $cooldownPassed = $lastCompletedAutoLog->ended_at
                    ->copy()
                    ->addMinutes((int) $rule->cooldown_minutes)
                    ->isPast();
            }

            if (! $hasActiveCommand && $cooldownPassed) {
                $durationSeconds = (int) ($rule->max_watering_duration_seconds ?? 30);

                $command = DeviceCommand::create([
                    'device_id' => $device->id,
                    'command_type' => 'valve_on',
                    'payload' => [
                        'duration_seconds' => $durationSeconds,
                    ],
                    'status' => 'pending',
                    'issued_at' => now(),
                ]);

                WateringLog::create([
                    'device_id' => $device->id,
                    'device_command_id' => $command->id,
                    'trigger_type' => 'auto',
                    'duration_seconds' => $durationSeconds,
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

    private function expireStalePendingCommands(Device $device): void
    {
        $expiredCommands = DeviceCommand::where('device_id', $device->id)
            ->where('status', 'pending')
            ->where('issued_at', '<', now()->subMinute())
            ->get();

        foreach ($expiredCommands as $expiredCommand) {
            $expiredCommand->update([
                'status' => 'expired',
            ]);

            WateringLog::where('device_command_id', $expiredCommand->id)
                ->where('status', 'requested')
                ->update([
                    'status' => 'failed',
                    'notes' => 'Command expired before device confirmation.',
                ]);
        }
    }
}
