<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\DeviceCommand;
use App\Models\WateringLog;
use App\Services\DeviceCommandLifecycleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceCommandController extends Controller
{
    public function index(Request $request): JsonResponse
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

        $device = Device::where('uuid', $validated['device_uuid'])
            ->where('api_key', $deviceKey)
            ->first();

        if (! $device) {
            return response()->json([
                'message' => 'Invalid device credentials.',
            ], 401);
        }

        $this->markDeviceSeen($device);
        $this->cleanupStaleCommands($device);

        $command = $device->deviceCommands()
            ->where('status', 'pending')
            ->oldest('id')
            ->first();

        if (! $command) {
            return response()->json([
                'message' => 'No pending commands.',
                'command' => null,
            ]);
        }

        return response()->json([
            'message' => 'Pending command found.',
            'command' => [
                'id' => $command->id,
                'command_type' => $command->command_type,
                'payload' => $command->payload,
                'status' => $command->status,
                'issued_at' => $command->issued_at?->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    public function ack(Request $request, DeviceCommand $command): JsonResponse
    {
        $validated = $request->validate([
            'device_uuid' => ['required', 'uuid'],
            'status' => ['required', 'in:acknowledged,executed,failed'],
            'message' => ['nullable', 'string', 'max:1000'],
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

        $this->markDeviceSeen($device);

        if ($command->device_id !== $device->id) {
            return response()->json([
                'message' => 'This command does not belong to the authenticated device.',
            ], 403);
        }

        if (in_array($command->status, ['expired', 'failed', 'executed'], true)) {
            return response()->json([
                'message' => 'Command is already closed and cannot be updated.',
                'command_id' => $command->id,
                'status' => $command->status,
            ], 409);
        }

        if ($command->status === 'pending' && ! in_array($validated['status'], ['acknowledged', 'failed'], true)) {
            return response()->json([
                'message' => 'Pending command must be acknowledged or failed before it can be executed.',
                'command_id' => $command->id,
                'current_status' => $command->status,
                'requested_status' => $validated['status'],
            ], 409);
        }

        if ($command->status === 'acknowledged' && ! in_array($validated['status'], ['executed', 'failed'], true)) {
            return response()->json([
                'message' => 'Acknowledged command can only be executed or failed.',
                'command_id' => $command->id,
                'current_status' => $command->status,
                'requested_status' => $validated['status'],
            ], 409);
        }

        $log = $command->wateringLogs()->latest()->first();

        if ($validated['status'] === 'acknowledged') {
            $command->update([
                'status' => 'acknowledged',
                'acknowledged_at' => $command->acknowledged_at ?? now(),
            ]);

            if ($command->command_type === 'valve_on' && $log) {
                $log->update([
                    'status' => 'running',
                    'started_at' => $log->started_at ?? now(),
                ]);
            }

            if ($command->command_type === 'valve_off') {
                $activeLog = WateringLog::where('device_id', $device->id)
                    ->whereIn('status', ['requested', 'running'])
                    ->latest('id')
                    ->first();

                if ($activeLog) {
                    $activeLog->update([
                        'notes' => trim(($activeLog->notes ? $activeLog->notes . ' ' : '') . 'Stop request acknowledged by device.'),
                    ]);
                }
            }
        }

        if ($validated['status'] === 'executed') {
            $command->update([
                'status' => 'executed',
                'acknowledged_at' => $command->acknowledged_at ?? now(),
                'executed_at' => now(),
            ]);

            if ($command->command_type === 'valve_on' && $log) {
                $log->update([
                    'status' => 'completed',
                    'started_at' => $log->started_at ?? now(),
                    'ended_at' => now(),
                ]);
            }

            if ($command->command_type === 'valve_off') {
                $activeLog = WateringLog::where('device_id', $device->id)
                    ->whereIn('status', ['requested', 'running'])
                    ->latest('id')
                    ->first();

                if ($activeLog) {
                    $activeLog->update([
                        'status' => 'completed',
                        'ended_at' => now(),
                        'notes' => trim(($activeLog->notes ? $activeLog->notes . ' ' : '') . 'Stopped by valve_off command.'),
                    ]);
                }

                DeviceCommand::where('device_id', $device->id)
                    ->where('command_type', 'valve_on')
                    ->whereIn('status', ['pending', 'acknowledged'])
                    ->update([
                        'status' => 'executed',
                        'acknowledged_at' => now(),
                        'executed_at' => now(),
                    ]);
            }

            if ($command->command_type === 'output_set') {
                $this->applyOutputSetCommand($device, $command);
            }
        }

        if ($validated['status'] === 'failed') {
            $command->update([
                'status' => 'failed',
                'acknowledged_at' => $command->acknowledged_at ?? now(),
            ]);

            if ($log) {
                $log->update([
                    'status' => 'failed',
                    'ended_at' => now(),
                    'notes' => $validated['message'] ?? $log->notes,
                ]);
            }
        }

        return response()->json([
            'message' => 'Command status updated successfully.',
            'command_id' => $command->id,
            'command_type' => $command->command_type,
            'status' => $command->fresh()->status,
            'execution_meaning' => $this->executionMeaning($command),
        ]);
    }

    private function applyOutputSetCommand(Device $device, DeviceCommand $command): void
    {
        $outputKey = data_get($command->payload, 'output');
        $state = data_get($command->payload, 'state');

        if (! is_string($outputKey) || ! is_array($state)) {
            return;
        }

        $deviceOutput = $device->outputs()
            ->where('key', $outputKey)
            ->first();

        if (! $deviceOutput) {
            return;
        }

        $deviceOutput->update([
            'state' => array_merge($deviceOutput->state ?? [], $state),
            'last_changed_source' => data_get($command->payload, 'source', 'device_ack'),
            'last_changed_at' => now(),
        ]);
    }

    private function markDeviceSeen(Device $device): void
    {
        $device->update([
            'status' => in_array($device->status, ['claimed', 'claimed_pending_wifi'], true) ? 'active' : $device->status,
            'last_seen_at' => now(),
        ]);
    }

    private function executionMeaning(DeviceCommand $command): string
    {
        return match ($command->command_type) {
            'valve_on', 'valve_off' => 'For timed watering commands, executed means the watering action completed.',
            'output_set', 'scene_apply' => 'For persistent state commands, executed means the requested state was applied.',
            default => 'Executed means the device completed the command according to its command type.',
        };
    }

    private function cleanupStaleCommands(Device $device): void
    {
        app(DeviceCommandLifecycleService::class)->cleanupStaleCommands($device);
    }
}
