<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $device->name }} - Automation</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="max-w-4xl mx-auto py-6 px-4">
        <div class="mb-4">
            <a href="{{ route('devices.show', $device) }}" class="text-blue-600 hover:underline">← Back to Device Home</a>
        </div>

        <div class="mb-4 flex flex-wrap gap-2">
            <a href="{{ route('devices.show', $device) }}" class="rounded bg-white px-3 py-2 text-sm border">Home</a>
            <a href="{{ route('devices.automation', $device) }}" class="rounded bg-blue-600 px-3 py-2 text-sm text-white">Automation</a>
            <a href="{{ route('devices.schedules.index', $device) }}" class="rounded bg-white px-3 py-2 text-sm border">Schedules</a>
            <a href="{{ route('devices.history', $device) }}" class="rounded bg-white px-3 py-2 text-sm border">History</a>
        </div>

        @if (session('success'))
        <div class="mb-4 rounded bg-green-100 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
        @endif

        @if ($errors->any())
        <div class="mb-4 rounded bg-red-100 px-4 py-3 text-red-800">
            <ul class="list-disc ml-5">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="rounded-lg bg-white p-6 shadow">
            <h1 class="mb-2 text-2xl font-bold">Automation Settings</h1>
            <p class="mb-6 text-gray-600">
                Device timezone controls all schedule times. Auto mode uses soil moisture. Schedule mode uses saved day/time rules.
            </p>

            <form action="{{ route('devices.settings.update', $device) }}" method="POST" class="space-y-6">
                @csrf

                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label for="name" class="mb-1 block font-medium">Device Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $device->name) }}" class="w-full rounded border px-3 py-2" required>
                    </div>

                    <div>
                        <label for="location_label" class="mb-1 block font-medium">Location</label>
                        <input type="text" name="location_label" id="location_label" value="{{ old('location_label', $device->location_label) }}" class="w-full rounded border px-3 py-2">
                    </div>
                </div>

                <div>
                    <label for="timezone" class="mb-1 block font-medium">Device Timezone</label>
                    <select name="timezone" id="timezone" class="w-full rounded border px-3 py-2" required>
                        @foreach ($timezoneOptions as $timezone)
                        <option value="{{ $timezone }}" @selected(old('timezone', $device->timezone ?? 'Asia/Dhaka') === $timezone)>
                            {{ $timezone }}
                        </option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-sm text-gray-500">All schedules use this timezone.</p>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label for="watering_mode" class="mb-1 block font-medium">Automation Mode</label>
                        <select name="watering_mode" id="watering_mode" class="w-full rounded border px-3 py-2" required>
                            <option value="auto" @selected(($device->wateringRule?->watering_mode ?? 'schedule') === 'auto')>Auto</option>
                            <option value="schedule" @selected(($device->wateringRule?->watering_mode ?? 'schedule') === 'schedule')>Schedule</option>
                        </select>

                        @if ($latestReading && ! is_null($latestReading->soil_moisture))
                        <p class="mt-2 text-sm text-green-700">Current soil moisture data is available. Auto mode can operate.</p>
                        @else
                        <p class="mt-2 text-sm text-yellow-700">Current soil moisture data is unavailable. Auto mode needs a valid moisture reading.</p>
                        @endif
                    </div>

                    <div>
                        <label for="soil_moisture_threshold" class="mb-1 block font-medium">Soil Moisture Threshold (%)</label>
                        <input
                            type="number"
                            name="soil_moisture_threshold"
                            id="soil_moisture_threshold"
                            min="0"
                            max="100"
                            value="{{ old('soil_moisture_threshold', $device->wateringRule?->soil_moisture_threshold ?? 35) }}"
                            class="w-full rounded border px-3 py-2"
                            required>
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    <div>
                        <label for="max_watering_duration_seconds" class="mb-1 block font-medium">Max Watering Duration (sec)</label>
                        <input
                            type="number"
                            name="max_watering_duration_seconds"
                            id="max_watering_duration_seconds"
                            min="1"
                            max="300"
                            value="{{ old('max_watering_duration_seconds', $device->wateringRule?->max_watering_duration_seconds ?? 30) }}"
                            class="w-full rounded border px-3 py-2"
                            required>
                    </div>

                    <div>
                        <label for="cooldown_minutes" class="mb-1 block font-medium">Cooldown (minutes)</label>
                        <input
                            type="number"
                            name="cooldown_minutes"
                            id="cooldown_minutes"
                            min="0"
                            max="1440"
                            value="{{ old('cooldown_minutes', $device->wateringRule?->cooldown_minutes ?? 60) }}"
                            class="w-full rounded border px-3 py-2"
                            required>
                    </div>

                    <div>
                        <label for="local_manual_duration_seconds" class="mb-1 block font-medium">Local Manual Duration (sec)</label>
                        <input
                            type="number"
                            name="local_manual_duration_seconds"
                            id="local_manual_duration_seconds"
                            min="1"
                            max="300"
                            value="{{ old('local_manual_duration_seconds', $device->wateringRule?->local_manual_duration_seconds ?? 30) }}"
                            class="w-full rounded border px-3 py-2"
                            required>
                    </div>
                </div>

                <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    Save Automation Settings
                </button>
            </form>
        </div>
    </div>
</body>

</html>