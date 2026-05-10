<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $device->name }} - Device Home</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .soil-gauge {
            background: conic-gradient(#2563eb var(--soil-percent, 0%), #e5e7eb 0);
        }
    </style>
</head>

<body class="min-h-screen bg-slate-100 text-slate-900">
    <div class="mx-auto max-w-5xl px-4 py-5 sm:py-8">
        <div class="mb-4">
            <a href="{{ route('devices.index') }}" class="text-sm font-medium text-blue-600 hover:underline">← Back to Devices</a>
        </div>

        <div class="mb-5 flex flex-wrap gap-2">
            <a href="{{ route('devices.show', $device) }}" class="rounded-full bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm">Home</a>
            <a href="{{ route('devices.automation', $device) }}" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">Automation</a>
            <a href="{{ route('devices.schedules.index', $device) }}" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">Schedules</a>
            <a href="{{ route('devices.history', $device) }}" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">History</a>
        </div>

        @if (session('success'))
        <div id="flash-success" class="mb-4 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-green-800 shadow-sm">
            {{ session('success') }}
        </div>
        @endif

        @if ($errors->any())
        <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-800 shadow-sm">
            <ul class="ml-5 list-disc">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @php
            $soilValue = is_null($latestReading?->soil_moisture) ? 0 : max(0, min(100, (int) $latestReading->soil_moisture));
            $soilStatus = $soilValue === 0 && is_null($latestReading?->soil_moisture)
                ? 'No reading yet'
                : ($soilValue < 35 ? 'Dry' : ($soilValue > 85 ? 'Wet' : 'Optimal'));
            $soilStatusClass = $soilStatus === 'Dry'
                ? 'text-amber-700 bg-amber-100'
                : ($soilStatus === 'Wet' ? 'text-blue-700 bg-blue-100' : 'text-emerald-700 bg-emerald-100');
            $manualStateClass = match ($manualWateringState) {
                'waiting' => 'bg-yellow-100 text-yellow-800',
                'watering' => 'bg-blue-100 text-blue-800',
                'stopping' => 'bg-red-100 text-red-800',
                default => 'bg-slate-100 text-slate-700',
            };
        @endphp

        <section class="mb-5 overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-slate-900 to-blue-950 p-5 text-white shadow-xl sm:p-7">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="mb-1 text-sm font-medium text-blue-200">Smart Plant Bed</p>
                    <h1 id="device-name" class="text-2xl font-bold sm:text-3xl">{{ $device->name }}</h1>
                    <p class="mt-1 text-sm text-slate-300">
                        <span id="device-location">{{ $device->location_label ?? 'N/A' }}</span>
                        <span class="mx-1">•</span>
                        <span id="device-type">{{ $device->displayType() }}</span>
                    </p>
                </div>

                <div class="text-right">
                    <p class="mb-1 text-xs uppercase tracking-wide text-slate-400">Status</p>
                    <div id="online-badge" class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold {{ $isOnline ? 'bg-green-100 text-green-800' : 'bg-slate-200 text-slate-700' }}">
                        {{ $isOnline ? 'Live' : 'Offline' }}
                    </div>
                </div>
            </div>

            <div class="mt-6 grid gap-3 sm:grid-cols-3">
                <div class="rounded-2xl bg-white/10 p-4 backdrop-blur">
                    <p class="text-xs uppercase tracking-wide text-slate-300">Mode</p>
                    <p id="device-mode" class="mt-1 text-lg font-bold">{{ ucfirst($device->wateringRule?->watering_mode ?? 'schedule') }}</p>
                </div>
                <div class="rounded-2xl bg-white/10 p-4 backdrop-blur">
                    <p class="text-xs uppercase tracking-wide text-slate-300">Enabled Schedules</p>
                    <p id="enabled-schedules" class="mt-1 text-lg font-bold">{{ $enabledScheduleCount }}</p>
                </div>
                <div class="rounded-2xl bg-white/10 p-4 backdrop-blur">
                    <p class="text-xs uppercase tracking-wide text-slate-300">Last Seen</p>
                    <p id="device-last-seen" class="mt-1 text-lg font-bold">{{ $device->last_seen_at?->diffForHumans() ?? 'Never' }}</p>
                </div>
            </div>
        </section>

        <div class="grid gap-5 lg:grid-cols-[1.15fr_0.85fr]">
            <section class="rounded-3xl bg-white p-5 shadow-sm sm:p-6">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold">Soil Moisture</h2>
                        <p class="text-sm text-slate-500">Latest sensor reading</p>
                    </div>
                    <span id="soil-status-badge" class="rounded-full px-3 py-1 text-sm font-semibold {{ $soilStatusClass }}">{{ $soilStatus }}</span>
                </div>

                <div class="flex flex-col items-center justify-center py-3">
                    <div id="soil-gauge" class="soil-gauge flex h-52 w-52 items-center justify-center rounded-full shadow-inner" style="--soil-percent: {{ $soilValue }}%;">
                        <div class="flex h-40 w-40 flex-col items-center justify-center rounded-full bg-white shadow">
                            <div class="text-5xl font-black tracking-tight"><span id="reading-soil">{{ $latestReading?->soil_moisture ?? 'N/A' }}</span><span class="text-3xl">%</span></div>
                            <div class="mt-1 text-sm font-medium text-slate-500">Soil level</div>
                        </div>
                    </div>
                </div>

                <p class="mt-4 text-center text-sm text-slate-500">
                    Recorded: <span id="reading-recorded">{{ $latestReading?->recorded_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</span>
                </p>
            </section>

            <section class="grid gap-5">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                    <div class="rounded-3xl bg-white p-5 shadow-sm">
                        <div class="mb-2 flex items-center gap-2 text-sm text-slate-500">
                            <span class="rounded-full bg-blue-100 px-2 py-1 text-blue-700">℃</span>
                            Air Temperature
                        </div>
                        <div class="text-3xl font-black"><span id="reading-temperature">{{ $latestReading?->temperature ?? 'N/A' }}</span> °C</div>
                    </div>

                    <div class="rounded-3xl bg-white p-5 shadow-sm">
                        <div class="mb-2 flex items-center gap-2 text-sm text-slate-500">
                            <span class="rounded-full bg-cyan-100 px-2 py-1 text-cyan-700">%</span>
                            Humidity
                        </div>
                        <div class="text-3xl font-black"><span id="reading-humidity">{{ $latestReading?->humidity ?? 'N/A' }}</span>%</div>
                    </div>
                </div>

                <div class="rounded-3xl bg-white p-5 shadow-sm">
                    <h2 class="mb-3 text-lg font-bold">Device Details</h2>
                    <div class="space-y-2 text-sm text-slate-700">
                        <p><span class="font-semibold">Connection:</span> <span id="device-status">{{ $isOnline ? 'Online' : 'Offline' }}</span></p>
                        <p><span class="font-semibold">Timezone:</span> <span id="device-timezone">{{ $device->timezone ?? 'Asia/Dhaka' }}</span></p>
                    </div>
                </div>
            </section>
        </div>

        <section class="mt-5 rounded-3xl bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold">Control Center</h2>
                    <p class="text-sm text-slate-500">Manual watering control</p>
                </div>
                <span id="manual-state-badge" class="rounded-full px-3 py-1 text-sm font-semibold {{ $manualStateClass }}">
                    {{ ucfirst($manualWateringState) }}
                </span>
            </div>

            <div class="rounded-3xl bg-slate-950 p-5 text-white shadow-lg">
                <div class="mb-4 flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold">Pump Control</h3>
                        <p class="text-sm text-slate-400">Watering State: <span id="manual-state-text">{{ ucfirst($manualWateringState) }}</span></p>
                    </div>
                    <div id="started-by-row" class="{{ in_array($manualWateringState, ['waiting', 'watering', 'stopping'], true) ? '' : 'hidden' }} text-right text-sm text-slate-300">
                        Started By:<br>
                        <span id="started-by-text" class="font-semibold text-white">N/A</span>
                    </div>
                </div>

                <div id="manual-offline-note" class="{{ $isOnline ? 'hidden' : '' }} mb-4 rounded-2xl border border-yellow-400/40 bg-yellow-300/10 px-4 py-3 text-yellow-100">
                    Device is offline. Manual watering is unavailable right now.
                </div>

                <div id="start-form-wrapper" class="{{ $manualWateringState === 'idle' && $isOnline ? '' : 'hidden' }}">
                    <form action="{{ route('devices.water-now', $device) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="duration_seconds" class="mb-1 block text-sm font-medium text-slate-300">Duration (seconds)</label>
                            <input
                                type="number"
                                name="duration_seconds"
                                id="duration_seconds"
                                min="1"
                                max="{{ $manualMaxDuration }}"
                                value="{{ old('duration_seconds', min(30, $manualMaxDuration)) }}"
                                class="w-full rounded-2xl border border-white/10 bg-white/95 px-4 py-3 text-slate-900 outline-none focus:ring-2 focus:ring-blue-400 sm:w-64"
                                required>
                            <p class="mt-2 text-sm text-slate-400">
                                Maximum allowed: <span id="manual-max-duration">{{ $manualMaxDuration }}</span> seconds
                            </p>
                        </div>

                        <button type="submit" class="w-full rounded-2xl bg-blue-500 px-5 py-3 font-bold text-white shadow hover:bg-blue-600 sm:w-auto">
                            Start Manual Watering
                        </button>
                    </form>
                </div>

                <div id="stop-form-wrapper" class="{{ in_array($manualWateringState, ['waiting', 'watering'], true) && $isOnline ? '' : 'hidden' }}">
                    <form action="{{ route('devices.water-stop', $device) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full rounded-2xl bg-red-500 px-5 py-3 font-bold text-white shadow hover:bg-red-600 sm:w-auto">
                            Stop Watering
                        </button>
                    </form>
                </div>
            </div>
        </section>

        <div class="mt-5 grid gap-4 sm:grid-cols-3">
            <a href="{{ route('devices.automation', $device) }}" class="rounded-3xl bg-white p-5 shadow-sm hover:bg-slate-50">
                <h3 class="font-bold">Automation</h3>
                <p class="mt-2 text-sm text-slate-500">Mode, threshold, cooldown, and durations.</p>
            </a>

            <a href="{{ route('devices.schedules.index', $device) }}" class="rounded-3xl bg-white p-5 shadow-sm hover:bg-slate-50">
                <h3 class="font-bold">Schedules</h3>
                <p class="mt-2 text-sm text-slate-500">Manage scheduled watering times.</p>
            </a>

            <a href="{{ route('devices.history', $device) }}" class="rounded-3xl bg-white p-5 shadow-sm hover:bg-slate-50">
                <h3 class="font-bold">History</h3>
                <p class="mt-2 text-sm text-slate-500">Recent logs and device commands.</p>
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

        function soilStatus(value) {
            if (value === null || value === undefined || value === 'N/A') return { label: 'No reading yet', classes: 'rounded-full px-3 py-1 text-sm font-semibold text-slate-700 bg-slate-100' };
            const number = Number(value);
            if (number < 35) return { label: 'Dry', classes: 'rounded-full px-3 py-1 text-sm font-semibold text-amber-700 bg-amber-100' };
            if (number > 85) return { label: 'Wet', classes: 'rounded-full px-3 py-1 text-sm font-semibold text-blue-700 bg-blue-100' };
            return { label: 'Optimal', classes: 'rounded-full px-3 py-1 text-sm font-semibold text-emerald-700 bg-emerald-100' };
        }

        function updateSoilGauge(value) {
            const gauge = document.getElementById('soil-gauge');
            const badge = document.getElementById('soil-status-badge');
            const number = Number(value);
            const percent = Number.isFinite(number) ? Math.max(0, Math.min(100, number)) : 0;

            if (gauge) {
                gauge.style.setProperty('--soil-percent', `${percent}%`);
            }

            if (badge) {
                const status = soilStatus(value);
                badge.textContent = status.label;
                badge.className = status.classes;
            }
        }

        function manualBadgeClass(state) {
            if (state === 'waiting') return 'rounded-full px-3 py-1 text-sm font-semibold bg-yellow-100 text-yellow-800';
            if (state === 'watering') return 'rounded-full px-3 py-1 text-sm font-semibold bg-blue-100 text-blue-800';
            if (state === 'stopping') return 'rounded-full px-3 py-1 text-sm font-semibold bg-red-100 text-red-800';
            return 'rounded-full px-3 py-1 text-sm font-semibold bg-slate-100 text-slate-700';
        }

        function updateManualState(data) {
            const state = data.manual.state;
            const isOnline = data.device.is_online;

            const stateText = document.getElementById('manual-state-text');
            const stateBadge = document.getElementById('manual-state-badge');
            const startWrapper = document.getElementById('start-form-wrapper');
            const stopWrapper = document.getElementById('stop-form-wrapper');
            const offlineNote = document.getElementById('manual-offline-note');
            const startedByRow = document.getElementById('started-by-row');
            const startedByText = document.getElementById('started-by-text');

            let label = 'Idle';
            if (state === 'waiting') label = 'Waiting';
            if (state === 'watering') label = 'Watering';
            if (state === 'stopping') label = 'Stopping';

            if (stateText) stateText.textContent = label;
            if (stateBadge) {
                stateBadge.textContent = label;
                stateBadge.className = manualBadgeClass(state);
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
                setText('device-status', data.device.is_online ? 'Online' : 'Offline');
                setText('device-type', data.device.display_type);
                setText('device-location', data.device.location_label);
                setText('device-timezone', data.device.timezone);
                setText('device-mode', data.device.mode_label);
                setText('enabled-schedules', data.device.enabled_schedule_count);
                setText('device-last-seen', data.device.last_seen_human);

                const soilValue = data.latest_reading.soil_moisture ?? 'N/A';
                setText('reading-temperature', data.latest_reading.temperature ?? 'N/A');
                setText('reading-humidity', data.latest_reading.humidity ?? 'N/A');
                setText('reading-soil', soilValue);
                setText('reading-recorded', data.latest_reading.recorded_at ?? 'N/A');
                setText('manual-max-duration', data.manual.max_duration);
                updateSoilGauge(soilValue);

                const badge = document.getElementById('online-badge');
                if (badge) {
                    badge.textContent = data.device.is_online ? 'Live' : 'Offline';
                    badge.className = data.device.is_online ?
                        'inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold bg-green-100 text-green-800' :
                        'inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold bg-slate-200 text-slate-700';
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