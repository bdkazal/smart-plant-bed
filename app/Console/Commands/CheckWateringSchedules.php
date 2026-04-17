<?php

namespace App\Console\Commands;

use App\Models\DeviceCommand;
use App\Models\WateringSchedule;
use App\Models\WateringLog;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckWateringSchedules extends Command
{
    protected $signature = 'watering:check-schedules {--day=} {--time=}';

    protected $description = 'Check watering schedules and create watering commands when due';

    public function handle(): int
    {
        $overrideDay = $this->option('day') ? (int) $this->option('day') : null;
        $overrideTime = $this->option('time')
            ? $this->normalizeTime((string) $this->option('time'))
            : null;

        if ($this->option('time') && ! $overrideTime) {
            $this->error('Invalid --time value. Use HH:MM or HH:MM:SS');
            return self::FAILURE;
        }

        if (! is_null($overrideDay) && ($overrideDay < 1 || $overrideDay > 7)) {
            $this->error('Invalid --day value. Use 1 to 7 (1=Monday, 7=Sunday).');
            return self::FAILURE;
        }

        $schedules = WateringSchedule::with(['device.wateringRule'])
            ->where('is_enabled', true)
            ->get();

        $createdCount = 0;

        foreach ($schedules as $schedule) {
            $device = $schedule->device;

            if (! $device || ! $device->wateringRule) {
                continue;
            }

            if ($device->wateringRule->watering_mode !== 'schedule') {
                continue;
            }

            $deviceTimezone = $device->timezone ?: 'Asia/Dhaka';
            $deviceNow = Carbon::now($deviceTimezone);

            $currentDayOfWeek = $overrideDay ?? $deviceNow->dayOfWeekIso;
            $currentTime = $overrideTime ?? $deviceNow->format('H:i:00');

            if (
                (int) $schedule->day_of_week !== (int) $currentDayOfWeek ||
                $schedule->time_of_day !== $currentTime
            ) {
                continue;
            }

            $hasActiveCommand = DeviceCommand::where('device_id', $device->id)
                ->where('command_type', 'valve_on')
                ->whereIn('status', ['pending', 'acknowledged'])
                ->exists();

            if ($hasActiveCommand) {
                continue;
            }

            $command = DeviceCommand::create([
                'device_id' => $device->id,
                'command_type' => 'valve_on',
                'payload' => [
                    'duration_seconds' => (int) $schedule->duration_seconds,
                ],
                'status' => 'pending',
                'issued_at' => now(),
            ]);

            WateringLog::create([
                'device_id' => $device->id,
                'device_command_id' => $command->id,
                'trigger_type' => 'schedule',
                'duration_seconds' => (int) $schedule->duration_seconds,
                'status' => 'requested',
                'notes' => 'Scheduled watering triggered by Laravel scheduler.',
            ]);

            $createdCount++;
        }

        $this->info("Created {$createdCount} scheduled watering command(s).");

        return self::SUCCESS;
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
