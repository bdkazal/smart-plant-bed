<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $device->name }} - Smart Fountain</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="max-w-6xl mx-auto py-6 px-4">
        <div class="mb-4">
            <a href="{{ route('devices.index') }}" class="text-blue-600 hover:underline">← Back to Devices</a>
        </div>

        <div class="mb-6 flex items-center justify-between gap-4">
            <div>
                <h1 id="device-name" class="text-2xl font-bold">{{ $device->name }}</h1>
                <p id="device-type-heading" class="text-gray-600">{{ $device->displayType() }}</p>
            </div>

            <div id="online-badge" class="rounded-full px-3 py-1 text-sm {{ $isOnline ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-700' }}">
                {{ $isOnline ? 'Online' : 'Offline' }}
            </div>
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

        @php
            $pump = $outputs->get('pump');
            $cobLight = $outputs->get('cob_light');
            $rgbLight = $outputs->get('rgb_light');

            $waterLow = $latestReadings->get('water_low')?->value;
            $waterLevel = $latestReadings->get('water_level_percent')?->value;

            $latestCommandFor = function (string $outputKey) use ($device) {
                return $device->deviceCommands->first(function ($command) use ($outputKey) {
                    return $command->command_type === 'output_set'
                        && data_get($command->payload, 'output') === $outputKey;
                });
            };

            $commandLabel = function ($command) {
                if (! $command) {
                    return 'None yet';
                }

                return match ($command->status) {
                    'pending' => 'Waiting for device',
                    'acknowledged' => 'Applying',
                    'executed' => 'Applied',
                    'failed' => 'Failed',
                    'expired' => 'Expired',
                    default => ucfirst($command->status),
                };
            };

            $commandClass = function ($command) {
                if (! $command) {
                    return 'bg-gray-100 text-gray-700';
                }

                return match ($command->status) {
                    'pending' => 'bg-yellow-100 text-yellow-800',
                    'acknowledged' => 'bg-blue-100 text-blue-800',
                    'executed' => 'bg-green-100 text-green-800',
                    'failed', 'expired' => 'bg-red-100 text-red-800',
                    default => 'bg-gray-100 text-gray-700',
                };
            };

            $pumpCommand = $latestCommandFor('pump');
            $cobLightCommand = $latestCommandFor('cob_light');
            $rgbLightCommand = $latestCommandFor('rgb_light');
        @endphp

        <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-lg bg-white p-5 shadow">
                <h2 class="mb-3 text-lg font-semibold">Device Status</h2>
                <p><strong>Status:</strong> <span id="device-status">{{ $isOnline ? 'Online' : 'Offline' }}</span></p>
                <p><strong>Type:</strong> <span id="device-type">{{ $device->displayType() }}</span></p>
                <p><strong>Location:</strong> <span id="device-location">{{ $device->location_label ?? 'N/A' }}</span></p>
                <p><strong>Timezone:</strong> <span id="device-timezone">{{ $device->timezone ?? 'Asia/Dhaka' }}</span></p>
                <p><strong>Last Seen:</strong> <span id="device-last-seen">{{ $device->last_seen_at?->diffForHumans() ?? 'Never' }}</span></p>
            </div>

            <div class="rounded-lg bg-white p-5 shadow">
                <h2 class="mb-3 text-lg font-semibold">Water Safety</h2>

                <p>
                    <strong>Water Low:</strong>
                    <span id="water-low" class="{{ is_null($waterLow) ? '' : ((int) $waterLow === 1 ? 'font-semibold text-red-600' : 'font-semibold text-green-700') }}">
                        @if (is_null($waterLow))
                            N/A
                        @elseif ((int) $waterLow === 1)
                            Yes
                        @else
                            No
                        @endif
                    </span>
                </p>

                <p>
                    <strong>Water Level:</strong>
                    <span id="water-level">{{ is_null($waterLevel) ? 'N/A' : number_format($waterLevel, 0) . '%' }}</span>
                </p>

                <div id="water-low-warning" class="{{ (int) $waterLow === 1 ? '' : 'hidden' }} mt-4 rounded border border-red-300 bg-red-50 px-4 py-3 text-red-700">
                    Low water detected. Pump should stay OFF to protect the motor.
                </div>
            </div>
        </div>

        <div id="offline-note" class="{{ $isOnline ? 'hidden' : '' }} mt-4 rounded border border-yellow-300 bg-yellow-50 px-4 py-3 text-yellow-800">
            Device is offline. Live output commands are still enabled for backend testing; before customer release, this should be changed to block live controls while offline.
        </div>

        @if ($device->status !== 'active')
            <div class="mt-4 rounded border border-yellow-300 bg-yellow-50 px-4 py-3 text-yellow-800">
                This device is not active in the account yet. Output commands are disabled.
            </div>
        @else
            <div class="mt-4 grid gap-4 md:grid-cols-3">
                <form method="POST" action="{{ route('devices.outputs.set', [$device, 'pump']) }}" class="rounded-lg bg-white p-5 shadow">
                    @csrf
                    <div class="mb-4 flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold">Pump Control</h2>
                            <p class="text-sm text-gray-500">Water pump output</p>
                        </div>
                        <span id="pump-command" class="rounded-full px-2 py-1 text-xs {{ $commandClass($pumpCommand) }}">
                            {{ $commandLabel($pumpCommand) }}
                        </span>
                    </div>

                    <div class="mb-4 rounded border border-gray-200 bg-gray-50 px-4 py-3">
                        <p><strong>Current State:</strong> <span id="pump-state">{{ data_get($pump?->state, 'enabled') ? 'ON' : 'OFF' }}</span></p>
                        <p><strong>Speed:</strong> <span id="pump-speed">{{ data_get($pump?->state, 'speed_percent', 0) }}%</span></p>
                        <p><strong>Source:</strong> <span id="pump-source">{{ $pump?->last_changed_source ?? 'N/A' }}</span></p>
                    </div>

                    <label class="mb-3 flex items-center gap-2">
                        <input id="pump-enabled-input" type="checkbox" name="enabled" value="1" {{ data_get($pump?->state, 'enabled') ? 'checked' : '' }}>
                        <span>Enable pump</span>
                    </label>

                    <label for="pump_speed_percent" class="mb-1 block text-sm font-medium">Speed (%)</label>
                    <input
                        id="pump_speed_percent"
                        type="number"
                        name="speed_percent"
                        min="0"
                        max="100"
                        value="{{ old('speed_percent', data_get($pump?->state, 'speed_percent', 0)) }}"
                        class="mb-3 w-full rounded border px-3 py-2"
                        required>

                    <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                        Send Pump Command
                    </button>
                </form>

                <form method="POST" action="{{ route('devices.outputs.set', [$device, 'cob_light']) }}" class="rounded-lg bg-white p-5 shadow">
                    @csrf
                    <div class="mb-4 flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold">COB Light Control</h2>
                            <p class="text-sm text-gray-500">Main white light</p>
                        </div>
                        <span id="cob-light-command" class="rounded-full px-2 py-1 text-xs {{ $commandClass($cobLightCommand) }}">
                            {{ $commandLabel($cobLightCommand) }}
                        </span>
                    </div>

                    <div class="mb-4 rounded border border-gray-200 bg-gray-50 px-4 py-3">
                        <p><strong>Current State:</strong> <span id="cob-light-state">{{ data_get($cobLight?->state, 'enabled') ? 'ON' : 'OFF' }}</span></p>
                        <p><strong>Brightness:</strong> <span id="cob-light-brightness">{{ data_get($cobLight?->state, 'brightness_percent', 0) }}%</span></p>
                        <p><strong>Source:</strong> <span id="cob-light-source">{{ $cobLight?->last_changed_source ?? 'N/A' }}</span></p>
                    </div>

                    <label class="mb-3 flex items-center gap-2">
                        <input id="cob-light-enabled-input" type="checkbox" name="enabled" value="1" {{ data_get($cobLight?->state, 'enabled') ? 'checked' : '' }}>
                        <span>Enable COB light</span>
                    </label>

                    <label for="cob_brightness_percent" class="mb-1 block text-sm font-medium">Brightness (%)</label>
                    <input
                        id="cob_brightness_percent"
                        type="number"
                        name="brightness_percent"
                        min="0"
                        max="100"
                        value="{{ old('brightness_percent', data_get($cobLight?->state, 'brightness_percent', 0)) }}"
                        class="mb-3 w-full rounded border px-3 py-2"
                        required>

                    <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                        Send COB Command
                    </button>
                </form>

                <form method="POST" action="{{ route('devices.outputs.set', [$device, 'rgb_light']) }}" class="rounded-lg bg-white p-5 shadow">
                    @csrf
                    <div class="mb-4 flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold">RGB Light Control</h2>
                            <p class="text-sm text-gray-500">Decorative light</p>
                        </div>
                        <span id="rgb-light-command" class="rounded-full px-2 py-1 text-xs {{ $commandClass($rgbLightCommand) }}">
                            {{ $commandLabel($rgbLightCommand) }}
                        </span>
                    </div>

                    <div class="mb-4 rounded border border-gray-200 bg-gray-50 px-4 py-3">
                        <p><strong>Current State:</strong> <span id="rgb-light-state">{{ data_get($rgbLight?->state, 'enabled') ? 'ON' : 'OFF' }}</span></p>
                        <p><strong>Brightness:</strong> <span id="rgb-light-brightness">{{ data_get($rgbLight?->state, 'brightness_percent', 0) }}%</span></p>
                        <p><strong>Color:</strong> <span id="rgb-light-color">{{ data_get($rgbLight?->state, 'color', 'N/A') }}</span></p>
                        <p><strong>Effect:</strong> <span id="rgb-light-effect">{{ str_replace('_', ' ', data_get($rgbLight?->state, 'effect', 'N/A')) }}</span></p>
                        <p><strong>Source:</strong> <span id="rgb-light-source">{{ $rgbLight?->last_changed_source ?? 'N/A' }}</span></p>
                    </div>

                    <label class="mb-3 flex items-center gap-2">
                        <input id="rgb-light-enabled-input" type="checkbox" name="enabled" value="1" {{ data_get($rgbLight?->state, 'enabled') ? 'checked' : '' }}>
                        <span>Enable RGB light</span>
                    </label>

                    <label for="rgb_brightness_percent" class="mb-1 block text-sm font-medium">Brightness (%)</label>
                    <input
                        id="rgb_brightness_percent"
                        type="number"
                        name="brightness_percent"
                        min="0"
                        max="100"
                        value="{{ old('brightness_percent', data_get($rgbLight?->state, 'brightness_percent', 0)) }}"
                        class="mb-3 w-full rounded border px-3 py-2"
                        required>

                    <label for="rgb_color" class="mb-1 block text-sm font-medium">Color</label>
                    <input
                        id="rgb_color"
                        type="color"
                        name="color"
                        value="{{ old('color', data_get($rgbLight?->state, 'color', '#FFB066')) }}"
                        class="mb-3 h-10 w-full rounded border px-2 py-1"
                        required>

                    <label for="rgb_effect" class="mb-1 block text-sm font-medium">Effect</label>
                    <select id="rgb_effect" name="effect" class="mb-3 w-full rounded border px-3 py-2" required>
                        @foreach (['solid', 'breathing', 'slow_rainbow', 'warm_glow', 'water_shimmer', 'night_mode'] as $effect)
                            <option value="{{ $effect }}" @selected(old('effect', data_get($rgbLight?->state, 'effect', 'warm_glow')) === $effect)>
                                {{ ucwords(str_replace('_', ' ', $effect)) }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                        Send RGB Command
                    </button>
                </form>
            </div>
        @endif
    </div>

    <script>
        const smartFountainStatusUrl = "{{ route('devices.smart-fountain.status', $device) }}";

        function setText(id, value) {
            const el = document.getElementById(id);
            if (!el) return;
            el.textContent = value ?? 'N/A';
        }

        function setInputValue(id, value) {
            const el = document.getElementById(id);
            if (!el || document.activeElement === el) return;
            el.value = value ?? '';
        }

        function setCheckbox(id, checked) {
            const el = document.getElementById(id);
            if (!el || document.activeElement === el) return;
            el.checked = Boolean(checked);
        }

        function commandBadgeClass(status) {
            if (status === 'pending') return 'rounded-full px-2 py-1 text-xs bg-yellow-100 text-yellow-800';
            if (status === 'acknowledged') return 'rounded-full px-2 py-1 text-xs bg-blue-100 text-blue-800';
            if (status === 'executed') return 'rounded-full px-2 py-1 text-xs bg-green-100 text-green-800';
            if (status === 'failed' || status === 'expired') return 'rounded-full px-2 py-1 text-xs bg-red-100 text-red-800';
            return 'rounded-full px-2 py-1 text-xs bg-gray-100 text-gray-700';
        }

        function updateCommandBadge(id, command) {
            const el = document.getElementById(id);
            if (!el) return;

            el.textContent = command?.status_label ?? 'None yet';
            el.className = commandBadgeClass(command?.status);
        }

        function updateOnlineStatus(device) {
            setText('device-name', device.name);
            setText('device-type-heading', device.display_type);
            setText('device-status', device.is_online ? 'Online' : 'Offline');
            setText('device-type', device.display_type);
            setText('device-location', device.location_label);
            setText('device-timezone', device.timezone);
            setText('device-last-seen', device.last_seen_human);

            const badge = document.getElementById('online-badge');
            if (badge) {
                badge.textContent = device.is_online ? 'Online' : 'Offline';
                badge.className = device.is_online ?
                    'rounded-full px-3 py-1 text-sm bg-green-100 text-green-800' :
                    'rounded-full px-3 py-1 text-sm bg-gray-200 text-gray-700';
            }

            document.getElementById('offline-note')?.classList.toggle('hidden', device.is_online);
        }

        function updateWaterSafety(readings) {
            const waterLow = readings.water_low;
            const waterLevel = readings.water_level_percent;
            const waterLowEl = document.getElementById('water-low');

            if (waterLowEl) {
                if (waterLow === null || waterLow === undefined) {
                    waterLowEl.textContent = 'N/A';
                    waterLowEl.className = '';
                } else if (Number(waterLow) === 1) {
                    waterLowEl.textContent = 'Yes';
                    waterLowEl.className = 'font-semibold text-red-600';
                } else {
                    waterLowEl.textContent = 'No';
                    waterLowEl.className = 'font-semibold text-green-700';
                }
            }

            setText('water-level', waterLevel === null || waterLevel === undefined ? 'N/A' : `${Number(waterLevel).toFixed(0)}%`);
            document.getElementById('water-low-warning')?.classList.toggle('hidden', Number(waterLow) !== 1);
        }

        function updateOutputCards(outputs) {
            const pump = outputs.pump ?? {};
            const pumpState = pump.state ?? {};
            setText('pump-state', pumpState.enabled ? 'ON' : 'OFF');
            setText('pump-speed', `${pumpState.speed_percent ?? 0}%`);
            setText('pump-source', pump.last_changed_source ?? 'N/A');
            updateCommandBadge('pump-command', pump.last_command);
            setCheckbox('pump-enabled-input', pumpState.enabled);
            setInputValue('pump_speed_percent', pumpState.speed_percent ?? 0);

            const cob = outputs.cob_light ?? {};
            const cobState = cob.state ?? {};
            setText('cob-light-state', cobState.enabled ? 'ON' : 'OFF');
            setText('cob-light-brightness', `${cobState.brightness_percent ?? 0}%`);
            setText('cob-light-source', cob.last_changed_source ?? 'N/A');
            updateCommandBadge('cob-light-command', cob.last_command);
            setCheckbox('cob-light-enabled-input', cobState.enabled);
            setInputValue('cob_brightness_percent', cobState.brightness_percent ?? 0);

            const rgb = outputs.rgb_light ?? {};
            const rgbState = rgb.state ?? {};
            setText('rgb-light-state', rgbState.enabled ? 'ON' : 'OFF');
            setText('rgb-light-brightness', `${rgbState.brightness_percent ?? 0}%`);
            setText('rgb-light-color', rgbState.color ?? 'N/A');
            setText('rgb-light-effect', (rgbState.effect ?? 'N/A').replace(/_/g, ' '));
            setText('rgb-light-source', rgb.last_changed_source ?? 'N/A');
            updateCommandBadge('rgb-light-command', rgb.last_command);
            setCheckbox('rgb-light-enabled-input', rgbState.enabled);
            setInputValue('rgb_brightness_percent', rgbState.brightness_percent ?? 0);
            setInputValue('rgb_color', rgbState.color ?? '#FFB066');
            setInputValue('rgb_effect', rgbState.effect ?? 'warm_glow');
        }

        async function refreshSmartFountainStatus() {
            try {
                const response = await fetch(smartFountainStatusUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) return;

                const data = await response.json();

                updateOnlineStatus(data.device);
                updateWaterSafety(data.readings ?? {});
                updateOutputCards(data.outputs ?? {});
            } catch (error) {
                console.error('Smart Fountain status refresh failed:', error);
            }
        }

        refreshSmartFountainStatus();
        setInterval(refreshSmartFountainStatus, 5000);

        const flashSuccess = document.getElementById('flash-success');
        if (flashSuccess) {
            setTimeout(() => {
                flashSuccess.remove();
            }, 5000);
        }
    </script>
</body>

</html>
