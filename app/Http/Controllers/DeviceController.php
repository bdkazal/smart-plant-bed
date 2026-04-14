<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceCommand;
use App\Models\WateringLog;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index()
    {
        $devices = Device::latest()->get();

        return view('devices.index', compact('devices'));
    }

    public function show(Device $device)
    {
        $today = now($device->timezone ?? config('app.timezone'))->dayOfWeekIso;

        $device->load([
            'wateringRule',
            'wateringSchedules' => fn($query) => $query
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

        return view('devices.show', compact('device', 'latestReading'));
    }

    public function waterNow(Request $request, Device $device)
    {
        $validated = $request->validate([
            'duration_seconds' => ['required', 'integer', 'min:1', 'max:300'],
        ]);

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
            'duration_seconds' => $validated['duration_seconds'],
            'status' => 'requested',
            'notes' => 'Manual watering requested from dashboard.',
        ]);

        return redirect()
            ->route('devices.show', $device)
            ->with('success', 'Watering command created successfully.');
    }
}
