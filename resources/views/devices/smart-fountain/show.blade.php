<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $device->name }} - Smart Fountain</title>
    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/smart-fountain-dashboard.js',
    ])

    <style>
        :root {
            --page-bg: #eaf1f8;
            --ink: #0f172a;
            --muted: #64748b;
            --line: rgba(148, 163, 184, .28);
            --blue: #1687f9;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            color: var(--ink);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at top left, rgba(14, 165, 233, .18), transparent 32rem),
                radial-gradient(circle at bottom right, rgba(59, 130, 246, .12), transparent 28rem),
                linear-gradient(135deg, #f8fbff 0%, var(--page-bg) 55%, #dce8f5 100%);
        }

        .hidden { display: none !important; }
        .page-shell { width: min(100%, 980px); margin: 0 auto; padding: 18px 14px 38px; }
        .back-link { display: inline-flex; margin: 0 0 14px; color: #2563eb; font-size: 14px; font-weight: 750; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
        .tabs { display: flex; gap: 9px; overflow-x: auto; padding-bottom: 12px; margin-bottom: 8px; }
        .tab {
            flex: 0 0 auto; min-height: 40px; display: inline-flex; align-items: center; justify-content: center;
            padding: 0 16px; border-radius: 999px; border: 1px solid var(--line); background: rgba(255,255,255,.82);
            color: var(--ink); font-size: 14px; font-weight: 850; text-decoration: none; box-shadow: 0 10px 26px rgba(15,23,42,.08);
        }
        .tab.active { background: var(--blue); border-color: var(--blue); color: #fff; }
        .notice { margin-bottom: 14px; border-radius: 18px; padding: 13px 15px; font-size: 14px; font-weight: 750; }
        .notice.success { background: #dcfce7; color: #166534; }
        .notice.error { background: #fee2e2; color: #991b1b; }

        .phone-frame { max-width: 480px; margin: 0 auto; padding: 16px; border-radius: 42px; background: rgba(255,255,255,.72); box-shadow: 0 34px 80px rgba(15,23,42,.16), inset 0 0 0 1px rgba(255,255,255,.92); }
        .app-screen { overflow: hidden; border-radius: 30px; min-height: 760px; background: linear-gradient(180deg, rgba(255,255,255,.96), rgba(239,246,255,.95)); border: 1px solid rgba(226,232,240,.95); }
        .app-content { padding: 20px 16px 18px; }

        .hero { position: relative; overflow: hidden; margin-bottom: 14px; padding: 18px; border-radius: 28px; color: #fff; background: radial-gradient(circle at 82% 18%, rgba(56,189,248,.55), transparent 8rem), radial-gradient(circle at 86% 86%, rgba(34,197,94,.18), transparent 9rem), linear-gradient(145deg, #132033 0%, #061225 100%); box-shadow: 0 22px 50px rgba(2,6,23,.28); }
        .hero-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; position: relative; z-index: 1; }
        .title { margin: 0; font-size: 28px; line-height: 1.05; font-weight: 950; letter-spacing: -.055em; }
        .status-pill { flex: 0 0 auto; display: inline-flex; align-items: center; gap: 7px; border-radius: 999px; padding: 8px 11px; background: rgba(34,197,94,.16); color: #bbf7d0; border: 1px solid rgba(187,247,208,.22); font-size: 12px; font-weight: 950; text-transform: uppercase; }
        .status-pill.offline { background: rgba(148,163,184,.18); color: #e2e8f0; border-color: rgba(226,232,240,.2); }
        .status-dot { width: 8px; height: 8px; border-radius: 99px; background: currentColor; }
        .hero-meta { display: grid; grid-template-columns: 1fr; gap: 9px; margin-top: 16px; position: relative; z-index: 1; }
        .meta-card { min-height: 42px; display: flex; align-items: center; gap: 8px; border-radius: 16px; padding: 11px; background: rgba(255,255,255,.10); border: 1px solid rgba(255,255,255,.12); backdrop-filter: blur(12px); }
        .meta-label { margin: 0; color: #bfdbfe; font-size: 11px; font-weight: 850; }
        .meta-label::after { content: ':'; }
        .meta-value { margin: 0; color: #fff; font-size: 13px; font-weight: 900; }

        .glass-card { margin-bottom: 13px; border-radius: 24px; border: 1px solid rgba(255,255,255,.88); background: rgba(255,255,255,.78); box-shadow: 0 18px 40px rgba(15,23,42,.09), inset 0 1px 0 rgba(255,255,255,.88); backdrop-filter: blur(16px); padding: 16px; }
        .card-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 13px; }
        .card-title { margin: 0; font-size: 17px; font-weight: 950; letter-spacing: -.035em; }
        .card-subtitle { margin: 4px 0 0; color: var(--muted); font-size: 13px; line-height: 1.36; }
        .tiny-badge { flex: 0 0 auto; border-radius: 999px; padding: 6px 9px; font-size: 11px; font-weight: 950; text-transform: uppercase; }
        .badge-ok { background: #dcfce7; color: #166534; }
        .badge-warn { background: #fee2e2; color: #991b1b; }
        .badge-info { background: #dbeafe; color: #1d4ed8; }
        .badge-muted { background: #e5e7eb; color: #475569; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-failed { background: #fee2e2; color: #991b1b; }

        .water-card { display: grid; grid-template-columns: 118px 1fr; gap: 14px; align-items: center; }
        .water-gauge { position: relative; width: 118px; height: 118px; display: grid; place-items: center; }
        .water-gauge::before { content: ""; position: absolute; inset: 0; border-radius: 50%; background: conic-gradient(var(--blue) var(--water-level, 0%), #dbeafe 0); box-shadow: inset 0 0 0 10px rgba(255,255,255,.86), 0 14px 30px rgba(37,99,235,.16); }
        .water-gauge::after { content: ""; position: absolute; inset: 18px; border-radius: 50%; background: rgba(255,255,255,.94); }
        .water-number { position: relative; z-index: 1; font-size: 28px; font-weight: 950; letter-spacing: -.06em; }
        .water-number small { font-size: 15px; }
        .safety-warning { margin-top: 11px; border-radius: 16px; border: 1px solid #fecaca; background: #fff1f2; color: #991b1b; padding: 12px; font-size: 13px; line-height: 1.4; font-weight: 750; }
        .offline-note { margin-bottom: 13px; border-radius: 18px; border: 1px solid #fde68a; background: #fffbeb; color: #92400e; padding: 13px; font-size: 13px; line-height: 1.42; font-weight: 750; }

        .controls-grid { display: grid; gap: 13px; }
        .control-card { position: relative; overflow: hidden; }
        .control-card::before { content: ""; position: absolute; inset: 0 0 auto 0; height: 4px; background: linear-gradient(90deg, #38bdf8, #2563eb); }
        .control-card.pump::before { background: linear-gradient(90deg, #38bdf8, #0ea5e9); }
        .control-card.cob::before { background: linear-gradient(90deg, #fbbf24, #f97316); }
        .control-card.rgb::before { background: linear-gradient(90deg, #ec4899, #8b5cf6, #06b6d4); }
        .output-icon { width: 42px; height: 42px; border-radius: 16px; display: grid; place-items: center; font-size: 20px; background: #eff6ff; }
        .state-box { margin-bottom: 13px; border-radius: 18px; background: #f8fafc; border: 1px solid rgba(148,163,184,.20); padding: 12px; display: grid; gap: 8px; }
        .state-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; font-size: 13px; }
        .state-row span:first-child { color: var(--muted); font-weight: 800; }
        .state-row span:last-child { color: var(--ink); font-weight: 950; }
        .dirty-note, .safety-note { margin-bottom: 13px; border-radius: 16px; padding: 12px; font-size: 13px; font-weight: 800; line-height: 1.35; }
        .dirty-note { border: 1px solid #fde68a; background: #fffbeb; color: #92400e; }
        .dirty-note button { border: 0; background: transparent; color: #78350f; font-weight: 950; text-decoration: underline; cursor: pointer; }
        .safety-note { border: 1px solid #fecaca; background: #fff1f2; color: #991b1b; }

        .field { margin-bottom: 12px; }
        .switch-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 13px; }
        .field-label, .switch-label { color: #334155; font-size: 13px; font-weight: 900; }
        .switch { position: relative; width: 54px; height: 31px; flex: 0 0 auto; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; inset: 0; background: #cbd5e1; border-radius: 999px; transition: .2s; }
        .slider::before { content: ""; position: absolute; width: 25px; height: 25px; left: 3px; top: 3px; border-radius: 50%; background: #fff; box-shadow: 0 3px 10px rgba(15,23,42,.22); transition: .2s; }
        .switch input:checked + .slider { background: var(--blue); }
        .switch input:checked + .slider::before { transform: translateX(23px); }
        .switch input:disabled + .slider { background: #e5e7eb; cursor: not-allowed; }
        .input, .select, .color-input { width: 100%; border-radius: 15px; border: 1px solid rgba(148,163,184,.38); background: rgba(255,255,255,.94); color: var(--ink); font-size: 15px; font-weight: 800; outline: none; }
        .input, .select { min-height: 46px; padding: 0 13px; }
        .color-input { height: 46px; padding: 5px; }
        .input:focus, .select:focus, .color-input:focus { border-color: var(--blue); box-shadow: 0 0 0 4px rgba(22,135,249,.13); }
        .input:disabled { background: #f1f5f9; color: #94a3b8; }
        .send-button { width: 100%; min-height: 48px; border: 0; border-radius: 999px; cursor: pointer; background: linear-gradient(135deg, #2aa8ff, #0877ef); color: #fff; font-size: 13px; font-weight: 950; text-transform: uppercase; box-shadow: 0 16px 30px rgba(37,99,235,.28); }
        .send-button:disabled { cursor: not-allowed; background: #94a3b8; box-shadow: none; }
        .quick-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 13px; }
        .quick-action { border-radius: 18px; border: 1px solid rgba(148,163,184,.24); background: rgba(255,255,255,.76); padding: 13px; color: var(--ink); text-decoration: none; box-shadow: 0 12px 26px rgba(15,23,42,.06); }
        .quick-action strong { display: block; margin-bottom: 4px; font-size: 14px; font-weight: 950; }
        .quick-action span { color: var(--muted); font-size: 12px; font-weight: 700; }
        .last-sync { margin: 14px 0 0; text-align: center; color: var(--muted); font-size: 12px; font-weight: 700; }

        @media (min-width: 900px) { .phone-frame { max-width: 520px; } }
        @media (max-width: 430px) {
            .page-shell { padding-left: 10px; padding-right: 10px; }
            .phone-frame { padding: 10px; border-radius: 30px; }
            .app-screen { border-radius: 24px; }
            .app-content { padding: 16px 13px; }
            .title { font-size: 25px; }
            .water-card { grid-template-columns: 1fr; }
            .water-gauge { margin: 0 auto; }
        }
    </style>
</head>

<body>
    @php
        $pump = $outputs->get('pump');
        $cobLight = $outputs->get('cob_light');
        $rgbLight = $outputs->get('rgb_light');

        $waterLow = $latestReadings->get('water_low')?->value;
        $isWaterLow = (int) $waterLow === 1;
        $waterLevel = $latestReadings->get('water_level_percent')?->value;
        $waterLevelNumber = is_null($waterLevel) ? null : max(0, min(100, (int) $waterLevel));

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
                'pending' => 'Waiting',
                'acknowledged' => 'Applying',
                'executed' => 'Applied',
                'failed' => 'Failed',
                'expired' => 'Expired',
                default => ucfirst($command->status),
            };
        };

        $commandClass = function ($command) {
            if (! $command) {
                return 'badge-muted';
            }

            return match ($command->status) {
                'pending' => 'badge-pending',
                'acknowledged' => 'badge-info',
                'executed' => 'badge-ok',
                'failed', 'expired' => 'badge-failed',
                default => 'badge-muted',
            };
        };

        $pumpCommand = $latestCommandFor('pump');
        $cobLightCommand = $latestCommandFor('cob_light');
        $rgbLightCommand = $latestCommandFor('rgb_light');
    @endphp

    <div class="page-shell" data-smart-fountain-dashboard data-status-url="{{ route('devices.smart-fountain.status', $device) }}" data-water-safety-locked="{{ $isWaterLow ? '1' : '0' }}">
        <a href="{{ route('devices.index') }}" class="back-link">← Back to Devices</a>

        <nav class="tabs">
            <a href="{{ route('devices.show', $device) }}" class="tab active">Home</a>
            <a href="{{ route('devices.smart-fountain.scenes.index', $device) }}" class="tab">Scenes</a>
            <a href="{{ route('devices.smart-fountain.schedules.index', $device) }}" class="tab">Schedule</a>
            <a href="{{ route('devices.history', $device) }}" class="tab">History</a>
        </nav>

        @if (session('success'))
            <div id="flash-success" class="notice success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="notice error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <main class="phone-frame">
            <div class="app-screen">
                <div class="app-content">
                    <section class="hero">
                        <div class="hero-top">
                            <div>
                                <h1 id="device-name" class="title">{{ $device->name }}</h1>
                            </div>
                            <div id="online-badge" class="status-pill {{ $isOnline ? '' : 'offline' }}">
                                <span>{{ $isOnline ? 'Live' : 'Offline' }}</span>
                                <span class="status-dot"></span>
                            </div>
                        </div>

                        <div class="hero-meta">
                            <div class="meta-card">
                                <p class="meta-label">Location</p>
                                <p id="device-location" class="meta-value">{{ $device->location_label ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </section>

                    <section class="glass-card water-card">
                        <div id="water-gauge" class="water-gauge" style="--water-level: {{ $waterLevelNumber ?? 0 }}%;">
                            <div class="water-number">
                                <span id="water-level-number">{{ $waterLevelNumber ?? 'N/A' }}</span><small id="water-level-percent-sign" class="{{ is_null($waterLevelNumber) ? 'hidden' : '' }}">%</small>
                            </div>
                        </div>

                        <div>
                            <div class="card-head" style="margin-bottom: 8px;">
                                <div>
                                    <h2 class="card-title">Water Safety</h2>
                                    <p class="card-subtitle">Pump protection status</p>
                                </div>
                                <span id="water-low" class="tiny-badge {{ is_null($waterLow) ? 'badge-muted' : ($isWaterLow ? 'badge-warn' : 'badge-ok') }}">
                                    @if (is_null($waterLow))
                                        N/A
                                    @elseif ($isWaterLow)
                                        Low
                                    @else
                                        Safe
                                    @endif
                                </span>
                            </div>
                            <p class="card-subtitle">Level: <span id="water-level">{{ is_null($waterLevel) ? 'N/A' : number_format($waterLevel, 0) . '%' }}</span></p>
                            <div id="water-low-warning" class="{{ $isWaterLow ? '' : 'hidden' }} safety-warning">
                                Low water detected. Pump is locked OFF for safety. Lights can still be used.
                            </div>
                        </div>
                    </section>

                    <div id="offline-note" class="{{ $isOnline ? 'hidden' : '' }} offline-note">
                        Device is offline. Commands are still enabled for backend testing, but customer release should block live controls while offline.
                    </div>

                    <span id="device-status" class="hidden">{{ $isOnline ? 'Online' : 'Offline' }}</span>
                    <span id="device-type" class="hidden">{{ $device->displayType() }}</span>
                    <span id="device-timezone" class="hidden">{{ $device->timezone ?? 'Asia/Dhaka' }}</span>

                    @if ($device->status !== 'active')
                        <div class="offline-note">This device is not active in the account yet. Output commands are disabled.</div>
                    @else
                        <section class="controls-grid">
                            <form id="pump-control-form" data-output-form="pump" method="POST" action="{{ route('devices.outputs.set', [$device, 'pump']) }}" class="glass-card control-card pump">
                                @csrf
                                <div class="card-head">
                                    <div style="display:flex;gap:12px;align-items:center;">
                                        <div class="output-icon">💧</div>
                                        <div>
                                            <h2 class="card-title">Pump</h2>
                                            <p class="card-subtitle">Water flow control</p>
                                        </div>
                                    </div>
                                    <span id="pump-command" class="tiny-badge {{ $isWaterLow ? 'badge-warn' : $commandClass($pumpCommand) }}">{{ $isWaterLow ? 'Locked' : $commandLabel($pumpCommand) }}</span>
                                </div>
                                <div id="pump-safety-note" class="{{ $isWaterLow ? '' : 'hidden' }} safety-note">Pump controls are disabled while water is low.</div>
                                <div id="pump-dirty-note" class="dirty-note hidden">Unsaved pump changes. <button type="button" data-reset-output="pump">Reset to current</button></div>
                                <div class="state-box">
                                    <div class="state-row"><span>Current</span><span id="pump-state">{{ data_get($pump?->state, 'enabled') ? 'ON' : 'OFF' }}</span></div>
                                    <div class="state-row"><span>Speed</span><span id="pump-speed">{{ data_get($pump?->state, 'speed_percent', 0) }}%</span></div>
                                    <div class="state-row"><span>Source</span><span id="pump-source">{{ $pump?->last_changed_source ?? 'N/A' }}</span></div>
                                </div>
                                <div class="switch-row">
                                    <span class="switch-label">Enable pump</span>
                                    <label class="switch">
                                        <input id="pump-enabled-input" type="checkbox" name="enabled" value="1" {{ data_get($pump?->state, 'enabled') ? 'checked' : '' }} {{ $isWaterLow ? 'disabled' : '' }}>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="field">
                                    <label for="pump_speed_percent" class="field-label">Speed (%)</label>
                                    <input id="pump_speed_percent" type="number" name="speed_percent" min="0" max="100" value="{{ old('speed_percent', data_get($pump?->state, 'speed_percent', 0)) }}" class="input" {{ $isWaterLow ? 'disabled' : '' }} required>
                                </div>
                                <button id="pump-submit-button" type="submit" class="send-button" {{ $isWaterLow ? 'disabled' : '' }}>{{ $isWaterLow ? 'Pump Locked by Water Safety' : 'Send Pump Command' }}</button>
                            </form>

                            <form data-output-form="cob_light" method="POST" action="{{ route('devices.outputs.set', [$device, 'cob_light']) }}" class="glass-card control-card cob">
                                @csrf
                                <div class="card-head">
                                    <div style="display:flex;gap:12px;align-items:center;">
                                        <div class="output-icon">☀️</div>
                                        <div>
                                            <h2 class="card-title">COB Light</h2>
                                            <p class="card-subtitle">Main white light</p>
                                        </div>
                                    </div>
                                    <span id="cob-light-command" class="tiny-badge {{ $commandClass($cobLightCommand) }}">{{ $commandLabel($cobLightCommand) }}</span>
                                </div>
                                <div id="cob-light-dirty-note" class="dirty-note hidden">Unsaved COB light changes. <button type="button" data-reset-output="cob_light">Reset to current</button></div>
                                <div class="state-box">
                                    <div class="state-row"><span>Current</span><span id="cob-light-state">{{ data_get($cobLight?->state, 'enabled') ? 'ON' : 'OFF' }}</span></div>
                                    <div class="state-row"><span>Brightness</span><span id="cob-light-brightness">{{ data_get($cobLight?->state, 'brightness_percent', 0) }}%</span></div>
                                    <div class="state-row"><span>Source</span><span id="cob-light-source">{{ $cobLight?->last_changed_source ?? 'N/A' }}</span></div>
                                </div>
                                <div class="switch-row">
                                    <span class="switch-label">Enable COB</span>
                                    <label class="switch">
                                        <input id="cob-light-enabled-input" type="checkbox" name="enabled" value="1" {{ data_get($cobLight?->state, 'enabled') ? 'checked' : '' }}>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="field">
                                    <label for="cob_brightness_percent" class="field-label">Brightness (%)</label>
                                    <input id="cob_brightness_percent" type="number" name="brightness_percent" min="0" max="100" value="{{ old('brightness_percent', data_get($cobLight?->state, 'brightness_percent', 0)) }}" class="input" required>
                                </div>
                                <button type="submit" class="send-button">Send COB Command</button>
                            </form>

                            <form data-output-form="rgb_light" method="POST" action="{{ route('devices.outputs.set', [$device, 'rgb_light']) }}" class="glass-card control-card rgb">
                                @csrf
                                <div class="card-head">
                                    <div style="display:flex;gap:12px;align-items:center;">
                                        <div class="output-icon">🌈</div>
                                        <div>
                                            <h2 class="card-title">RGB Light</h2>
                                            <p class="card-subtitle">Decorative ambience</p>
                                        </div>
                                    </div>
                                    <span id="rgb-light-command" class="tiny-badge {{ $commandClass($rgbLightCommand) }}">{{ $commandLabel($rgbLightCommand) }}</span>
                                </div>
                                <div id="rgb-light-dirty-note" class="dirty-note hidden">Unsaved RGB light changes. <button type="button" data-reset-output="rgb_light">Reset to current</button></div>
                                <div class="state-box">
                                    <div class="state-row"><span>Current</span><span id="rgb-light-state">{{ data_get($rgbLight?->state, 'enabled') ? 'ON' : 'OFF' }}</span></div>
                                    <div class="state-row"><span>Brightness</span><span id="rgb-light-brightness">{{ data_get($rgbLight?->state, 'brightness_percent', 0) }}%</span></div>
                                    <div class="state-row"><span>Color</span><span id="rgb-light-color">{{ data_get($rgbLight?->state, 'color', 'N/A') }}</span></div>
                                    <div class="state-row"><span>Effect</span><span id="rgb-light-effect">{{ str_replace('_', ' ', data_get($rgbLight?->state, 'effect', 'N/A')) }}</span></div>
                                    <div class="state-row"><span>Source</span><span id="rgb-light-source">{{ $rgbLight?->last_changed_source ?? 'N/A' }}</span></div>
                                </div>
                                <div class="switch-row">
                                    <span class="switch-label">Enable RGB</span>
                                    <label class="switch">
                                        <input id="rgb-light-enabled-input" type="checkbox" name="enabled" value="1" {{ data_get($rgbLight?->state, 'enabled') ? 'checked' : '' }}>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="field">
                                    <label for="rgb_brightness_percent" class="field-label">Brightness (%)</label>
                                    <input id="rgb_brightness_percent" type="number" name="brightness_percent" min="0" max="100" value="{{ old('brightness_percent', data_get($rgbLight?->state, 'brightness_percent', 0)) }}" class="input" required>
                                </div>
                                <div class="field">
                                    <label for="rgb_color" class="field-label">Color</label>
                                    <input id="rgb_color" type="color" name="color" value="{{ old('color', data_get($rgbLight?->state, 'color', '#FFB066')) }}" class="color-input" required>
                                </div>
                                <div class="field">
                                    <label for="rgb_effect" class="field-label">Effect</label>
                                    <select id="rgb_effect" name="effect" class="select" required>
                                        @foreach (['solid', 'breathing', 'slow_rainbow', 'warm_glow', 'water_shimmer', 'night_mode'] as $effect)
                                            <option value="{{ $effect }}" @selected(old('effect', data_get($rgbLight?->state, 'effect', 'warm_glow')) === $effect)>{{ ucwords(str_replace('_', ' ', $effect)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="send-button">Send RGB Command</button>
                            </form>
                        </section>
                    @endif

                    <section class="quick-actions">
                        <a href="{{ route('devices.smart-fountain.scenes.index', $device) }}" class="quick-action"><strong>Scenes</strong><span>Apply presets</span></a>
                        <a href="{{ route('devices.smart-fountain.schedules.index', $device) }}" class="quick-action"><strong>Timeline</strong><span>Day / Evening / Night</span></a>
                    </section>

                    <p class="last-sync">Last synced: <span id="device-last-seen">{{ $device->last_seen_at?->diffForHumans() ?? 'Never' }}</span></p>
                </div>
            </div>
        </main>
    </div>
</body>

</html>
