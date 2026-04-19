<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $device->name }} - Smart Plant Bed</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="max-w-5xl mx-auto py-10 px-4">
        <div class="mb-6">
            <a href="{{ route('devices.index') }}" class="text-blue-600 hover:underline">← Back to Devices</a>
        </div>

        @if (session('success'))
        <div class="mb-6 rounded-lg bg-green-100 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
        @endif

        @if ($errors->any())
        <div class="mb-6 rounded-lg bg-red-100 px-4 py-3 text-red-800">
            <ul class="list-disc ml-5">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <h1 class="mb-6 text-3xl font-bold">{{ $device->name }}</h1>

        @if ($device->status === 'claimed_pending_wifi')
        <div class="mb-6 rounded-lg border border-yellow-300 bg-yellow-50 p-5">
            <h2 class="text-lg font-semibold text-yellow-900">Wi-Fi setup is not finished yet</h2>
            <p class="mt-2 text-yellow-800">
                This device has been claimed successfully, but it is still waiting for Wi-Fi setup.
                Open the setup instructions again to complete onboarding.
            </p>

            <div class="mt-4">
                <a
                    href="{{ route('devices.setup', $device) }}"
                    class="inline-flex items-center rounded bg-yellow-600 px-4 py-2 text-white hover:bg-yellow-700">
                    Continue Setup
                </a>
            </div>
        </div>
        @endif

        <div class="grid gap-6 md:grid-cols-2">
            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-4 text-xl font-semibold">Device Info</h2>
                <p><strong>ID:</strong> {{ $device->id }}</p>
                <p><strong>UUID:</strong> {{ $device->uuid }}</p>
                <p><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $device->status)) }}</p>
                <p><strong>Location:</strong> {{ $device->location_label ?? 'N/A' }}</p>
                <p><strong>Timezone:</strong> {{ $device->timezone ?? 'N/A' }}</p>
                <p><strong>Firmware:</strong> {{ $device->firmware_version ?? 'N/A' }}</p>
                <p><strong>Last Seen:</strong> {{ $device->last_seen_at?->diffForHumans() ?? 'Never' }}</p>
            </div>

            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-4 text-xl font-semibold">Latest Sensor Reading</h2>

                @if ($latestReading)
                <p><strong>Temperature:</strong> {{ $latestReading->temperature ?? 'N/A' }} °C</p>
                <p><strong>Humidity:</strong> {{ $latestReading->humidity ?? 'N/A' }}%</p>
                <p><strong>Soil Moisture:</strong> {{ $latestReading->soil_moisture ?? 'N/A' }}%</p>
                <p><strong>Recorded At:</strong> {{ $latestReading->recorded_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</p>
                @else
                @if ($device->status === 'claimed_pending_wifi')
                <p class="text-gray-500">
                    No sensor readings yet. Complete Wi-Fi setup first so the device can connect and send data.
                </p>
                @else
                <p class="text-gray-500">No sensor readings available.</p>
                @endif
                @endif
            </div>
        </div>

        <div class="mt-6 rounded-lg bg-white p-6 shadow">
            <h2 class="mb-4 text-xl font-semibold">Device Settings</h2>

            <form action="{{ route('devices.settings.update', $device) }}" method="POST" class="space-y-6">
                @csrf

                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label for="name" class="mb-1 block font-medium">Device Name</label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name', $device->name) }}"
                            class="w-full rounded border px-3 py-2"
                            required>
                    </div>

                    <div>
                        <label for="location_label" class="mb-1 block font-medium">Location</label>
                        <input
                            type="text"
                            name="location_label"
                            id="location_label"
                            value="{{ old('location_label', $device->location_label) }}"
                            class="w-full rounded border px-3 py-2">
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label for="watering_mode" class="mb-1 block font-medium">Automation Mode</label>
                        <select
                            name="watering_mode"
                            id="watering_mode"
                            class="w-full rounded border px-3 py-2"
                            required>
                            <option value="auto" @selected($device->wateringRule?->watering_mode === 'auto')>Auto</option>
                            <option value="schedule" @selected(($device->wateringRule?->watering_mode ?? 'schedule') === 'schedule')>Schedule</option>
                        </select>

                        @if ($latestReading && ! is_null($latestReading->soil_moisture))
                        <p class="mt-2 text-sm text-green-700">
                            Current soil moisture data is available. Auto mode can operate.
                        </p>
                        @else
                        <p class="mt-2 text-sm text-yellow-700">
                            Current soil moisture data is unavailable. Auto mode can be selected only after the device sends a valid moisture reading.
                        </p>
                        @endif

                        @if (($device->wateringRule?->watering_mode ?? 'schedule') === 'schedule' && $device->wateringSchedules->isEmpty())
                        <p class="mt-2 text-sm text-yellow-700">
                            Schedule mode is selected, but no schedules exist yet.
                        </p>
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

                <button
                    type="submit"
                    class="inline-flex items-center rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    Save Settings
                </button>
            </form>
        </div>

        @if ($device->status === 'active')
        <div class="mt-6 rounded-lg bg-white p-6 shadow">
            <h2 class="mb-4 text-xl font-semibold">Manual Watering</h2>

            @if ($manualWateringState === 'pending')
            <div class="mb-4 rounded border border-yellow-300 bg-yellow-50 px-4 py-3 text-yellow-800">
                <p class="font-medium">Watering request is waiting for device confirmation.</p>
                @if ($latestActiveWateringLog)
                <p class="mt-1 text-sm">
                    Current trigger: {{ ucfirst($latestActiveWateringLog->trigger_type) }}.
                </p>
                @endif
            </div>

            <form action="{{ route('devices.water-stop', $device) }}" method="POST">
                @csrf
                <button
                    type="submit"
                    class="inline-flex items-center rounded bg-red-600 px-4 py-2 text-white hover:bg-red-700">
                    Stop Watering
                </button>
            </form>
            @elseif ($manualWateringState === 'running')
            <div class="mb-4 rounded border border-green-300 bg-green-50 px-4 py-3 text-green-800">
                <p class="font-medium">Watering is currently in progress.</p>
                @if ($latestActiveWateringLog)
                <p class="mt-1 text-sm">
                    Current trigger: {{ ucfirst($latestActiveWateringLog->trigger_type) }}.
                </p>
                @endif
            </div>

            <form action="{{ route('devices.water-stop', $device) }}" method="POST">
                @csrf
                <button
                    type="submit"
                    class="inline-flex items-center rounded bg-red-600 px-4 py-2 text-white hover:bg-red-700">
                    Stop Watering
                </button>
            </form>
            @elseif ($manualWateringState === 'stopping')
            <div class="mb-4 rounded border border-gray-300 bg-gray-50 px-4 py-3 text-gray-800">
                <p class="font-medium">Stop request is waiting for device confirmation.</p>
            </div>
            @else
            <form action="{{ route('devices.water-now', $device) }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="duration_seconds" class="mb-1 block font-medium">Duration (seconds)</label>
                    <input
                        type="number"
                        name="duration_seconds"
                        id="duration_seconds"
                        min="1"
                        max="{{ $manualMaxDuration }}"
                        value="{{ old('duration_seconds', min(30, $manualMaxDuration)) }}"
                        class="w-full rounded border px-3 py-2 md:w-64"
                        required>
                    <p class="mt-2 text-sm text-gray-500">
                        Maximum allowed for manual watering: {{ $manualMaxDuration }} seconds.
                    </p>
                </div>

                <button
                    type="submit"
                    class="inline-flex items-center rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    Start Watering
                </button>
            </form>
            @endif
        </div>
        @else
        <div class="mt-6 rounded-lg border border-gray-300 bg-white p-6 shadow">
            <h2 class="mb-2 text-xl font-semibold">Manual Watering</h2>
            <p class="text-gray-600">
                Manual watering will be available after the device finishes setup and becomes active.
            </p>
        </div>
        @endif

        <div class="mt-6 grid gap-6 md:grid-cols-2)">
            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-4 text-xl font-semibold">Watering Rule</h2>

                @if ($device->wateringRule)
                <p><strong>Automation Mode:</strong> {{ ucfirst($device->wateringRule->watering_mode ?? 'schedule') }}</p>
                <p><strong>Soil Moisture Threshold:</strong> {{ $device->wateringRule->soil_moisture_threshold }}%</p>
                <p><strong>Max Watering Duration:</strong> {{ $device->wateringRule->max_watering_duration_seconds }} sec</p>
                <p><strong>Cooldown:</strong> {{ $device->wateringRule->cooldown_minutes }} min</p>
                <p><strong>Local Manual Duration:</strong> {{ $device->wateringRule->local_manual_duration_seconds }} sec</p>
                @else
                <p class="text-gray-500">No watering rule found.</p>
                @endif
            </div>

            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-4 text-xl font-semibold">Watering Schedules</h2>

                @php
                $days = [
                1 => 'Monday',
                2 => 'Tuesday',
                3 => 'Wednesday',
                4 => 'Thursday',
                5 => 'Friday',
                6 => 'Saturday',
                7 => 'Sunday',
                ];
                @endphp

                @forelse($device->wateringSchedules as $schedule)
                <div class="border-b py-2 last:border-b-0">
                    <p><strong>Status:</strong> {{ $schedule->is_enabled ? 'Enabled' : 'Disabled' }}</p>
                    <p><strong>Day:</strong> {{ $days[$schedule->day_of_week] ?? 'Unknown' }}</p>
                    <p><strong>Time:</strong> {{ $schedule->time_of_day }}</p>
                    <p><strong>Duration:</strong> {{ $schedule->duration_seconds }} sec</p>
                </div>
                @empty
                <p class="text-gray-500">No watering schedules found.</p>
                @endforelse
            </div>
        </div>

        <div class="mt-6 rounded-lg bg-white p-6 shadow">
            <h2 class="mb-4 text-xl font-semibold">Recent Watering Logs</h2>

            @forelse($device->wateringLogs as $log)
            <div class="border-b py-3 last:border-b-0">
                <p><strong>Trigger:</strong> {{ ucfirst($log->trigger_type) }}</p>
                <p><strong>Duration:</strong> {{ $log->duration_seconds }} sec</p>
                <p><strong>Status:</strong> {{ ucfirst($log->status) }}</p>
                <p><strong>Started At:</strong> {{ $log->started_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</p>
                <p><strong>Ended At:</strong> {{ $log->ended_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</p>
                <p><strong>Requested At:</strong> {{ $log->created_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</p>
                <p><strong>Notes:</strong> {{ $log->notes ?? 'N/A' }}</p>
            </div>
            @empty
            <p class="text-gray-500">No watering logs found.</p>
            @endforelse
        </div>

        <div class="mt-6 rounded-lg bg-white p-6 shadow">
            <h2 class="mb-4 text-xl font-semibold">Recent Device Commands</h2>

            @forelse($device->deviceCommands as $command)
            <div class="border-b py-3 last:border-b-0">
                <p><strong>Command Type:</strong> {{ $command->command_type }}</p>
                <p><strong>Status:</strong> {{ ucfirst($command->status) }}</p>
                <p><strong>Payload:</strong></p>

                <pre class="overflow-x-auto rounded bg-gray-100 p-3 text-sm">{{ json_encode($command->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>

                <p><strong>Issued At:</strong> {{ $command->issued_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</p>
                <p><strong>Acknowledged At:</strong> {{ $command->acknowledged_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</p>
                <p><strong>Executed At:</strong> {{ $command->executed_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</p>
            </div>
            @empty
            <p class="text-gray-500">No device commands found.</p>
            @endforelse
        </div>
    </div>
</body>

</html>