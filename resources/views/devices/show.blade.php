<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $device->name }} - Plant Bed</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --page-bg: #eef3f8;
            --card-bg: rgba(255, 255, 255, 0.78);
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-soft: rgba(148, 163, 184, 0.28);
            --blue: #1687f9;
            --blue-dark: #0f63d8;
            --green: #16a34a;
            --red: #dc2626;
            --warning: #d97706;
            --dark: #111827;
            --dark-2: #020617;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            color: var(--text-main);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at top left, rgba(59, 130, 246, 0.14), transparent 34rem),
                linear-gradient(135deg, #f8fbff 0%, var(--page-bg) 52%, #e6edf6 100%);
        }

        .hidden {
            display: none !important;
        }

        .page-shell {
            width: min(100%, 980px);
            margin: 0 auto;
            padding: 18px 14px 34px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 14px;
            color: #2563eb;
            font-weight: 650;
            font-size: 14px;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 18px;
        }

        .tab {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 0 16px;
            border-radius: 999px;
            border: 1px solid var(--border-soft);
            background: rgba(255, 255, 255, 0.86);
            color: var(--text-main);
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.08);
        }

        .tab.active {
            background: var(--blue);
            color: #ffffff;
            border-color: var(--blue);
        }

        .phone-frame {
            max-width: 430px;
            margin: 0 auto;
            padding: 18px;
            border-radius: 42px;
            background: rgba(255, 255, 255, 0.82);
            box-shadow:
                0 30px 70px rgba(15, 23, 42, 0.14),
                inset 0 0 0 1px rgba(255, 255, 255, 0.9);
        }

        .app-screen {
            overflow: hidden;
            border-radius: 30px;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(238, 243, 248, 0.94));
            border: 1px solid rgba(226, 232, 240, 0.9);
            min-height: 720px;
        }

        .app-content {
            padding: 22px 18px 18px;
        }

        .hero {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
        }

        .eyebrow {
            margin: 0 0 4px;
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 600;
        }

        .title {
            margin: 0;
            color: var(--text-main);
            font-size: 25px;
            line-height: 1.05;
            font-weight: 850;
            letter-spacing: -0.04em;
        }

        .subtitle {
            margin: 6px 0 0;
            color: var(--text-muted);
            font-size: 15px;
            line-height: 1.35;
        }

        .status-box {
            flex: 0 0 auto;
            text-align: right;
        }

        .status-box-label {
            margin: 0 0 5px;
            font-size: 13px;
            font-weight: 700;
            color: var(--text-main);
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 10px;
            border-radius: 999px;
            background: #dcfce7;
            color: #15803d;
            font-size: 12px;
            font-weight: 850;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        .status-pill.offline {
            background: #e5e7eb;
            color: #475569;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: currentColor;
        }

        .glass-card {
            border: 1px solid rgba(255, 255, 255, 0.86);
            background: var(--card-bg);
            border-radius: 22px;
            box-shadow:
                0 18px 38px rgba(15, 23, 42, 0.09),
                inset 0 1px 0 rgba(255, 255, 255, 0.86);
            backdrop-filter: blur(16px);
        }

        .moisture-card {
            padding: 18px 16px 16px;
            margin-bottom: 14px;
            text-align: center;
        }

        .card-heading {
            margin: 0;
            font-size: 16px;
            font-weight: 850;
        }

        .gauge-wrap {
            position: relative;
            width: 190px;
            height: 190px;
            margin: 8px auto 4px;
        }

        .gauge-svg {
            width: 190px;
            height: 190px;
            transform: rotate(-90deg);
            filter: drop-shadow(0 10px 12px rgba(37, 99, 235, 0.18));
        }

        .gauge-bg {
            fill: none;
            stroke: #d9e1ec;
            stroke-width: 14;
            stroke-linecap: round;
        }

        .gauge-progress {
            fill: none;
            stroke: var(--blue);
            stroke-width: 14;
            stroke-linecap: round;
            stroke-dasharray: 439.82;
            stroke-dashoffset: 439.82;
            transition: stroke-dashoffset 260ms ease;
        }

        .gauge-center {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .gauge-value {
            font-size: 44px;
            line-height: 1;
            font-weight: 900;
            letter-spacing: -0.05em;
        }

        .gauge-value .percent {
            font-size: 26px;
            letter-spacing: -0.03em;
        }

        .gauge-label {
            margin-top: 4px;
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 700;
        }

        .soil-state {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: #0f766e;
            font-size: 14px;
            font-weight: 700;
        }

        .soil-state::before {
            content: "";
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: #22c4d6;
            box-shadow: 0 0 0 4px rgba(34, 196, 214, 0.14);
        }

        .soil-state.dry {
            color: #b45309;
        }

        .soil-state.dry::before {
            background: #f59e0b;
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.14);
        }

        .soil-state.wet {
            color: #1d4ed8;
        }

        .soil-state.wet::before {
            background: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.14);
        }

        .metric-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 13px;
            margin-bottom: 16px;
        }

        .metric-card {
            padding: 14px 15px;
            min-height: 92px;
        }

        .metric-label {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            color: var(--text-main);
            font-size: 13px;
            font-weight: 700;
        }

        .metric-icon {
            width: 26px;
            height: 26px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #e8eef7;
            font-size: 13px;
        }

        .metric-value {
            font-size: 28px;
            font-weight: 900;
            letter-spacing: -0.05em;
        }

        .section-title {
            margin: 0 0 8px;
            font-size: 20px;
            font-weight: 900;
            letter-spacing: -0.04em;
        }

        .control-card {
            padding: 18px;
            color: #ffffff;
            background: linear-gradient(145deg, #152136 0%, #070d1d 100%);
            border-radius: 18px;
            box-shadow: 0 22px 44px rgba(2, 6, 23, 0.28);
            margin-bottom: 12px;
        }

        .control-head {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .control-title {
            margin: 0;
            font-size: 20px;
            font-weight: 800;
        }

        .control-subtitle {
            margin: 5px 0 0;
            color: #cbd5e1;
            font-size: 13px;
        }

        .manual-pill {
            align-self: flex-start;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            color: #e2e8f0;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 800;
        }

        .form-row {
            margin-bottom: 12px;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            color: #dbeafe;
            font-size: 13px;
            font-weight: 700;
        }

        .duration-input {
            width: 100%;
            height: 44px;
            border: 0;
            border-radius: 14px;
            padding: 0 14px;
            background: rgba(255, 255, 255, 0.96);
            color: var(--text-main);
            font-size: 16px;
            font-weight: 750;
            outline: none;
        }

        .hint-text {
            margin: 7px 0 0;
            color: #94a3b8;
            font-size: 12px;
        }

        .action-button {
            width: 100%;
            border: 0;
            border-radius: 999px;
            min-height: 48px;
            cursor: pointer;
            background: linear-gradient(135deg, #2aa8ff, #0877ef);
            color: #ffffff;
            font-size: 14px;
            font-weight: 900;
            letter-spacing: 0.01em;
            text-transform: uppercase;
            box-shadow: 0 16px 30px rgba(37, 99, 235, 0.35);
        }

        .action-button.danger {
            background: linear-gradient(135deg, #f87171, #dc2626);
            box-shadow: 0 16px 30px rgba(220, 38, 38, 0.25);
        }

        .offline-note {
            margin-bottom: 12px;
            border: 1px solid rgba(251, 191, 36, 0.45);
            border-radius: 14px;
            background: rgba(251, 191, 36, 0.12);
            color: #fde68a;
            padding: 12px;
            font-size: 13px;
            line-height: 1.4;
        }

        .mode-chips {
            display: flex;
            gap: 8px;
            justify-content: center;
            color: #cbd5e1;
        }

        .mode-chip {
            min-width: 92px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
            padding: 7px 10px;
            text-align: center;
            font-size: 13px;
        }

        .mode-chip.active {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.18);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.18);
        }

        .device-strip {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin: 13px 0 0;
        }

        .strip-card {
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.72);
            padding: 12px;
            border: 1px solid var(--border-soft);
        }

        .strip-label {
            margin: 0 0 4px;
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 750;
        }

        .strip-value {
            margin: 0;
            color: var(--text-main);
            font-size: 14px;
            font-weight: 850;
        }

        .quick-links {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
            margin-top: 15px;
        }

        .quick-link {
            display: block;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.72);
            border: 1px solid var(--border-soft);
            color: var(--text-main);
            padding: 14px;
            text-decoration: none;
        }

        .quick-link strong {
            display: block;
            margin-bottom: 4px;
            font-size: 15px;
        }

        .quick-link span {
            color: var(--text-muted);
            font-size: 13px;
        }

        .last-sync {
            margin: 13px 0 0;
            text-align: center;
            color: var(--text-muted);
            font-size: 12px;
        }

        .notice {
            margin-bottom: 14px;
            border-radius: 16px;
            padding: 12px 14px;
            font-size: 14px;
            font-weight: 650;
        }

        .notice.success {
            background: #dcfce7;
            color: #166534;
        }

        .notice.error {
            background: #fee2e2;
            color: #991b1b;
        }

        @media (min-width: 800px) {
            .phone-frame {
                max-width: 460px;
            }
        }

        @media (max-width: 420px) {
            .page-shell {
                padding-left: 10px;
                padding-right: 10px;
            }

            .phone-frame {
                padding: 10px;
                border-radius: 30px;
            }

            .app-screen {
                border-radius: 24px;
            }

            .app-content {
                padding: 18px 14px 16px;
            }

            .title {
                font-size: 22px;
            }

            .gauge-wrap,
            .gauge-svg {
                width: 172px;
                height: 172px;
            }

            .gauge-value {
                font-size: 38px;
            }
        }
    </style>
</head>

<body>
    @php
        $soilValue = is_null($latestReading?->soil_moisture) ? null : max(0, min(100, (int) $latestReading->soil_moisture));
        $gaugePercent = $soilValue ?? 0;
        $soilStatus = is_null($soilValue)
            ? 'No reading yet'
            : ($soilValue < 35 ? 'Dry' : ($soilValue > 85 ? 'Wet' : 'Optimal'));
        $soilStatusKey = $soilStatus === 'Dry' ? 'dry' : ($soilStatus === 'Wet' ? 'wet' : 'optimal');
        $manualStateLabel = ucfirst($manualWateringState);
    @endphp

    <div class="page-shell">
        <a href="{{ route('devices.index') }}" class="back-link">← Back to Devices</a>

        <nav class="tabs">
            <a href="{{ route('devices.show', $device) }}" class="tab active">Home</a>
            <a href="{{ route('devices.automation', $device) }}" class="tab">Automation</a>
            <a href="{{ route('devices.schedules.index', $device) }}" class="tab">Schedules</a>
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
                    <header class="hero">
                        <div>
                            <h1 id="device-name" class="title">{{ $device->name }}</h1>
                            <p class="subtitle">
                                <span id="device-location">{{ $device->location_label ?? 'N/A' }}</span>
                            </p>
                        </div>

                        <div class="status-box">
                            <p class="status-box-label">Status</p>
                            <div id="online-badge" class="status-pill {{ $isOnline ? '' : 'offline' }}">
                                <span>{{ $isOnline ? 'Live' : 'Offline' }}</span>
                                <span class="status-dot"></span>
                            </div>
                        </div>
                    </header>

                    <section class="glass-card moisture-card">
                        <h2 class="card-heading">Soil Moisture</h2>

                        <div class="gauge-wrap">
                            <svg class="gauge-svg" viewBox="0 0 160 160" aria-hidden="true">
                                <circle class="gauge-bg" cx="80" cy="80" r="70"></circle>
                                <circle id="soil-gauge-progress" class="gauge-progress" cx="80" cy="80" r="70" data-radius="70"></circle>
                            </svg>

                            <div class="gauge-center">
                                <div class="gauge-value"><span id="reading-soil">{{ $soilValue ?? 'N/A' }}</span><span class="percent">%</span></div>
                                <div class="gauge-label">Soil level</div>
                            </div>
                        </div>

                        <div id="soil-status-badge" class="soil-state {{ $soilStatusKey }}">{{ $soilStatus }}</div>
                    </section>

                    <section class="metric-grid">
                        <div class="glass-card metric-card">
                            <div class="metric-label"><span class="metric-icon">°C</span> Air Temp</div>
                            <div class="metric-value"><span id="reading-temperature">{{ $latestReading?->temperature ?? 'N/A' }}</span>°C</div>
                        </div>

                        <div class="glass-card metric-card">
                            <div class="metric-label"><span class="metric-icon">%</span> Humidity</div>
                            <div class="metric-value"><span id="reading-humidity">{{ $latestReading?->humidity ?? 'N/A' }}</span>%</div>
                        </div>
                    </section>

                    <section>
                        <h2 class="section-title">Control Center</h2>

                        <div class="control-card">
                            <div class="control-head">
                                <div>
                                    <h3 class="control-title">Pump Control</h3>
                                    <p class="control-subtitle">State: <span id="manual-state-text">{{ $manualStateLabel }}</span></p>
                                </div>

                                <div id="manual-state-badge" class="manual-pill">{{ $manualStateLabel }}</div>
                            </div>

                            <div id="started-by-row" class="{{ in_array($manualWateringState, ['waiting', 'watering', 'stopping'], true) ? '' : 'hidden' }} control-subtitle">
                                Started By: <span id="started-by-text">N/A</span>
                            </div>

                            <div id="manual-offline-note" class="{{ $isOnline ? 'hidden' : '' }} offline-note">
                                Device is offline. Manual watering is unavailable right now.
                            </div>

                            <div id="start-form-wrapper" class="{{ $manualWateringState === 'idle' && $isOnline ? '' : 'hidden' }}">
                                <form action="{{ route('devices.water-now', $device) }}" method="POST">
                                    @csrf
                                    <div class="form-row">
                                        <label for="duration_seconds" class="form-label">Duration (seconds)</label>
                                        <input
                                            type="number"
                                            name="duration_seconds"
                                            id="duration_seconds"
                                            min="1"
                                            max="{{ $manualMaxDuration }}"
                                            value="{{ old('duration_seconds', min(30, $manualMaxDuration)) }}"
                                            class="duration-input"
                                            required>
                                        <p class="hint-text">Maximum allowed: <span id="manual-max-duration">{{ $manualMaxDuration }}</span> seconds</p>
                                    </div>

                                    <button type="submit" class="action-button">Start Manual Watering</button>
                                </form>
                            </div>

                            <div id="stop-form-wrapper" class="{{ in_array($manualWateringState, ['waiting', 'watering'], true) && $isOnline ? '' : 'hidden' }}">
                                <form action="{{ route('devices.water-stop', $device) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="action-button danger">Stop Watering</button>
                                </form>
                            </div>

                            <div class="mode-chips" style="margin-top: 14px;">
                                <div class="mode-chip active">Manual</div>
                                <div class="mode-chip">Scheduled</div>
                            </div>
                        </div>
                    </section>

                    <section class="device-strip">
                        <div class="strip-card">
                            <p class="strip-label">Mode</p>
                            <p id="device-mode" class="strip-value">{{ ucfirst($device->wateringRule?->watering_mode ?? 'schedule') }}</p>
                        </div>

                        <div class="strip-card">
                            <p class="strip-label">Schedules</p>
                            <p id="enabled-schedules" class="strip-value">{{ $enabledScheduleCount }} enabled</p>
                        </div>

                        <div class="strip-card">
                            <p class="strip-label">Connection</p>
                            <p id="device-status" class="strip-value">{{ $isOnline ? 'Online' : 'Offline' }}</p>
                        </div>

                        <div class="strip-card">
                            <p class="strip-label">Timezone</p>
                            <p id="device-timezone" class="strip-value">{{ $device->timezone ?? 'Asia/Dhaka' }}</p>
                        </div>
                    </section>

                    <section class="quick-links">
                        <a href="{{ route('devices.automation', $device) }}" class="quick-link">
                            <strong>Automation</strong>
                            <span>Mode, threshold, cooldown, and durations.</span>
                        </a>

                        <a href="{{ route('devices.schedules.index', $device) }}" class="quick-link">
                            <strong>Schedules</strong>
                            <span>Manage scheduled watering times.</span>
                        </a>

                        <a href="{{ route('devices.history', $device) }}" class="quick-link">
                            <strong>History</strong>
                            <span>Recent logs and device commands.</span>
                        </a>
                    </section>

                    <p class="last-sync">Last synced: <span id="device-last-seen">{{ $device->last_seen_at?->diffForHumans() ?? 'Never' }}</span></p>
                    <span id="device-type" class="hidden">{{ $device->displayType() }}</span>
                    <span id="reading-recorded" class="hidden">{{ $latestReading?->recorded_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</span>
                </div>
            </div>
        </main>
    </div>

    <script>
        const statusUrl = "{{ route('devices.status', $device) }}";

        function setText(id, value) {
            const el = document.getElementById(id);
            if (!el) return;
            el.textContent = value ?? 'N/A';
        }

        function soilStatus(value) {
            if (value === null || value === undefined || value === 'N/A') return { label: 'No reading yet', key: 'optimal' };
            const number = Number(value);
            if (!Number.isFinite(number)) return { label: 'No reading yet', key: 'optimal' };
            if (number < 35) return { label: 'Dry', key: 'dry' };
            if (number > 85) return { label: 'Wet', key: 'wet' };
            return { label: 'Optimal', key: 'optimal' };
        }

        function updateSoilGauge(value) {
            const progress = document.getElementById('soil-gauge-progress');
            const badge = document.getElementById('soil-status-badge');
            const number = Number(value);
            const percent = Number.isFinite(number) ? Math.max(0, Math.min(100, number)) : 0;

            if (progress) {
                const radius = Number(progress.dataset.radius || 70);
                const circumference = 2 * Math.PI * radius;
                progress.style.strokeDasharray = `${circumference}`;
                progress.style.strokeDashoffset = `${circumference - (percent / 100) * circumference}`;
            }

            if (badge) {
                const status = soilStatus(value);
                badge.textContent = status.label;
                badge.className = `soil-state ${status.key}`;
            }
        }

        function manualLabel(state) {
            if (state === 'waiting') return 'Waiting';
            if (state === 'watering') return 'Watering';
            if (state === 'stopping') return 'Stopping';
            return 'Idle';
        }

        function updateManualState(data) {
            const state = data.manual.state;
            const isOnline = data.device.is_online;
            const label = manualLabel(state);

            const stateText = document.getElementById('manual-state-text');
            const stateBadge = document.getElementById('manual-state-badge');
            const startWrapper = document.getElementById('start-form-wrapper');
            const stopWrapper = document.getElementById('stop-form-wrapper');
            const offlineNote = document.getElementById('manual-offline-note');
            const startedByRow = document.getElementById('started-by-row');
            const startedByText = document.getElementById('started-by-text');

            if (stateText) stateText.textContent = label;
            if (stateBadge) stateBadge.textContent = label;

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

        function updateOnlineBadge(isOnline) {
            const badge = document.getElementById('online-badge');
            if (!badge) return;

            badge.className = isOnline ? 'status-pill' : 'status-pill offline';
            badge.innerHTML = `<span>${isOnline ? 'Live' : 'Offline'}</span><span class="status-dot"></span>`;
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
                setText('enabled-schedules', `${data.device.enabled_schedule_count} enabled`);
                setText('device-last-seen', data.device.last_seen_human);

                const soilValue = data.latest_reading.soil_moisture ?? 'N/A';
                setText('reading-temperature', data.latest_reading.temperature ?? 'N/A');
                setText('reading-humidity', data.latest_reading.humidity ?? 'N/A');
                setText('reading-soil', soilValue);
                setText('reading-recorded', data.latest_reading.recorded_at ?? 'N/A');
                setText('manual-max-duration', data.manual.max_duration);
                updateSoilGauge(soilValue);
                updateOnlineBadge(Boolean(data.device.is_online));
                updateManualState(data);
            } catch (error) {
                console.error('Status refresh failed:', error);
            }
        }

        updateSoilGauge({{ is_null($soilValue) ? 'null' : $soilValue }});
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