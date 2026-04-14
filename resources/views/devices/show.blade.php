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
        <div class="mb-6 rounded-lg bg-green-100 text-green-800 px-4 py-3">
            {{ session('success') }}
        </div>
        @endif

        <h1 class="text-3xl font-bold mb-6">{{ $device->name }}</h1>

        <div class="grid gap-6 md:grid-cols-2">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Device Info</h2>
                <p><strong>ID:</strong> {{ $device->id }}</p>
                <p><strong>UUID:</strong> {{ $device->uuid }}</p>
                <p><strong>Status:</strong> {{ ucfirst($device->status) }}</p>
                <p><strong>Location:</strong> {{ $device->location_label ?? 'N/A' }}</p>
                <p><strong>Timezone:</strong> {{ $device->timezone ?? 'N/A' }}</p>
                <p><strong>Firmware:</strong> {{ $device->firmware_version ?? 'N/A' }}</p>
                <p><strong>Last Seen:</strong> {{ $device->last_seen_at?->diffForHumans() ?? 'Never' }}</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Latest Sensor Reading</h2>

                @if($latestReading)
                <p><strong>Temperature:</strong> {{ $latestReading->temperature ?? 'N/A' }} °C</p>
                <p><strong>Humidity:</strong> {{ $latestReading->humidity ?? 'N/A' }}%</p>
                <p><strong>Soil Moisture:</strong> {{ $latestReading->soil_moisture ?? 'N/A' }}%</p>
                <p><strong>Recorded At:</strong> {{ $latestReading->recorded_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</p>
                @else
                <p class="text-gray-500">No sensor readings available.</p>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mt-6">
            <h2 class="text-xl font-semibold mb-4">Manual Watering</h2>

            <form action="{{ route('devices.water-now', $device) }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="duration_seconds" class="block font-medium mb-1">Duration (seconds)</label>
                    <input
                        type="number"
                        name="duration_seconds"
                        id="duration_seconds"
                        min="1"
                        max="300"
                        value="30"
                        class="w-full md:w-64 rounded border px-3 py-2"
                        required>
                    @error('duration_seconds')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="inline-flex items-center rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    Water Now
                </button>
            </form>
        </div>

        <div class="grid gap-6 md:grid-cols-2 mt-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Watering Rule</h2>

                @if($device->wateringRule)
                <p><strong>Auto Mode:</strong> {{ $device->wateringRule->auto_mode_enabled ? 'Enabled' : 'Disabled' }}</p>
                <p><strong>Soil Moisture Threshold:</strong> {{ $device->wateringRule->soil_moisture_threshold }}%</p>
                <p><strong>Max Watering Duration:</strong> {{ $device->wateringRule->max_watering_duration_seconds }} sec</p>
                <p><strong>Cooldown:</strong> {{ $device->wateringRule->cooldown_minutes }} min</p>
                <p><strong>Local Manual Duration:</strong> {{ $device->wateringRule->local_manual_duration_seconds }} sec</p>
                @else
                <p class="text-gray-500">No watering rule found.</p>
                @endif
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Watering Schedules</h2>

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

        <div class="bg-white rounded-lg shadow p-6 mt-6">
            <h2 class="text-xl font-semibold mb-4">Recent Watering Logs</h2>

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

        <div class="bg-white rounded-lg shadow p-6 mt-6">
            <h2 class="text-xl font-semibold mb-4">Recent Device Commands</h2>

            @forelse($device->deviceCommands as $command)
            <div class="border-b py-3 last:border-b-0">
                <p><strong>Command Type:</strong> {{ $command->command_type }}</p>
                <p><strong>Status:</strong> {{ ucfirst($command->status) }}</p>
                <p><strong>Payload:</strong></p>

                <pre class="bg-gray-100 rounded p-3 text-sm overflow-x-auto">{{ json_encode($command->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>

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