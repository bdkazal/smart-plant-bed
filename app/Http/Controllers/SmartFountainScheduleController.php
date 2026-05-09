<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceScheduleRange;
use App\Services\SmartFountainScenePresetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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

    private const PERIODS = [
        'day' => [
            'name' => 'Day',
            'start_time' => '06:00:00',
            'end_time' => '18:00:00',
            'scene_name' => 'Day Fountain',
        ],
        'evening' => [
            'name' => 'Evening',
            'start_time' => '18:00:00',
            'end_time' => '23:00:00',
            'scene_name' => 'Night Glow',
        ],
        'night' => [
            'name' => 'Night',
            'start_time' => '23:00:00',
            'end_time' => '06:00:00',
            'scene_name' => 'All Off',
        ],
    ];

    public function index(Device $device): View
    {
        $this->authorizeSmartFountain($device);
        $this->ensureTimelineBlocks($device);

        $schedules = $this->timelineSchedules($device);

        return view('devices.smart-fountain.schedules.index', [
            'device' => $device,
            'schedules' => $schedules,
            'dayNames' => self::DAY_NAMES,
        ]);
    }

    public function create(Device $device): RedirectResponse
    {
        $this->authorizeSmartFountain($device);

        return redirect()
            ->route('devices.smart-fountain.schedules.index', $device)
            ->with('success', 'Smart Fountain uses three fixed timeline blocks: Day, Evening, and Night. Edit one of them to change its time or scene.');
    }

    public function store(Request $request, Device $device): RedirectResponse
    {
        $this->authorizeSmartFountain($device);

        return redirect()
            ->route('devices.smart-fountain.schedules.index', $device)
            ->withErrors(['schedule' => 'Creating extra Smart Fountain schedules is disabled for V1. Edit the Day, Evening, or Night block instead.']);
    }

    public function edit(Device $device, DeviceScheduleRange $schedule): View
    {
        $this->authorizeSchedule($device, $schedule);

        if (! array_key_exists((string) $schedule->period_key, self::PERIODS)) {
            abort(404);
        }

        $this->ensureTimelineBlocks($device);

        return view('devices.smart-fountain.schedules.form', [
            'device' => $device,
            'schedule' => $schedule->fresh(),
            'scenes' => $device->scenes()->orderBy('name')->get(),
            'dayNames' => self::DAY_NAMES,
        ]);
    }

    public function update(Request $request, Device $device, DeviceScheduleRange $schedule): RedirectResponse
    {
        $this->authorizeSchedule($device, $schedule);

        if (! array_key_exists((string) $schedule->period_key, self::PERIODS)) {
            abort(404);
        }

        $validated = $this->validateSchedule($request, $device, $schedule);
        $this->ensureNoConflictingRanges($device, $validated, $schedule);

        $schedule->update($validated);

        return redirect()
            ->route('devices.smart-fountain.schedules.index', $device)
            ->with('success', 'Timeline block updated successfully.');
    }

    public function toggle(Device $device, DeviceScheduleRange $schedule): RedirectResponse
    {
        $this->authorizeSchedule($device, $schedule);

        if (! array_key_exists((string) $schedule->period_key, self::PERIODS)) {
            abort(404);
        }

        if (! $schedule->is_enabled) {
            $this->ensureNoConflictingRanges($device, [
                'period_key' => $schedule->period_key,
                'name' => $schedule->name,
                'days_of_week' => $schedule->days_of_week ?? [],
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'start_scene_id' => $schedule->start_scene_id,
                'end_scene_id' => $schedule->end_scene_id,
                'is_enabled' => true,
            ], $schedule);
        }

        $schedule->update([
            'is_enabled' => ! $schedule->is_enabled,
        ]);

        return redirect()
            ->route('devices.smart-fountain.schedules.index', $device)
            ->with('success', 'Timeline block status updated successfully.');
    }

    public function destroy(Device $device, DeviceScheduleRange $schedule): RedirectResponse
    {
        $this->authorizeSchedule($device, $schedule);

        return redirect()
            ->route('devices.smart-fountain.schedules.index', $device)
            ->withErrors(['schedule' => 'Timeline blocks cannot be deleted. Disable the block instead.']);
    }

    private function validateSchedule(Request $request, Device $device, DeviceScheduleRange $schedule): array
    {
        $validated = $request->validate([
            'days_of_week' => ['required', 'array', 'min:1'],
            'days_of_week.*' => ['integer', 'between:1,7'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'different:start_time'],
            'start_scene_id' => [
                'required',
                Rule::exists('device_scenes', 'id')->where('device_id', $device->id),
            ],
            'is_enabled' => ['nullable', 'boolean'],
        ]);

        $validated['name'] = self::PERIODS[$schedule->period_key]['name'];
        $validated['period_key'] = $schedule->period_key;
        $validated['days_of_week'] = collect($validated['days_of_week'])
            ->map(fn ($day) => (int) $day)
            ->unique()
            ->sort()
            ->values()
            ->all();

        $validated['start_time'] = $validated['start_time'] . ':00';
        $validated['end_time'] = $validated['end_time'] . ':00';
        $validated['end_scene_id'] = $validated['start_scene_id'];
        $validated['is_enabled'] = $request->boolean('is_enabled');

        return $validated;
    }

    private function ensureTimelineBlocks(Device $device): void
    {
        app(SmartFountainScenePresetService::class)->ensureDefaultScenes($device);
        $device->loadMissing('scenes');

        foreach (self::PERIODS as $periodKey => $period) {
            $scene = $this->sceneByName($device, $period['scene_name'])
                ?? $device->scenes()->orderBy('id')->first();

            if (! $scene) {
                continue;
            }

            $device->scheduleRanges()->firstOrCreate(
                ['period_key' => $periodKey],
                [
                    'name' => $period['name'],
                    'days_of_week' => [1, 2, 3, 4, 5, 6, 7],
                    'start_time' => $period['start_time'],
                    'end_time' => $period['end_time'],
                    'start_scene_id' => $scene->id,
                    'end_scene_id' => $scene->id,
                    'is_enabled' => true,
                ]
            );
        }
    }

    private function sceneByName(Device $device, string $name)
    {
        return $device->scenes->firstWhere('name', $name)
            ?? $device->scenes()->where('name', $name)->first();
    }

    private function timelineSchedules(Device $device)
    {
        $device->load([
            'scheduleRanges.startScene',
            'scheduleRanges.endScene',
        ]);

        return collect(array_keys(self::PERIODS))
            ->map(fn ($periodKey) => $device->scheduleRanges->firstWhere('period_key', $periodKey))
            ->filter()
            ->values();
    }

    private function ensureNoConflictingRanges(Device $device, array $validated, ?DeviceScheduleRange $ignoreSchedule = null): void
    {
        if (! ($validated['is_enabled'] ?? false)) {
            return;
        }

        $newRanges = $this->expandScheduleRanges(
            days: $validated['days_of_week'],
            startTime: $validated['start_time'],
            endTime: $validated['end_time']
        );

        $existingSchedules = $device->scheduleRanges()
            ->when($ignoreSchedule, fn ($query) => $query->whereKeyNot($ignoreSchedule->id))
            ->whereIn('period_key', array_keys(self::PERIODS))
            ->where('is_enabled', true)
            ->get();

        foreach ($existingSchedules as $existingSchedule) {
            $existingRanges = $this->expandScheduleRanges(
                days: $existingSchedule->days_of_week ?? [],
                startTime: $existingSchedule->start_time,
                endTime: $existingSchedule->end_time
            );

            foreach ($newRanges as $newRange) {
                foreach ($existingRanges as $existingRange) {
                    if ($newRange['day'] !== $existingRange['day']) {
                        continue;
                    }

                    if ($this->rangesOverlap($newRange, $existingRange)) {
                        throw ValidationException::withMessages([
                            'start_time' => "Timeline block overlaps with '{$existingSchedule->name}'. Adjust the time range or disable the other block first.",
                        ]);
                    }
                }
            }
        }
    }

    private function expandScheduleRanges(array $days, string $startTime, string $endTime): array
    {
        $startMinute = $this->timeToMinute($startTime);
        $endMinute = $this->timeToMinute($endTime);
        $ranges = [];

        foreach ($days as $day) {
            $day = (int) $day;

            if ($startMinute < $endMinute) {
                $ranges[] = [
                    'day' => $day,
                    'start' => $startMinute,
                    'end' => $endMinute,
                ];

                continue;
            }

            $ranges[] = [
                'day' => $day,
                'start' => $startMinute,
                'end' => 1440,
            ];

            $ranges[] = [
                'day' => $this->nextDay($day),
                'start' => 0,
                'end' => $endMinute,
            ];
        }

        return $ranges;
    }

    private function rangesOverlap(array $first, array $second): bool
    {
        return $first['start'] < $second['end'] && $second['start'] < $first['end'];
    }

    private function timeToMinute(string $time): int
    {
        [$hour, $minute] = array_map('intval', explode(':', substr($time, 0, 5)));

        return ($hour * 60) + $minute;
    }

    private function nextDay(int $day): int
    {
        return $day === 7 ? 1 : $day + 1;
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
