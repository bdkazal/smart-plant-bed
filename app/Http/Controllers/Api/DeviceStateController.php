<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\DeviceCommand;
use App\Models\SensorReading;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceStateController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_uuid' => ['required', 'uuid'],
            'device_type' => ['required', 'string', 'max:100'],
            'reported_at' => ['nullable', 'date'],
            'firmware_version' => ['nullable', 'string', 'max:100'],
            'operation_state' => ['required', 'string', 'max:100'],

            'valve_state' => ['nullable', 'in:open,closed'],
            'watering_state' => ['nullable', 'in:idle,watering'],
            'last_completed_command_id' => ['nullable', 'integer'],

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

        $device = Device::where('uuid', $validated['device_uuid'])
            ->where('api_key', $deviceKey)
            ->first();

        if (! $device) {
            return response()->json([
                'message' => 'Invalid device credentials.',
            ], 401);
        }

        if ($device->device_type !== $validated['device_type']) {
            return response()->json([
                'message' => 'Device type mismatch.',
            ], 409);
        }

        $device->update([
            'firmware_version' => $validated['firmware_version'] ?? $device->firmware_version,
            'last_seen_at' => now(),
            'last_reported_at' => $validated['reported_at'] ?? now(),
            'last_reported_operation_state' => $validated['operation_state'],
            'last_reported_valve_state' => $validated['valve_state'] ?? null,
            'last_reported_watering_state' => $validated['watering_state'] ?? null,
        ]);

        $storedReading = null;

        if (
            array_key_exists('temperature', $validated) ||
            array_key_exists('humidity', $validated) ||
            array_key_exists('soil_moisture', $validated)
        ) {
            $storedReading = SensorReading::create([
                'device_id' => $device->id,
                'temperature' => $validated['temperature'] ?? null,
                'humidity' => $validated['humidity'] ?? null,
                'soil_moisture' => $validated['soil_moisture'] ?? null,
                'recorded_at' => now(),
            ]);
        }

        $acceptedCompletedCommandId = null;

        if (! empty($validated['last_completed_command_id'])) {
            $command = DeviceCommand::where('id', $validated['last_completed_command_id'])
                ->where('device_id', $device->id)
                ->whereIn('status', ['pending', 'acknowledged'])
                ->first();

            if ($command) {
                $command->update([
                    'status' => 'executed',
                    'acknowledged_at' => $command->acknowledged_at ?? now(),
                    'executed_at' => now(),
                ]);

                $log = $command->wateringLogs()->latest()->first();

                if ($log && in_array($log->status, ['requested', 'running'], true)) {
                    $log->update([
                        'status' => 'completed',
                        'started_at' => $log->started_at ?? now(),
                        'ended_at' => now(),
                    ]);
                }

                $acceptedCompletedCommandId = $command->id;
            }
        }

        return response()->json([
            'message' => 'Device state synced successfully.',
            'device_id' => $device->id,
            'state' => [
                'operation_state' => $device->fresh()->last_reported_operation_state,
                'valve_state' => $device->fresh()->last_reported_valve_state,
                'watering_state' => $device->fresh()->last_reported_watering_state,
                'last_reported_at' => $device->fresh()->last_reported_at?->format('Y-m-d H:i:s'),
            ],
            'reading_stored' => (bool) $storedReading,
            'reading_id' => $storedReading?->id,
            'accepted_completed_command_id' => $acceptedCompletedCommandId,
        ]);
    }
}
