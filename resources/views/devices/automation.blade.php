<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $device->name }} - Automation</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --page-bg: #eef3f8;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-soft: rgba(148, 163, 184, 0.30);
            --blue: #1687f9;
            --dark: #020617;
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
                radial-gradient(circle at top left, rgba(59, 130, 246, 0.16), transparent 34rem),
                linear-gradient(135deg, #f8fbff 0%, var(--page-bg) 52%, #e6edf6 100%);
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
            background: rgba(255, 255, 255, .86);
            color: var(--text-main);
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 8px 22px rgba(15, 23, 42, .08);
        }

        .tab.active {
            background: var(--blue);
            color: #fff;
            border-color: var(--blue);
        }

        .phone-frame {
            max-width: 460px;
            margin: 0 auto;
            padding: 18px;
            border-radius: 42px;
            background: rgba(255, 255, 255, .82);
            box-shadow: 0 30px 70px rgba(15, 23, 42, .14), inset 0 0 0 1px rgba(255, 255, 255, .9);
        }

        .app-screen {
            overflow: hidden;
            border-radius: 30px;
            background: linear-gradient(180deg, rgba(255, 255, 255, .96), rgba(238, 243, 248, .94));
            border: 1px solid rgba(226, 232, 240, .9);
        }

        .app-content {
            padding: 22px 18px 18px;
        }

        .hero {
            margin-bottom: 16px;
            padding: 18px;
            border-radius: 24px;
            color: #fff;
            background: linear-gradient(145deg, #132033 0%, #061225 100%);
            box-shadow: 0 18px 40px rgba(2, 6, 23, .22);
        }

        .eyebrow {
            margin: 0 0 6px;
            color: #bfdbfe;
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .title {
            margin: 0;
            font-size: 27px;
            line-height: 1.08;
            font-weight: 900;
            letter-spacing: -.05em;
        }

        .subtitle {
            margin: 8px 0 0;
            color: #cbd5e1;
            font-size: 14px;
            line-height: 1.45;
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

        .card {
            margin-bottom: 14px;
            border-radius: 22px;
            border: 1px solid rgba(255, 255, 255, .86);
            background: rgba(255, 255, 255, .78);
            box-shadow: 0 18px 38px rgba(15, 23, 42, .09), inset 0 1px 0 rgba(255, 255, 255, .86);
            backdrop-filter: blur(16px);
            padding: 16px;
        }

        .card-title {
            margin: 0 0 4px;
            font-size: 18px;
            font-weight: 900;
            letter-spacing: -.03em;
        }

        .card-note {
            margin: 0 0 14px;
            color: var(--text-muted);
            font-size: 13px;
            line-height: 1.4;
        }

        .form-grid {
            display: grid;
            gap: 13px;
        }

        .field {
            margin-bottom: 13px;
        }

        .label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 800;
            color: var(--text-main);
        }

        .input,
        .select {
            width: 100%;
            min-height: 46px;
            border-radius: 15px;
            border: 1px solid rgba(148, 163, 184, .45);
            background: rgba(255, 255, 255, .92);
            padding: 0 13px;
            color: var(--text-main);
            font-size: 15px;
            font-weight: 650;
            outline: none;
        }

        .input:focus,
        .select:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 4px rgba(22, 135, 249, .14);
        }

        .hint {
            margin: 7px 0 0;
            color: var(--text-muted);
            font-size: 12px;
            line-height: 1.4;
        }

        .status-help {
            margin-top: 8px;
            border-radius: 14px;
            padding: 10px 12px;
            font-size: 13px;
            font-weight: 650;
            line-height: 1.35;
        }

        .status-help.good {
            background: #dcfce7;
            color: #166534;
        }

        .status-help.warn {
            background: #fef3c7;
            color: #92400e;
        }

        .mode-box {
            display: grid;
            gap: 10px;
            grid-template-columns: 1fr 1fr;
            margin-bottom: 14px;
        }

        .mode-card {
            border-radius: 18px;
            border: 1px solid rgba(148, 163, 184, .35);
            background: #fff;
            padding: 13px;
        }

        .mode-card strong {
            display: block;
            margin-bottom: 4px;
            font-size: 14px;
        }

        .mode-card span {
            color: var(--text-muted);
            font-size: 12px;
            line-height: 1.35;
        }

        .save-button {
            width: 100%;
            min-height: 50px;
            border: 0;
            border-radius: 999px;
            cursor: pointer;
            background: linear-gradient(135deg, #2aa8ff, #0877ef);
            color: #fff;
            font-size: 14px;
            font-weight: 900;
            letter-spacing: .01em;
            text-transform: uppercase;
            box-shadow: 0 16px 30px rgba(37, 99, 235, .35);
        }

        @media (min-width: 640px) {
            .form-grid.two {
                grid-template-columns: 1fr 1fr;
            }

            .form-grid.three {
                grid-template-columns: 1fr 1fr 1fr;
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
        }
    </style>
</head>

<body>
    <div class="page-shell">
        <a href="{{ route('devices.show', $device) }}" class="back-link">← Back to Device Home</a>

        <nav class="tabs">
            <a href="{{ route('devices.show', $device) }}" class="tab">Home</a>
            <a href="{{ route('devices.automation', $device) }}" class="tab active">Automation</a>
            <a href="{{ route('devices.schedules.index', $device) }}" class="tab">Schedules</a>
            <a href="{{ route('devices.history', $device) }}" class="tab">History</a>
        </nav>

        @if (session('success'))
        <div class="notice success">{{ session('success') }}</div>
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
                        <p class="eyebrow">Smart Plant Bed</p>
                        <h1 class="title">Automation Settings</h1>
                        <!-- <p class="subtitle">Tune watering behavior for {{ $device->name }}. Schedules use the device timezone.</p> -->
                    </section>

                    <form action="{{ route('devices.settings.update', $device) }}" method="POST">
                        @csrf

                        <section class="card">
                            <h2 class="card-title">Device Identity</h2>
                            <!-- <p class="card-note">Customer-facing name, location, and timezone.</p> -->

                            <div class="form-grid two">
                                <div class="field">
                                    <label for="name" class="label">Device Name</label>
                                    <input type="text" name="name" id="name" value="{{ old('name', $device->name) }}" class="input" required>
                                </div>

                                <div class="field">
                                    <label for="location_label" class="label">Location</label>
                                    <input type="text" name="location_label" id="location_label" value="{{ old('location_label', $device->location_label) }}" class="input">
                                </div>
                            </div>

                            <div class="field">
                                <label for="timezone" class="label">Device Timezone</label>
                                <select name="timezone" id="timezone" class="select" required>
                                    @foreach ($timezoneOptions as $timezone)
                                    <option value="{{ $timezone }}" @selected(old('timezone', $device->timezone ?? 'Asia/Dhaka') === $timezone)>
                                        {{ $timezone }}
                                    </option>
                                    @endforeach
                                </select>
                                <p class="hint">All schedules use this timezone.</p>
                            </div>
                        </section>

                        <section class="card">
                            <h2 class="card-title">Watering Mode</h2>
                            <!-- <p class="card-note">Choose whether watering runs by sensor threshold or saved schedules.</p> -->

                            <div class="mode-box">
                                <div class="mode-card">
                                    <strong>Auto</strong>
                                    <span>Waters when soil moisture is below threshold.</span>
                                </div>
                                <div class="mode-card">
                                    <strong>Schedule</strong>
                                    <span>Waters at saved day/time rules.</span>
                                </div>
                            </div>

                            <div class="field">
                                <label for="watering_mode" class="label">Automation Mode</label>
                                <select name="watering_mode" id="watering_mode" class="select" required>
                                    <option value="auto" @selected(($device->wateringRule?->watering_mode ?? 'schedule') === 'auto')>Auto</option>
                                    <option value="schedule" @selected(($device->wateringRule?->watering_mode ?? 'schedule') === 'schedule')>Schedule</option>
                                </select>

                                @if ($latestReading && ! is_null($latestReading->soil_moisture))
                                <p class="status-help good">Current soil moisture data is available. Auto mode can operate.</p>
                                @else
                                <p class="status-help warn">Current soil moisture data is unavailable. Auto mode needs a valid moisture reading.</p>
                                @endif
                            </div>

                            <div class="field">
                                <label for="soil_moisture_threshold" class="label">Soil Moisture Threshold (%)</label>
                                <input type="number" name="soil_moisture_threshold" id="soil_moisture_threshold" min="0" max="100" value="{{ old('soil_moisture_threshold', $device->wateringRule?->soil_moisture_threshold ?? 35) }}" class="input" required>
                                <p class="hint">In Auto mode, watering starts when soil moisture goes below this value.</p>
                            </div>
                        </section>

                        <section class="card">
                            <h2 class="card-title">Safety Limits</h2>
                            <p class="card-note">Protect the pump and prevent over-watering.</p>

                            <div class="form-grid three">
                                <div class="field">
                                    <label for="max_watering_duration_seconds" class="label">Max Duration (sec)</label>
                                    <input type="number" name="max_watering_duration_seconds" id="max_watering_duration_seconds" min="1" max="300" value="{{ old('max_watering_duration_seconds', $device->wateringRule?->max_watering_duration_seconds ?? 30) }}" class="input" required>
                                </div>

                                <div class="field">
                                    <label for="cooldown_minutes" class="label">Cooldown (min)</label>
                                    <input type="number" name="cooldown_minutes" id="cooldown_minutes" min="0" max="1440" value="{{ old('cooldown_minutes', $device->wateringRule?->cooldown_minutes ?? 60) }}" class="input" required>
                                </div>

                                <div class="field">
                                    <label for="local_manual_duration_seconds" class="label">Local Manual (sec)</label>
                                    <input type="number" name="local_manual_duration_seconds" id="local_manual_duration_seconds" min="1" max="300" value="{{ old('local_manual_duration_seconds', $device->wateringRule?->local_manual_duration_seconds ?? 30) }}" class="input" required>
                                </div>
                            </div>
                        </section>

                        <button type="submit" class="save-button">Save Automation Settings</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>

</html>