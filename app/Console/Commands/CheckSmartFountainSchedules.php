<?php

namespace App\Console\Commands;

use App\Models\Device;
use App\Models\DeviceCommand;
use App\Models\DeviceScheduleRange;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckSmartFountainSchedules extends Command
{
    protected $signature = 'smart-fountain:check-schedules {--time=}';

    protected $description = 'Check Smart Fountain daily timeline blocks and create scene commands when due';

    public function handle(): int
    {
        $overrideTime = $this->option('time')
            ? $this->normalizeTime((string) $this->option('time'))
            : null;

        if ($this->option('time') && ! $overrideTime) {
            $this->error('Invalid --time value. Use HH:MM or HH:MM:SS');
            return self::FAILURE;
        }

        $schedules = DeviceScheduleRange::with(['device.outputs', 'startScene'])
            ->where('is_enabled', true)
            ->whereIn('period_key', ['day', 'evening', 'night'])
            ->get();

        $createdCount = 0;
        $skippedOfflineCount = 0;

        foreach ($schedules as $schedule) {
            $device = $schedule->device;

            if (! $device || ! $device->isSmartFountain() || $device->status !== 'active') {
                continue;
            }

            $deviceTimezone = $device->timezone ?: 'Asia/Dhaka';
            $deviceNow = Carbon::now($deviceTimezone);

            $currentTime = $overrideTime ?? $deviceNow->format('H:i:00');
            $currentDate = $deviceNow->toDateString();

            if ($currentTime !== $schedule->start_time) {
                continue;
            }

            if ($schedule->last_started_on?->toDateString() === $currentDate) {
                continue;
            }

            if (! $this->isDeviceOnline($device)) {
                $skippedOfflineCount++;
                continue;
            }

            if ($this->queueSceneCommand($schedule)) {
                $schedule->update([
                    'days_of_week' => [1, 2, 3, 4, 5, 6, 7],
                    'last_started_on' => $currentDate,
                    'last_started_at' => now(),
                ]);

                $createdCount++;
            }
        }

        $this->info("Created {$createdCount} Smart Fountain daily timeline command(s). Skipped {$skippedOfflineCount} offline device schedule(s).");

        return self::SUCCESS;
    }

    private function queueSceneCommand(DeviceScheduleRange $schedule): bool
    {
        $scene = $schedule->startScene;
        $device = $schedule->device;

        if (! $scene || ! $device) {
            return false;
        }

        $existingCommand = DeviceCommand::where('device_id', $device->id)
            ->where('command_type', 'scene_apply')
            ->whereIn('status', ['pending', 'acknowledged'])
            ->where('payload->schedule_range_id', $schedule->id)
            ->where('payload->schedule_phase', 'start')
            ->exists();

        if ($existingCommand) {
            return false;
        }

        $outputs = collect($scene->outputs ?? [])
            ->filter(fn ($state, $outputKey) => is_string($outputKey) && is_array($state))
            ->filter(fn ($state, $outputKey) => $device->outputs()->where('key', $outputKey)->exists())
            ->all();

        if (empty($outputs)) {
            return false;
        }

        DeviceCommand::create([
            'device_id' => $device->id,
            'command_type' => 'scene_apply',
            'payload' => [
                'scene_id' => $scene->id,
                'scene_name' => $scene->name,
                'source' => 'schedule:' . $schedule->id . ':' . $schedule->period_key,
                'schedule_range_id' => $schedule->id,
                'schedule_name' => $schedule->name,
                'schedule_period' => $schedule->period_key,
                'schedule_phase' => 'start',
                'repeat' => 'daily',
                'outputs' => $outputs,
            ],
            'status' => 'pending',
            'issued_at' => now(),
        ]);

        return true;
    }

    private function isDeviceOnline(Device $device): bool
    {
        return $device->last_seen_at?->gt(now()->subSeconds(20)) ?? false;
    }

    private function normalizeTime(string $time): ?string
    {
        $time = trim($time);

        if (preg_match('/^\d{2}:\d{2}$/', $time)) {
            return $time . ':00';
        }

        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
            return $time;
        }

        return null;
    }
}
