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

        <div class="mb-4 flex flex-wrap gap-2">
            <a href="{{ route('devices.show', $device) }}" class="rounded bg-blue-600 px-3 py-2 text-sm text-white">Home</a>
            <a href="{{ route('devices.smart-fountain.scenes.index', $device) }}" class="rounded bg-white px-3 py-2 text-sm border hover:bg-gray-50">Scenes</a>
            <a href="{{ route('devices.smart-fountain.schedules.index', $device) }}" class="rounded bg-white px-3 py-2 text-sm border hover:bg-gray-50">Schedule</a>
            <a href="{{ route('devices.history', $device) }}" class="rounded bg-white px-3 py-2 text-sm border hover:bg-gray-50">History</a>
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
            $isWaterLow = (int) $waterLow === 1;
            $waterLevel = $latestReadings->get('water_level_percent')?->value;

            $latestCommandFor = function (string $outputKey) use ($device) {
                return $device->deviceCommands->first(function ($command) use ($outputKey) {
                    if ($command->command_type === 'output_set') {
                        return data_get($command->payload, 'output') === $outputKey;
                    }

                    if ($command->command_type === 'scene_apply') {
                        return is_array(data_get($command->payload, 'outputs.' . $outputKey));
                    }

                    return false;
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
                    <span id="water-low" class="{{ is_null($waterLow) ? '' : ($isWaterLow ? 'font-semibold text-red-600' : 'font-semibold text-green-700') }}">
                        @if (is_null($waterLow))
                            N/A
                        @elseif ($isWaterLow)
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

                <div id="water-low-warning" class="{{ $isWaterLow ? '' : 'hidden' }} mt-4 rounded border border-red-300 bg-red-50 px-4 py-3 text-red-700">
                    Low water detected. Pump is locked OFF for safety. Lights can still be used.
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
                <form id="pump-control-form" data-output-form="pump" method="POST" action="{{ route('devices.outputs.set', [$device, 'pump']) }}" class="rounded-lg bg-white p-5 shadow">
                    @csrf
                    <div class="mb-4 flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold">Pump Control</h2>
                            <p class="text-sm text-gray-500">Water pump output</p>
                        </div>
                        <span id="pump-command" class="rounded-full px-2 py-1 text-xs {{ $isWaterLow ? 'bg-red-100 text-red-800' : $commandClass($pumpCommand) }}">
                            {{ $isWaterLow ? 'Pump Locked' : $commandLabel($pumpCommand) }}
                        </span>
                    </div>

                    <div id="pump-safety-note" class="{{ $isWaterLow ? '' : 'hidden' }} mb-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        Pump controls are disabled while water is low.
                    </div>

                    <div id="pump-dirty-note" class="hidden mb-4 rounded border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        Unsaved pump changes.
                        <button type="button" data-reset-output="pump" class="ml-2 font-semibold text-amber-900 underline">Reset to current</button>
                    </div>

                    <div class="mb-4 rounded border border-gray-200 bg-gray-50 px-4 py-3">
                        <p><strong>Current State:</strong> <span id="pump-state">{{ data_get($pump?->state, 'enabled') ? 'ON' : 'OFF' }}</span></p>
                        <p><strong>Speed:</strong> <span id="pump-speed">{{ data_get($pump?->state, 'speed_percent', 0) }}%</span></p>
                        <p><strong>Source:</strong> <span id="pump-source">{{ $pump?->last_changed_source ?? 'N/A' }}</span></p>
                    </div>

                    <label class="mb-3 flex items-center gap-2 {{ $isWaterLow ? 'opacity-60' : '' }}" id="pump-enabled-label">
                        <input id="pump-enabled-input" type="checkbox" name="enabled" value="1" {{ data_get($pump?->state, 'enabled') ? 'checked' : '' }} {{ $isWaterLow ? 'disabled' : '' }}>
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
                        class="mb-3 w-full rounded border px-3 py-2 {{ $isWaterLow ? 'bg-gray-100 text-gray-500' : '' }}"
                        {{ $isWaterLow ? 'disabled' : '' }}
                        required>

                    <button id="pump-submit-button" type="submit" class="rounded px-4 py-2 text-white {{ $isWaterLow ? 'cursor-not-allowed bg-gray-400' : 'bg-blue-600 hover:bg-blue-700' }}" {{ $isWaterLow ? 'disabled' : '' }}>
                        {{ $isWaterLow ? 'Pump Locked by Water Safety' : 'Send Pump Command' }}
                    </button>
                </form>

                <form data-output-form="cob_light" method="POST" action="{{ route('devices.outputs.set', [$device, 'cob_light']) }}" class="rounded-lg bg-white p-5 shadow">
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

                    <div id="cob-light-dirty-note" class="hidden mb-4 rounded border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        Unsaved COB light changes.
                        <button type="button" data-reset-output="cob_light" class="ml-2 font-semibold text-amber-900 underline">Reset to current</button>
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

                <form data-output-form="rgb_light" method="POST" action="{{ route('devices.outputs.set', [$device, 'rgb_light']) }}" class="rounded-lg bg-white p-5 shadow">
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

                    <div id="rgb-light-dirty-note" class="hidden mb-4 rounded border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        Unsaved RGB light changes.
                        <button type="button" data-reset-output="rgb_light" class="ml-2 font-semibold text-amber-900 underline">Reset to current</button>
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
        let isWaterSafetyLocked = {{ $isWaterLow ? 'true' : 'false' }};
        const dirtyForms = new Set();
        const latestOutputStates = {};

        function setText(id, value) {
            const el = document.getElementById(id);
            if (!el) return;
            el.textContent = value ?? 'N/A';
        }

        function setInputValue(id, value, force = false) {
            const el = document.getElementById(id);
            if (!el || (!force && document.activeElement === el)) return;
            el.value = value ?? '';
        }

        function setCheckbox(id, checked, force = false) {
            const el = document.getElementById(id);
            if (!el || (!force && document.activeElement === el)) return;
            el.checked = Boolean(checked);
        }

        function dirtyNoteId(outputKey) {
            return `${outputKey.replace(/_/g, '-')}-dirty-note`;
        }

        function showDirtyNote(outputKey, show) {
            document.getElementById(dirtyNoteId(outputKey))?.classList.toggle('hidden', !show);
        }

        function markFormDirty(outputKey) {
            if (outputKey === 'pump' && isWaterSafetyLocked) return;
            dirtyForms.add(outputKey);
            showDirtyNote(outputKey, true);
        }

        function clearFormDirty(outputKey) {
            dirtyForms.delete(outputKey);
            showDirtyNote(outputKey, false);
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

        function applyOutputInputs(outputKey, state, force = false) {
            if (outputKey === 'pump') {
                setCheckbox('pump-enabled-input', state.enabled, force);
                setInputValue('pump_speed_percent', state.speed_percent ?? 0, force);
            }

            if (outputKey === 'cob_light') {
                setCheckbox('cob-light-enabled-input', state.enabled, force);
                setInputValue('cob_brightness_percent', state.brightness_percent ?? 0, force);
            }

            if (outputKey === 'rgb_light') {
                setCheckbox('rgb-light-enabled-input', state.enabled, force);
                setInputValue('rgb_brightness_percent', state.brightness_percent ?? 0, force);
                setInputValue('rgb_color', state.color ?? '#FFB066', force);
                setInputValue('rgb_effect', state.effect ?? 'warm_glow', force);
            }
        }

        function resetFormToCurrent(outputKey) {
            const state = latestOutputStates[outputKey] ?? {};
            clearFormDirty(outputKey);
            applyOutputInputs(outputKey, state, true);
        }

        function updatePumpSafetyLock(isLocked) {
            isWaterSafetyLocked = Boolean(isLocked);

            const checkbox = document.getElementById('pump-enabled-input');
            const speedInput = document.getElementById('pump_speed_percent');
            const submitButton = document.getElementById('pump-submit-button');
            const safetyNote = document.getElementById('pump-safety-note');
            const label = document.getElementById('pump-enabled-label');
            const badge = document.getElementById('pump-command');

            if (isWaterSafetyLocked) {
                clearFormDirty('pump');
            }

            if (checkbox) {
                checkbox.disabled = isWaterSafetyLocked;
                if (isWaterSafetyLocked) checkbox.checked = false;
            }

            if (speedInput) {
                speedInput.disabled = isWaterSafetyLocked;
                speedInput.classList.toggle('bg-gray-100', isWaterSafetyLocked);
                speedInput.classList.toggle('text-gray-500', isWaterSafetyLocked);
                if (isWaterSafetyLocked) speedInput.value = 0;
            }

            if (submitButton) {
                submitButton.disabled = isWaterSafetyLocked;
                submitButton.textContent = isWaterSafetyLocked ? 'Pump Locked by Water Safety' : 'Send Pump Command';
                submitButton.className = isWaterSafetyLocked
                    ? 'rounded px-4 py-2 text-white cursor-not-allowed bg-gray-400'
                    : 'rounded px-4 py-2 text-white bg-blue-600 hover:bg-blue-700';
            }

            safetyNote?.classList.toggle('hidden', !isWaterSafetyLocked);
            label?.classList.toggle('opacity-60', isWaterSafetyLocked);

            if (badge && isWaterSafetyLocked) {
                badge.textContent = 'Pump Locked';
                badge.className = 'rounded-full px-2 py-1 text-xs bg-red-100 text-red-800';
            }
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
            const locked = Number(waterLow) === 1;

            if (waterLowEl) {
                if (waterLow === null || waterLow === undefined) {
                    waterLowEl.textContent = 'N/A';
                    waterLowEl.className = '';
                } else if (locked) {
                    waterLowEl.textContent = 'Yes';
                    waterLowEl.className = 'font-semibold text-red-600';
                } else {
                    waterLowEl.textContent = 'No';
                    waterLowEl.className = 'font-semibold text-green-700';
                }
            }

            setText('water-level', waterLevel === null || waterLevel === undefined ? 'N/A' : `${Number(waterLevel).toFixed(0)}%`);
            document.getElementById('water-low-warning')?.classList.toggle('hidden', !locked);
            updatePumpSafetyLock(locked);
        }

        function updateOutputCards(outputs) {
            const pump = outputs.pump ?? {};
            const pumpState = pump.state ?? {};
            latestOutputStates.pump = pumpState;
            setText('pump-state', pumpState.enabled ? 'ON' : 'OFF');
            setText('pump-speed', `${pumpState.speed_percent ?? 0}%`);
            setText('pump-source', pump.last_changed_source ?? 'N/A');

            if (!isWaterSafetyLocked) {
                updateCommandBadge('pump-command', pump.last_command);
                if (!dirtyForms.has('pump')) {
                    applyOutputInputs('pump', pumpState);
                }
            }

            const cob = outputs.cob_light ?? {};
            const cobState = cob.state ?? {};
            latestOutputStates.cob_light = cobState;
            setText('cob-light-state', cobState.enabled ? 'ON' : 'OFF');
            setText('cob-light-brightness', `${cobState.brightness_percent ?? 0}%`);
            setText('cob-light-source', cob.last_changed_source ?? 'N/A');
            updateCommandBadge('cob-light-command', cob.last_command);
            if (!dirtyForms.has('cob_light')) {
                applyOutputInputs('cob_light', cobState);
            }

            const rgb = outputs.rgb_light ?? {};
            const rgbState = rgb.state ?? {};
            latestOutputStates.rgb_light = rgbState;
            setText('rgb-light-state', rgbState.enabled ? 'ON' : 'OFF');
            setText('rgb-light-brightness', `${rgbState.brightness_percent ?? 0}%`);
            setText('rgb-light-color', rgbState.color ?? 'N/A');
            setText('rgb-light-effect', (rgbState.effect ?? 'N/A').replace(/_/g, ' '));
            setText('rgb-light-source', rgb.last_changed_source ?? 'N/A');
            updateCommandBadge('rgb-light-command', rgb.last_command);
            if (!dirtyForms.has('rgb_light')) {
                applyOutputInputs('rgb_light', rgbState);
            }
        }

        function initializeDirtyFormTracking() {
            document.querySelectorAll('[data-output-form]').forEach((form) => {
                const outputKey = form.dataset.outputForm;

                form.addEventListener('input', (event) => {
                    if (event.target.closest('[data-reset-output]')) return;
                    markFormDirty(outputKey);
                });

                form.addEventListener('change', (event) => {
                    if (event.target.closest('[data-reset-output]')) return;
                    markFormDirty(outputKey);
                });

                form.addEventListener('submit', () => {
                    clearFormDirty(outputKey);
                });
            });

            document.querySelectorAll('[data-reset-output]').forEach((button) => {
                button.addEventListener('click', () => resetFormToCurrent(button.dataset.resetOutput));
            });
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

        initializeDirtyFormTracking();
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
