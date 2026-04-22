<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceCommand;
use App\Models\WateringLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DeviceController extends Controller
{
    public function index(): View
    {
        $devices = Auth::user()
            ->devices()
            ->latest()
            ->get();

        return view('devices.index', compact('devices'));
    }

    public function show(Device $device): View
    {
        $this->authorizeDevice($device);

        $this->cleanupStaleCommands($device);

        $today = now($device->timezone ?? config('app.timezone'))->dayOfWeekIso;

        $device->load([
            'wateringRule',
            'wateringSchedules' => fn($query) => $query
                ->where('is_enabled', true)
                ->orderByRaw("
                    CASE
                        WHEN day_of_week >= ? THEN day_of_week - ?
                        ELSE day_of_week + 7 - ?
                    END
                ", [$today, $today, $today])
                ->orderBy('time_of_day'),
            'sensorReadings' => fn($query) => $query->latest()->limit(5),
            'wateringLogs' => fn($query) => $query->latest()->limit(5),
            'deviceCommands' => fn($query) => $query->latest()->limit(5),
        ]);

        $latestReading = $device->sensorReadings->first();
        $manualMaxDuration = $device->wateringRule?->max_watering_duration_seconds ?? 300;

        $activeValveOnCommand = DeviceCommand::where('device_id', $device->id)
            ->where('command_type', 'valve_on')
            ->whereIn('status', ['pending', 'acknowledged'])
            ->latest('id')
            ->first();

        $activeValveOffCommand = DeviceCommand::where('device_id', $device->id)
            ->where('command_type', 'valve_off')
            ->whereIn('status', ['pending', 'acknowledged'])
            ->latest('id')
            ->first();

        $latestActiveWateringLog = $device->wateringLogs()
            ->whereIn('status', ['requested', 'running'])
            ->latest('id')
            ->first();

        $manualWateringState = 'idle';

        if ($activeValveOffCommand) {
            $manualWateringState = 'stopping';
        } elseif ($activeValveOnCommand?->status === 'pending') {
            $manualWateringState = 'pending';
        } elseif ($activeValveOnCommand?->status === 'acknowledged') {
            $manualWateringState = 'running';
        }

        $enabledScheduleCount = $device->wateringSchedules->count();

        return view('devices.show', compact(
            'device',
            'latestReading',
            'latestActiveWateringLog',
            'manualWateringState',
            'manualMaxDuration',
            'enabledScheduleCount'
        ));
    }

    public function automation(Device $device): View
    {
        $this->authorizeDevice($device);

        $this->cleanupStaleCommands($device);

        $device->load([
            'wateringRule',
            'sensorReadings' => fn($query) => $query->latest()->limit(5),
            'wateringSchedules' => fn($query) => $query
                ->orderBy('day_of_week')
                ->orderBy('time_of_day'),
        ]);

        $latestReading = $device->sensorReadings->first();
        $timezoneOptions = $this->getTimezoneOptions();
        $enabledScheduleCount = $device->wateringSchedules->where('is_enabled', true)->count();

        return view('devices.automation', compact(
            'device',
            'latestReading',
            'timezoneOptions',
            'enabledScheduleCount'
        ));
    }

    public function history(Device $device): View
    {
        $this->authorizeDevice($device);

        $this->cleanupStaleCommands($device);

        $wateringLogs = WateringLog::where('device_id', $device->id)
            ->latest()
            ->paginate(5, ['*'], 'logs_page');

        $deviceCommands = DeviceCommand::where('device_id', $device->id)
            ->latest()
            ->paginate(5, ['*'], 'commands_page');

        return view('devices.history', compact(
            'device',
            'wateringLogs',
            'deviceCommands'
        ));
    }

    public function updateSettings(Request $request, Device $device): RedirectResponse
    {
        $this->authorizeDevice($device);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'location_label' => ['nullable', 'string', 'max:100'],
            'timezone' => ['required', 'timezone'],
            'watering_mode' => ['required', 'in:auto,schedule'],
            'soil_moisture_threshold' => ['required', 'integer', 'min:0', 'max:100'],
            'max_watering_duration_seconds' => ['required', 'integer', 'min:1', 'max:300'],
            'cooldown_minutes' => ['required', 'integer', 'min:0', 'max:1440'],
            'local_manual_duration_seconds' => ['required', 'integer', 'min:1', 'max:300'],
        ]);

        $latestReading = $device->sensorReadings()->latest()->first();

        if (
            $validated['watering_mode'] === 'auto' &&
            (
                ! $latestReading ||
                is_null($latestReading->soil_moisture)
            )
        ) {
            return back()->withErrors([
                'watering_mode' => 'Auto mode requires a current soil moisture reading. Select schedule mode or reconnect the moisture sensor and send a valid reading first.',
            ]);
        }

        $device->update([
            'name' => $validated['name'],
            'location_label' => $validated['location_label'] ?: null,
            'timezone' => $validated['timezone'],
        ]);

        $device->wateringRule()->updateOrCreate(
            ['device_id' => $device->id],
            [
                'watering_mode' => $validated['watering_mode'],
                'soil_moisture_threshold' => $validated['soil_moisture_threshold'],
                'max_watering_duration_seconds' => $validated['max_watering_duration_seconds'],
                'cooldown_minutes' => $validated['cooldown_minutes'],
                'local_manual_duration_seconds' => $validated['local_manual_duration_seconds'],
                'auto_mode_enabled' => $validated['watering_mode'] === 'auto',
            ]
        );

        return redirect()
            ->route('devices.show', $device)
            ->with('success', 'Device settings updated successfully.');
    }

    public function waterNow(Request $request, Device $device): RedirectResponse
    {
        $this->authorizeDevice($device);

        $this->cleanupStaleCommands($device);

        if ($device->status !== 'active') {
            return redirect()
                ->route('devices.show', $device)
                ->withErrors([
                    'duration_seconds' => 'Manual watering is only available when the device is active.',
                ]);
        }

        $rule = $device->wateringRule;
        $manualMaxDuration = $rule?->max_watering_duration_seconds ?? 300;

        $validated = $request->validate([
            'duration_seconds' => ['required', 'integer', 'min:1'],
        ]);

        if ((int) $validated['duration_seconds'] > $manualMaxDuration) {
            return back()
                ->withErrors([
                    'duration_seconds' => "Manual watering duration cannot be greater than {$manualMaxDuration} seconds.",
                ])
                ->withInput();
        }

        $hasActiveStartCommand = DeviceCommand::where('device_id', $device->id)
            ->where('command_type', 'valve_on')
            ->whereIn('status', ['pending', 'acknowledged'])
            ->exists();

        $hasPendingStopCommand = DeviceCommand::where('device_id', $device->id)
            ->where('command_type', 'valve_off')
            ->whereIn('status', ['pending', 'acknowledged'])
            ->exists();

        if ($hasActiveStartCommand) {
            return redirect()
                ->route('devices.show', $device)
                ->withErrors([
                    'duration_seconds' => 'A watering request already exists. Use Stop Watering instead.',
                ]);
        }

        if ($hasPendingStopCommand) {
            return redirect()
                ->route('devices.show', $device)
                ->withErrors([
                    'duration_seconds' => 'A stop command is already waiting for the device.',
                ]);
        }

        $command = DeviceCommand::create([
            'device_id' => $device->id,
            'command_type' => 'valve_on',
            'payload' => [
                'duration_seconds' => (int) $validated['duration_seconds'],
            ],
            'status' => 'pending',
            'issued_at' => now(),
        ]);

        WateringLog::create([
            'device_id' => $device->id,
            'device_command_id' => $command->id,
            'trigger_type' => 'manual',
            'duration_seconds' => (int) $validated['duration_seconds'],
            'status' => 'requested',
            'notes' => 'Manual watering requested from dashboard.',
        ]);

        return redirect()
            ->route('devices.show', $device)
            ->with('success', 'Start watering command created successfully.');
    }

    public function stopWatering(Device $device): RedirectResponse
    {
        $this->authorizeDevice($device);

        $this->cleanupStaleCommands($device);

        if ($device->status !== 'active') {
            return redirect()
                ->route('devices.show', $device)
                ->withErrors([
                    'manual_control' => 'Stop watering is only available when the device is active.',
                ]);
        }

        $activeValveOnCommand = DeviceCommand::where('device_id', $device->id)
            ->where('command_type', 'valve_on')
            ->whereIn('status', ['pending', 'acknowledged'])
            ->latest('id')
            ->first();

        if (! $activeValveOnCommand) {
            return redirect()
                ->route('devices.show', $device)
                ->withErrors([
                    'manual_control' => 'There is no active watering request to stop.',
                ]);
        }

        $existingStopCommand = DeviceCommand::where('device_id', $device->id)
            ->where('command_type', 'valve_off')
            ->whereIn('status', ['pending', 'acknowledged'])
            ->exists();

        if ($existingStopCommand) {
            return redirect()
                ->route('devices.show', $device)
                ->withErrors([
                    'manual_control' => 'A stop command is already waiting for the device.',
                ]);
        }

        DeviceCommand::create([
            'device_id' => $device->id,
            'command_type' => 'valve_off',
            'payload' => [],
            'status' => 'pending',
            'issued_at' => now(),
        ]);

        return redirect()
            ->route('devices.show', $device)
            ->with('success', 'Stop watering command created successfully.');
    }

    private function authorizeDevice(Device $device): void
    {
        $user = Auth::user();

        if (! $user || $device->user_id !== $user->id) {
            abort(403);
        }
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
        }
    }

    private function getTimezoneOptions(): array
    {
        return timezone_identifiers_list();
    }
}
