<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\DeviceCommand;
use App\Models\WateringLog;
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

        if ($command->device_id !== $device->id) {
            return response()->json([
                'message' => 'This command does not belong to the authenticated device.',
            ], 403);
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
            'status' => $command->fresh()->status,
        ]);
    }

    private function cleanupStaleCommands(Device $device): void
    {
        $expiredPendingCommands = DeviceCommand::where('device_id', $device->id)
            ->where('status', 'pending')
            ->where('issued_at', '<', now()->subMinute())
            ->get();

        foreach ($expiredPendingCommands as $command) {
            $command->update([
                'status' => 'expired',
            ]);

            WateringLog::where('device_command_id', $command->id)
                ->where('status', 'requested')
                ->update([
                    'status' => 'failed',
                    'ended_at' => now(),
                    'notes' => 'Command expired before device confirmation.',
                ]);
        }

        $acknowledgedCommands = DeviceCommand::where('device_id', $device->id)
            ->where('status', 'acknowledged')
            ->get();

        foreach ($acknowledgedCommands as $command) {
            $timedOut = false;

            if ($command->command_type === 'valve_on') {
                $durationSeconds = (int) data_get($command->payload, 'duration_seconds', 0);
                $timeoutSeconds = max($durationSeconds, 0) + 60;

                $timedOut = $command->acknowledged_at
                    && $command->acknowledged_at->copy()->addSeconds($timeoutSeconds)->isPast();
            }

            if ($command->command_type === 'valve_off') {
                $timedOut = $command->acknowledged_at
                    && $command->acknowledged_at->copy()->addSeconds(60)->isPast();
            }

            if (! $timedOut) {
                continue;
            }

            $command->update([
                'status' => 'failed',
            ]);

            $log = WateringLog::where('device_command_id', $command->id)->latest('id')->first();

            if ($log && in_array($log->status, ['requested', 'running'], true)) {
                $log->update([
                    'status' => 'failed',
                    'ended_at' => now(),
                    'notes' => trim(($log->notes ? $log->notes . ' ' : '') . 'Device acknowledged command but no completion was received before timeout.'),
                ]);
            }

            if ($command->command_type === 'valve_off') {
                $activeLog = WateringLog::where('device_id', $device->id)
                    ->whereIn('status', ['requested', 'running'])
                    ->latest('id')
                    ->first();

                if ($activeLog) {
                    $activeLog->update([
                        'status' => 'failed',
                        'ended_at' => now(),
                        'notes' => trim(($activeLog->notes ? $activeLog->notes . ' ' : '') . 'Stop command timed out before completion.'),
                    ]);
                }
            }
        }
    }
}
