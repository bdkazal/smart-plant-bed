<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SmartFountainStatusController extends Controller
{
    public function __invoke(Device $device): JsonResponse
    {
        $user = Auth::user();

        if (! $user || $device->user_id !== $user->id) {
            abort(403);
        }

        if (! $device->isSmartFountain()) {
            abort(404);
        }

        $device->load([
            'outputs',
            'platformReadings' => fn ($query) => $query->latest()->limit(10),
            'deviceCommands' => fn ($query) => $query->latest()->limit(20),
        ]);

        $latestReadings = $device->platformReadings
            ->groupBy('metric')
            ->map(fn ($readings) => $readings->first());

        $outputs = $device->outputs->mapWithKeys(function ($output) use ($device) {
            $latestCommand = $device->deviceCommands->first(function ($command) use ($output) {
                return $command->command_type === 'output_set'
                    && data_get($command->payload, 'output') === $output->key;
            });

            return [
                $output->key => [
                    'key' => $output->key,
                    'type' => $output->type,
                    'name' => $output->name,
                    'state' => $output->state ?? [],
                    'last_changed_source' => $output->last_changed_source,
                    'last_changed_at' => $output->last_changed_at?->format('Y-m-d H:i:s'),
                    'last_command' => $latestCommand ? [
                        'id' => $latestCommand->id,
                        'command_type' => $latestCommand->command_type,
                        'status' => $latestCommand->status,
                        'status_label' => $this->commandStatusLabel($latestCommand->status),
                        'issued_at' => $latestCommand->issued_at?->format('Y-m-d H:i:s'),
                        'acknowledged_at' => $latestCommand->acknowledged_at?->format('Y-m-d H:i:s'),
                        'executed_at' => $latestCommand->executed_at?->format('Y-m-d H:i:s'),
                    ] : null,
                ],
            ];
        });

        return response()->json([
            'device' => [
                'id' => $device->id,
                'name' => $device->name,
                'display_type' => $device->displayType(),
                'status' => $device->status,
                'status_label' => ucfirst(str_replace('_', ' ', $device->status)),
                'location_label' => $device->location_label ?? 'N/A',
                'timezone' => $device->timezone ?? 'Asia/Dhaka',
                'last_seen_human' => $device->last_seen_at?->diffForHumans() ?? 'Never',
                'is_online' => $this->isDeviceOnline($device),
            ],
            'readings' => [
                'water_low' => $latestReadings->get('water_low')?->value,
                'water_level_percent' => $latestReadings->get('water_level_percent')?->value,
            ],
            'outputs' => $outputs,
        ]);
    }

    private function isDeviceOnline(Device $device): bool
    {
        return $device->last_seen_at?->gt(now()->subSeconds(20)) ?? false;
    }

    private function commandStatusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Waiting for device',
            'acknowledged' => 'Applying',
            'executed' => 'Applied',
            'failed' => 'Failed',
            'expired' => 'Expired',
            default => ucfirst($status),
        };
    }
}
