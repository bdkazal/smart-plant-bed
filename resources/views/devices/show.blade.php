<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $device->name }} - Device Home</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="max-w-4xl mx-auto py-6 px-4">
        <div class="mb-4">
            <a href="{{ route('devices.index') }}" class="text-blue-600 hover:underline">← Back to Devices</a>
        </div>

        <div class="mb-4 flex flex-wrap gap-2">
            <a href="{{ route('devices.show', $device) }}" class="rounded bg-blue-600 px-3 py-2 text-sm text-white">Home</a>
            <a href="{{ route('devices.automation', $device) }}" class="rounded bg-white px-3 py-2 text-sm border">Automation</a>
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

        <h1 class="mb-6 text-2xl font-bold">{{ $device->name }}</h1>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-lg bg-white p-5 shadow">
                <h2 class="mb-3 text-lg font-semibold">Device Status</h2>
                <p><strong>Type:</strong> {{ $device->displayType() }}</p>
                <p><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $device->status)) }}</p>
                <p><strong>Location:</strong> {{ $device->location_label ?? 'N/A' }}</p>
                <p><strong>Timezone:</strong> {{ $device->timezone ?? 'Asia/Dhaka' }}</p>
                <p><strong>Mode:</strong> {{ ucfirst($device->wateringRule?->watering_mode ?? 'schedule') }}</p>
                <p><strong>Enabled Schedules:</strong> {{ $device->wateringSchedules->count() }}</p>
                <p><strong>Last Seen:</strong> {{ $device->last_seen_at?->diffForHumans() ?? 'Never' }}</p>
            </div>

            <div class="rounded-lg bg-white p-5 shadow">
                <h2 class="mb-3 text-lg font-semibold">Latest Reading</h2>
                @if ($latestReading)
                <p><strong>Temperature:</strong> {{ $latestReading->temperature ?? 'N/A' }} °C</p>
                <p><strong>Humidity:</strong> {{ $latestReading->humidity ?? 'N/A' }}%</p>
                <p><strong>Soil Moisture:</strong> {{ $latestReading->soil_moisture ?? 'N/A' }}%</p>
                <p><strong>Recorded:</strong> {{ $latestReading->recorded_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</p>
                @else
                <p class="text-gray-500">No reading available yet.</p>
                @endif
            </div>
        </div>

        <div class="mt-4 rounded-lg bg-white p-5 shadow">
            <h2 class="mb-3 text-lg font-semibold">Watering Control</h2>

            @if ($manualWateringState === 'pending')
            <div class="mb-4 rounded border border-yellow-300 bg-yellow-50 px-4 py-3 text-yellow-800">
                Watering request is waiting for device confirmation.
                @if ($latestActiveWateringLog)
                <div class="mt-1 text-sm">Trigger: {{ ucfirst($latestActiveWateringLog->trigger_type) }}</div>
                @endif
            </div>

            <form action="{{ route('devices.water-stop', $device) }}" method="POST">
                @csrf
                <button type="submit" class="rounded bg-red-600 px-4 py-2 text-white hover:bg-red-700">
                    Stop Watering
                </button>
            </form>
            @elseif ($manualWateringState === 'running')
            <div class="mb-4 rounded border border-green-300 bg-green-50 px-4 py-3 text-green-800">
                Watering is currently in progress.
                @if ($latestActiveWateringLog)
                <div class="mt-1 text-sm">Trigger: {{ ucfirst($latestActiveWateringLog->trigger_type) }}</div>
                @endif
            </div>

            <form action="{{ route('devices.water-stop', $device) }}" method="POST">
                @csrf
                <button type="submit" class="rounded bg-red-600 px-4 py-2 text-white hover:bg-red-700">
                    Stop Watering
                </button>
            </form>
            @elseif ($manualWateringState === 'stopping')
            <div class="rounded border border-gray-300 bg-gray-50 px-4 py-3 text-gray-800">
                Stop request is waiting for device confirmation.
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
                        Maximum allowed: {{ $manualMaxDuration }} seconds
                    </p>
                </div>

                <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    Start Watering
                </button>
            </form>
            @endif
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-3">
            <a href="{{ route('devices.automation', $device) }}" class="rounded-lg bg-white p-5 shadow hover:bg-gray-50">
                <h3 class="font-semibold">Automation</h3>
                <p class="mt-2 text-sm text-gray-600">Mode, timezone, threshold, cooldown, and durations.</p>
            </a>

            <a href="{{ route('devices.schedules.index', $device) }}" class="rounded-lg bg-white p-5 shadow hover:bg-gray-50">
                <h3 class="font-semibold">Schedules</h3>
                <p class="mt-2 text-sm text-gray-600">Manage scheduled watering times.</p>
            </a>

            <a href="{{ route('devices.history', $device) }}" class="rounded-lg bg-white p-5 shadow hover:bg-gray-50">
                <h3 class="font-semibold">History</h3>
                <p class="mt-2 text-sm text-gray-600">See recent watering logs and device commands.</p>
            </a>
        </div>
    </div>
</body>

</html>