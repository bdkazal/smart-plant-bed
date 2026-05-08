<?php

namespace App\Services;

use App\Models\Device;
use App\Models\DeviceCommand;
use App\Models\WateringLog;

class DeviceCommandLifecycleService
{
    public function cleanupStaleCommands(Device $device): void
    {
        $this->expireOldPendingCommands($device);
        $this->failTimedOutAcknowledgedCommands($device);
    }

    private function expireOldPendingCommands(Device $device): void
    {
        $expiredPendingCommands = DeviceCommand::where('device_id', $device->id)
            ->where('status', 'pending')
            ->where('issued_at', '<', now()->subMinute())
            ->get();

        foreach ($expiredPendingCommands as $command) {
            $command->update([
                'status' => 'expired',
            ]);

            if ($this->isWateringCommand($command)) {
                WateringLog::where('device_command_id', $command->id)
                    ->where('status', 'requested')
                    ->update([
                        'status' => 'failed',
                        'ended_at' => now(),
                        'notes' => 'Command expired before device confirmation.',
                    ]);
            }
        }
    }

    private function failTimedOutAcknowledgedCommands(Device $device): void
    {
        $acknowledgedCommands = DeviceCommand::where('device_id', $device->id)
            ->where('status', 'acknowledged')
            ->get();

        foreach ($acknowledgedCommands as $command) {
            if (! $this->hasAcknowledgedCommandTimedOut($command)) {
                continue;
            }

            $command->update([
                'status' => 'failed',
            ]);

            if ($command->command_type === 'valve_on') {
                $this->failWateringCommandLog($command, 'Device acknowledged command but no completion was received before timeout.');
            }

            if ($command->command_type === 'valve_off') {
                $this->failActiveWateringLog($device, 'Stop command timed out before completion.');
            }
        }
    }

    private function hasAcknowledgedCommandTimedOut(DeviceCommand $command): bool
    {
        if (! $command->acknowledged_at) {
            return false;
        }

        $timeoutSeconds = match ($command->command_type) {
            'valve_on' => ((int) data_get($command->payload, 'duration_seconds', 0)) + 60,
            'valve_off' => 60,
            'output_set' => 60,
            'scene_apply' => 90,
            default => 60,
        };

        return $command->acknowledged_at
            ->copy()
            ->addSeconds(max($timeoutSeconds, 60))
            ->isPast();
    }

    private function isWateringCommand(DeviceCommand $command): bool
    {
        return in_array($command->command_type, ['valve_on', 'valve_off'], true);
    }

    private function failWateringCommandLog(DeviceCommand $command, string $note): void
    {
        $log = WateringLog::where('device_command_id', $command->id)->latest('id')->first();

        if (! $log || ! in_array($log->status, ['requested', 'running'], true)) {
            return;
        }

        $log->update([
            'status' => 'failed',
            'ended_at' => now(),
            'notes' => trim(($log->notes ? $log->notes . ' ' : '') . $note),
        ]);
    }

    private function failActiveWateringLog(Device $device, string $note): void
    {
        $activeLog = WateringLog::where('device_id', $device->id)
            ->whereIn('status', ['requested', 'running'])
            ->latest('id')
            ->first();

        if (! $activeLog) {
            return;
        }

        $activeLog->update([
            'status' => 'failed',
            'ended_at' => now(),
            'notes' => trim(($activeLog->notes ? $activeLog->notes . ' ' : '') . $note),
        ]);
    }
}
