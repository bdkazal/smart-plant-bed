<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceHeartbeatController extends Controller
{
    public function store(Request $request): JsonResponse
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

        $device->update([
            'last_seen_at' => now(),
        ]);

        return response()->json([
            'message' => 'Heartbeat received successfully.',
            'device_id' => $device->id,
            'last_seen_at' => $device->fresh()->last_seen_at?->format('Y-m-d H:i:s'),
        ]);
    }
}
