<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\DeviceCommand;

class DeviceCommandController extends Controller
{
    public function index(Request $request): JsonResponse
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

        $device = Device::where('uuid', $validated['device_uuid'])
            ->where('api_key', $deviceKey)
            ->first();

        if (!$device) {
            return response()->json([
                'message' => 'Invalid device credentials.',
            ], 401);
        }

        // $command = $device->deviceCommands()
        //     ->where('status', 'pending')
        //     ->latest()
        //     ->first();

        $command = $device->deviceCommands()
            ->where('status', 'pending')
            ->oldest('id')
            ->first();

        if (!$command) {
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

        if (!$deviceKey) {
            return response()->json([
                'message' => 'Missing device API key.',
            ], 401);
        }

        $device = Device::where('uuid', $validated['device_uuid'])
            ->where('api_key', $deviceKey)
            ->first();

        if (!$device) {
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

            if ($log) {
                $log->update([
                    'status' => 'running',
                    'started_at' => $log->started_at ?? now(),
                ]);
            }
        }

        if ($validated['status'] === 'executed') {
            $command->update([
                'status' => 'executed',
                'acknowledged_at' => $command->acknowledged_at ?? now(),
                'executed_at' => now(),
            ]);

            if ($log) {
                $log->update([
                    'status' => 'completed',
                    'started_at' => $log->started_at ?? now(),
                    'ended_at' => now(),
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
}
