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
        <div id="flash-success" class="mb-4 rounded bg-green-100 px-4 py-3 text-green-800">
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

        <div class="mb-6 flex items-center justify-between gap-4">
            <h1 id="device-name" class="text-2xl font-bold">{{ $device->name }}</h1>
            <div id="online-badge" class="rounded-full px-3 py-1 text-sm {{ $isOnline ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-700' }}">
                {{ $isOnline ? 'Online' : 'Offline' }}
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-lg bg-white p-5 shadow">
                <h2 class="mb-3 text-lg font-semibold">Device Status</h2>
                <p><strong>Type:</strong> <span id="device-type">{{ $device->displayType() }}</span></p>
                <p><strong>Status:</strong> <span id="device-status">{{ $isOnline ? 'Online' : 'Offline' }}</span></p>
                <p><strong>Location:</strong> <span id="device-location">{{ $device->location_label ?? 'N/A' }}</span></p>
                <p><strong>Timezone:</strong> <span id="device-timezone">{{ $device->timezone ?? 'Asia/Dhaka' }}</span></p>
                <p><strong>Mode:</strong> <span id="device-mode">{{ ucfirst($device->wateringRule?->watering_mode ?? 'schedule') }}</span></p>
                <p><strong>Enabled Schedules:</strong> <span id="enabled-schedules">{{ $enabledScheduleCount }}</span></p>
                <p><strong>Last Seen:</strong> <span id="device-last-seen">{{ $device->last_seen_at?->diffForHumans() ?? 'Never' }}</span></p>
            </div>

            <div class="rounded-lg bg-white p-5 shadow">
                <h2 class="mb-3 text-lg font-semibold">Latest Reading</h2>
                <p><strong>Temperature:</strong> <span id="reading-temperature">{{ $latestReading?->temperature ?? 'N/A' }}</span> °C</p>
                <p><strong>Humidity:</strong> <span id="reading-humidity">{{ $latestReading?->humidity ?? 'N/A' }}</span>%</p>
                <p><strong>Soil Moisture:</strong> <span id="reading-soil">{{ $latestReading?->soil_moisture ?? 'N/A' }}</span>%</p>
                <p><strong>Recorded:</strong> <span id="reading-recorded">{{ $latestReading?->recorded_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</span></p>
            </div>
        </div>

        <div class="mt-4 rounded-lg bg-white p-5 shadow">
            <h2 class="mb-3 text-lg font-semibold">Watering Control</h2>

            <div class="mb-4 rounded border border-gray-300 bg-gray-50 px-4 py-3 text-gray-800">
                <div><strong>Watering State:</strong> <span id="manual-state-text">{{ ucfirst($manualWateringState) }}</span></div>
                <div id="started-by-row" class="{{ in_array($manualWateringState, ['waiting', 'watering', 'stopping'], true) ? '' : 'hidden' }}">
                    <strong>Started By:</strong> <span id="started-by-text">N/A</span>
                </div>
            </div>

            <div id="manual-offline-note" class="{{ $isOnline ? 'hidden' : '' }} mb-4 rounded border border-yellow-300 bg-yellow-50 px-4 py-3 text-yellow-800">
                Device is offline. Manual watering is unavailable right now.
            </div>

            <div id="start-form-wrapper" class="{{ $manualWateringState === 'idle' && $isOnline ? '' : 'hidden' }}">
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
                            Maximum allowed: <span id="manual-max-duration">{{ $manualMaxDuration }}</span> seconds
                        </p>
                    </div>

                    <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                        Start Watering
                    </button>
                </form>
            </div>

            <div id="stop-form-wrapper" class="{{ in_array($manualWateringState, ['waiting', 'watering'], true) && $isOnline ? '' : 'hidden' }}">
                <form action="{{ route('devices.water-stop', $device) }}" method="POST">
                    @csrf
                    <button type="submit" class="rounded bg-red-600 px-4 py-2 text-white hover:bg-red-700">
                        Stop Watering
                    </button>
                </form>
            </div>
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

    <script>
        const statusUrl = "{{ route('devices.status', $device) }}";

        function setText(id, value) {
            const el = document.getElementById(id);
            if (!el) return;
            el.textContent = value ?? 'N/A';
        }

        function updateManualState(data) {
            const state = data.manual.state;
            const isOnline = data.device.is_online;

            const stateText = document.getElementById('manual-state-text');
            const startWrapper = document.getElementById('start-form-wrapper');
            const stopWrapper = document.getElementById('stop-form-wrapper');
            const offlineNote = document.getElementById('manual-offline-note');
            const startedByRow = document.getElementById('started-by-row');
            const startedByText = document.getElementById('started-by-text');

            if (stateText) {
                if (state === 'waiting') {
                    stateText.textContent = 'Waiting';
                } else if (state === 'watering') {
                    stateText.textContent = 'Watering';
                } else if (state === 'stopping') {
                    stateText.textContent = 'Stopping';
                } else {
                    stateText.textContent = 'Idle';
                }
            }

            if (startedByRow && startedByText) {
                if (['waiting', 'watering', 'stopping'].includes(state) && data.active_log?.trigger_label) {
                    startedByText.textContent = data.active_log.trigger_label;
                    startedByRow.classList.remove('hidden');
                } else {
                    startedByText.textContent = 'N/A';
                    startedByRow.classList.add('hidden');
                }
            }

            if (offlineNote) {
                offlineNote.classList.toggle('hidden', isOnline);
            }

            startWrapper?.classList.toggle('hidden', !(state === 'idle' && isOnline));
            stopWrapper?.classList.toggle('hidden', !(['waiting', 'watering'].includes(state) && isOnline));
        }

        async function refreshDeviceStatus() {
            try {
                const response = await fetch(statusUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    return;
                }

                const data = await response.json();

                setText('device-name', data.device.name);
                setText('device-type', data.device.display_type);
                setText('device-status', data.device.is_online ? 'Online' : 'Offline');
                setText('device-location', data.device.location_label);
                setText('device-timezone', data.device.timezone);
                setText('device-mode', data.device.mode_label);
                setText('enabled-schedules', data.device.enabled_schedule_count);
                setText('device-last-seen', data.device.last_seen_human);

                setText('reading-temperature', data.latest_reading.temperature ?? 'N/A');
                setText('reading-humidity', data.latest_reading.humidity ?? 'N/A');
                setText('reading-soil', data.latest_reading.soil_moisture ?? 'N/A');
                setText('reading-recorded', data.latest_reading.recorded_at ?? 'N/A');
                setText('manual-max-duration', data.manual.max_duration);

                const badge = document.getElementById('online-badge');
                if (badge) {
                    badge.textContent = data.device.is_online ? 'Online' : 'Offline';
                    badge.className = data.device.is_online ?
                        'rounded-full px-3 py-1 text-sm bg-green-100 text-green-800' :
                        'rounded-full px-3 py-1 text-sm bg-gray-200 text-gray-700';
                }

                updateManualState(data);
            } catch (error) {
                console.error('Status refresh failed:', error);
            }
        }

        refreshDeviceStatus();
        setInterval(refreshDeviceStatus, 5000);

        const flashSuccess = document.getElementById('flash-success');
        if (flashSuccess) {
            setTimeout(() => {
                flashSuccess.remove();
            }, 5000);
        }
    </script>
</body>

</html>