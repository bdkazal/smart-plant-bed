<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\WateringSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WateringScheduleController extends Controller
{
    public function create(Device $device): View
    {
        $this->authorizeDevice($device);

        return view('devices.schedules.create', compact('device'));
    }

    public function store(Request $request, Device $device): RedirectResponse
    {
        $this->authorizeDevice($device);

        $validated = $request->validate([
            'day_of_week' => ['required', 'integer', 'min:1', 'max:7'],
            'time_of_day' => ['required', 'date_format:H:i'],
            'duration_seconds' => ['required', 'integer', 'min:1', 'max:300'],
            'is_enabled' => ['nullable', 'boolean'],
        ]);

        WateringSchedule::create([
            'device_id' => $device->id,
            'day_of_week' => (int) $validated['day_of_week'],
            'time_of_day' => $validated['time_of_day'] . ':00',
            'duration_seconds' => (int) $validated['duration_seconds'],
            'is_enabled' => $request->boolean('is_enabled'),
        ]);

        return redirect()
            ->route('devices.show', $device)
            ->with('success', 'Watering schedule created successfully.');
    }

    public function edit(Device $device, WateringSchedule $schedule): View
    {
        $this->authorizeSchedule($device, $schedule);

        return view('devices.schedules.edit', compact('device', 'schedule'));
    }

    public function update(Request $request, Device $device, WateringSchedule $schedule): RedirectResponse
    {
        $this->authorizeSchedule($device, $schedule);

        $validated = $request->validate([
            'day_of_week' => ['required', 'integer', 'min:1', 'max:7'],
            'time_of_day' => ['required', 'date_format:H:i'],
            'duration_seconds' => ['required', 'integer', 'min:1', 'max:300'],
            'is_enabled' => ['nullable', 'boolean'],
        ]);

        $schedule->update([
            'day_of_week' => (int) $validated['day_of_week'],
            'time_of_day' => $validated['time_of_day'] . ':00',
            'duration_seconds' => (int) $validated['duration_seconds'],
            'is_enabled' => $request->boolean('is_enabled'),
        ]);

        return redirect()
            ->route('devices.show', $device)
            ->with('success', 'Watering schedule updated successfully.');
    }

    public function destroy(Device $device, WateringSchedule $schedule): RedirectResponse
    {
        $this->authorizeSchedule($device, $schedule);

        $schedule->delete();

        return redirect()
            ->route('devices.show', $device)
            ->with('success', 'Watering schedule deleted successfully.');
    }

    public function toggle(Device $device, WateringSchedule $schedule): RedirectResponse
    {
        $this->authorizeSchedule($device, $schedule);

        $schedule->update([
            'is_enabled' => ! $schedule->is_enabled,
        ]);

        return redirect()
            ->route('devices.show', $device)
            ->with('success', 'Watering schedule status updated successfully.');
    }

    private function authorizeDevice(Device $device): void
    {
        $user = Auth::user();

        if (! $user || $device->user_id !== $user->id) {
            abort(403);
        }
    }

    private function authorizeSchedule(Device $device, WateringSchedule $schedule): void
    {
        $this->authorizeDevice($device);

        if ($schedule->device_id !== $device->id) {
            abort(404);
        }
    }
}
