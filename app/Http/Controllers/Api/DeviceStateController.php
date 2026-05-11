<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\DeviceCommand;
use App\Models\DeviceReading;
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

            // Existing Plant Bed state fields.
            'valve_state' => ['nullable', 'in:open,closed'],
            'watering_state' => ['nullable', 'in:idle,watering'],
            'last_completed_command_id' => ['nullable', 'integer'],

            // Existing Plant Bed readings shape.
            'temperature' => ['nullable', 'numeric'],
            'humidity' => ['nullable', 'integer', 'min:0', 'max:100'],
            'soil_moisture' => ['nullable', 'integer', 'min:0', 'max:100'],

            // New platform state/readings shape for persistent state devices.
            'outputs' => ['nullable', 'array'],
            'outputs.*' => ['array'],
            'readings' => ['nullable', 'array'],
            'readings.*' => ['array'],
            'readings.*.value' => ['nullable', 'numeric'],
            'readings.*.unit' => ['nullable', 'string', 'max:50'],
            'readings.*.metadata' => ['nullable', 'array'],
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

        $lastReportedState = [
            'operation_state' => $validated['operation_state'],
            'reported_at' => now()->format('Y-m-d H:i:s'),
        ];

        if (array_key_exists('outputs', $validated)) {
            $lastReportedState['outputs'] = $validated['outputs'];
        }

        if (array_key_exists('readings', $validated)) {
            $lastReportedState['readings'] = $validated['readings'];
        }

        $device->update([
            'status' => in_array($device->status, ['claimed', 'claimed_pending_wifi'], true) ? 'active' : $device->status,
            'firmware_version' => $validated['firmware_version'] ?? $device->firmware_version,
            'last_seen_at' => now(),
            'last_reported_at' => now(),
            'last_reported_operation_state' => $validated['operation_state'],
            'last_reported_valve_state' => $validated['valve_state'] ?? null,
            'last_reported_watering_state' => $validated['watering_state'] ?? null,
            'last_reported_state' => $lastReportedState,
        ]);

        $storedReading = null;
        $hasLegacySensorReadingPayload = array_key_exists('temperature', $validated)
            || array_key_exists('humidity', $validated)
            || array_key_exists('soil_moisture', $validated);

        if ($hasLegacySensorReadingPayload) {
            $storedReading = SensorReading::create([
                'device_id' => $device->id,
                'temperature' => $validated['temperature'] ?? null,
                'humidity' => $validated['humidity'] ?? null,
                'soil_moisture' => $validated['soil_moisture'] ?? null,
                'recorded_at' => now(),
            ]);
        }

        $updatedOutputs = $this->syncPlatformOutputs($device, $validated['outputs'] ?? []);
        $storedDeviceReadings = $this->storePlatformReadings($device, $validated['readings'] ?? []);
        $acceptedCompletedCommandId = $this->markCompletedCommand($device, $validated['last_completed_command_id'] ?? null);

        return response()->json($this->buildResponsePayload(
            device: $device,
            storedReading: $storedReading,
            hasLegacySensorReadingPayload: $hasLegacySensorReadingPayload,
            updatedOutputs: $updatedOutputs,
            storedDeviceReadings: $storedDeviceReadings,
            acceptedCompletedCommandId: $acceptedCompletedCommandId,
            hasOutputsPayload: array_key_exists('outputs', $validated),
            hasReadingsPayload: array_key_exists('readings', $validated),
            hasValveStatePayload: array_key_exists('valve_state', $validated),
            hasWateringStatePayload: array_key_exists('watering_state', $validated),
        ));
    }

    private function buildResponsePayload(
        Device $device,
        ?SensorReading $storedReading,
        bool $hasLegacySensorReadingPayload,
        int $updatedOutputs,
        int $storedDeviceReadings,
        ?int $acceptedCompletedCommandId,
        bool $hasOutputsPayload,
        bool $hasReadingsPayload,
        bool $hasValveStatePayload,
        bool $hasWateringStatePayload,
    ): array {
        $freshDevice = $device->fresh();

        $state = [
            'operation_state' => $freshDevice->last_reported_operation_state,
            'last_reported_at' => $freshDevice->last_reported_at?->format('Y-m-d H:i:s'),
        ];

        if ($hasValveStatePayload) {
            $state['valve_state'] = $freshDevice->last_reported_valve_state;
        }

        if ($hasWateringStatePayload) {
            $state['watering_state'] = $freshDevice->last_reported_watering_state;
        }

        if ($hasOutputsPayload) {
            $state['outputs'] = $device->outputs()->get(['key', 'type', 'name', 'state', 'last_changed_source', 'last_changed_at']);
        }

        $response = [
            'message' => 'Device state synced successfully.',
            'device_id' => $device->id,
            'device_type' => $device->device_type,
            'state' => $state,
            'accepted_completed_command_id' => $acceptedCompletedCommandId,
        ];

        if ($hasOutputsPayload) {
            $response['platform_outputs_updated'] = $updatedOutputs;
        }

        if ($hasReadingsPayload) {
            $response['device_readings_stored'] = $storedDeviceReadings;
        }

        if ($hasLegacySensorReadingPayload) {
            $response['legacy_sensor_reading_stored'] = (bool) $storedReading;
            $response['legacy_sensor_reading_id'] = $storedReading?->id;

            // Deprecated MVP transition keys for older Plant Bed tests/firmware.
            $response['reading_stored'] = (bool) $storedReading;
            $response['reading_id'] = $storedReading?->id;
        }

        return $response;
    }

    private function syncPlatformOutputs(Device $device, array $outputs): int
    {
        $updatedCount = 0;

        foreach ($outputs as $key => $state) {
            if (! is_array($state)) {
                continue;
            }

            $deviceOutput = $device->outputs()
                ->where('key', $key)
                ->first();

            if (! $deviceOutput) {
                continue;
            }

            $source = $state['source'] ?? 'device_state';
            unset($state['source']);

            // Device state sync is the actual hardware report, so replace the
            // output state instead of merging. This prevents stale safety/debug
            // fields from staying after the device reports a clean state.
            $deviceOutput->update([
                'state' => $state,
                'last_changed_source' => $source,
                'last_changed_at' => now(),
            ]);

            $updatedCount++;
        }

        return $updatedCount;
    }

    private function storePlatformReadings(Device $device, array $readings): int
    {
        $storedCount = 0;

        foreach ($readings as $metric => $reading) {
            if (! is_array($reading) || ! array_key_exists('value', $reading)) {
                continue;
            }

            DeviceReading::create([
                'device_id' => $device->id,
                'metric' => $metric,
                'value' => $reading['value'],
                'unit' => $reading['unit'] ?? null,
                'metadata' => $reading['metadata'] ?? null,
                'recorded_at' => now(),
            ]);

            $storedCount++;
        }

        return $storedCount;
    }

    private function markCompletedCommand(Device $device, ?int $commandId): ?int
    {
        if (! $commandId) {
            return null;
        }

        $command = DeviceCommand::where('id', $commandId)
            ->where('device_id', $device->id)
            ->whereIn('status', ['pending', 'acknowledged'])
            ->first();

        if (! $command) {
            return null;
        }

        $command->update([
            'status' => 'executed',
            'acknowledged_at' => $command->acknowledged_at ?? now(),
            'executed_at' => now(),
        ]);

        if (in_array($command->command_type, ['valve_on', 'valve_off'], true)) {
            $log = $command->wateringLogs()->latest()->first();

            if ($log && in_array($log->status, ['requested', 'running'], true)) {
                $log->update([
                    'status' => 'completed',
                    'started_at' => $log->started_at ?? now(),
                    'ended_at' => now(),
                ]);
            }
        }

        return $command->id;
    }
}
