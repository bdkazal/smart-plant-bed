<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceScheduleRange;
use App\Services\SmartFountainScenePresetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SmartFountainScheduleController extends Controller
{
    private const DAY_NAMES = [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        7 => 'Sunday',
    ];

    public function index(Device $device): View
    {
        $this->authorizeSmartFountain($device);

        app(SmartFountainScenePresetService::class)->ensureDefaultScenes($device);

        $device->load([
            'scheduleRanges.startScene',
            'scheduleRanges.endScene',
        ]);

        $schedules = $device->scheduleRanges
            ->sortBy('start_time')
            ->values();

        return view('devices.smart-fountain.schedules.index', [
            'device' => $device,
            'schedules' => $schedules,
            'dayNames' => self::DAY_NAMES,
        ]);
    }

    public function create(Device $device): View
    {
        $this->authorizeSmartFountain($device);

        app(SmartFountainScenePresetService::class)->ensureDefaultScenes($device);

        return view('devices.smart-fountain.schedules.form', [
            'device' => $device,
            'schedule' => null,
            'scenes' => $device->scenes()->orderBy('name')->get(),
            'dayNames' => self::DAY_NAMES,
        ]);
    }

    public function store(Request $request, Device $device): RedirectResponse
    {
        $this->authorizeSmartFountain($device);

        $validated = $this->validateSchedule($request, $device);

        $device->scheduleRanges()->create($validated);

        return redirect()
            ->route('devices.smart-fountain.schedules.index', $device)
            ->with('success', 'Schedule range created successfully.');
    }

    public function edit(Device $device, DeviceScheduleRange $schedule): View
    {
        $this->authorizeSchedule($device, $schedule);

        return view('devices.smart-fountain.schedules.form', [
            'device' => $device,
            'schedule' => $schedule,
            'scenes' => $device->scenes()->orderBy('name')->get(),
            'dayNames' => self::DAY_NAMES,
        ]);
    }

    public function update(Request $request, Device $device, DeviceScheduleRange $schedule): RedirectResponse
    {
        $this->authorizeSchedule($device, $schedule);

        $schedule->update($this->validateSchedule($request, $device));

        return redirect()
            ->route('devices.smart-fountain.schedules.index', $device)
            ->with('success', 'Schedule range updated successfully.');
    }

    public function toggle(Device $device, DeviceScheduleRange $schedule): RedirectResponse
    {
        $this->authorizeSchedule($device, $schedule);

        $schedule->update([
            'is_enabled' => ! $schedule->is_enabled,
        ]);

        return redirect()
            ->route('devices.smart-fountain.schedules.index', $device)
            ->with('success', 'Schedule range status updated successfully.');
    }

    public function destroy(Device $device, DeviceScheduleRange $schedule): RedirectResponse
    {
        $this->authorizeSchedule($device, $schedule);

        $schedule->delete();

        return redirect()
            ->route('devices.smart-fountain.schedules.index', $device)
            ->with('success', 'Schedule range deleted successfully.');
    }

    private function validateSchedule(Request $request, Device $device): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'days_of_week' => ['required', 'array', 'min:1'],
            'days_of_week.*' => ['integer', 'between:1,7'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'different:start_time'],
            'start_scene_id' => [
                'required',
                Rule::exists('device_scenes', 'id')->where('device_id', $device->id),
            ],
            'end_scene_id' => [
                'required',
                Rule::exists('device_scenes', 'id')->where('device_id', $device->id),
            ],
            'is_enabled' => ['nullable', 'boolean'],
        ]);

        $validated['days_of_week'] = collect($validated['days_of_week'])
            ->map(fn ($day) => (int) $day)
            ->unique()
            ->sort()
            ->values()
            ->all();

        $validated['start_time'] = $validated['start_time'] . ':00';
        $validated['end_time'] = $validated['end_time'] . ':00';
        $validated['is_enabled'] = $request->boolean('is_enabled');

        return $validated;
    }

    private function authorizeSchedule(Device $device, DeviceScheduleRange $schedule): void
    {
        $this->authorizeSmartFountain($device);

        if ($schedule->device_id !== $device->id) {
            abort(404);
        }
    }

    private function authorizeSmartFountain(Device $device): void
    {
        $user = Auth::user();

        if (! $user || $device->user_id !== $user->id) {
            abort(403);
        }

        if (! $device->isSmartFountain()) {
            abort(404);
        }
    }
}
