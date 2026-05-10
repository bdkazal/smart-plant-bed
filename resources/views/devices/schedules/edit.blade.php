<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Schedule - {{ $device->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --page-bg: #eef3f8;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-soft: rgba(148, 163, 184, 0.30);
            --blue: #1687f9;
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

        .field {
            margin-bottom: 14px;
        }

        .label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 850;
        }

        .input,
        .select {
            width: 100%;
            min-height: 48px;
            border-radius: 16px;
            border: 1px solid rgba(148, 163, 184, .45);
            background: rgba(255, 255, 255, .92);
            padding: 0 14px;
            color: var(--text-main);
            font-size: 15px;
            font-weight: 700;
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

        .toggle-row {
            display: flex;
            align-items: center;
            gap: 10px;
            border-radius: 16px;
            background: #f8fafc;
            padding: 12px;
            border: 1px solid rgba(148, 163, 184, .22);
            font-weight: 750;
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
            text-transform: uppercase;
            box-shadow: 0 16px 30px rgba(37, 99, 235, .35);
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
    @php
    $maxScheduleDuration = $device->wateringRule?->max_watering_duration_seconds ?? 300;
    @endphp

    <div class="page-shell">
        <a href="{{ route('devices.schedules.index', $device) }}" class="back-link">← Back to Schedules</a>

        @if ($errors->any())
        <div class="notice">
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
                        <h1 class="title">Edit Schedule</h1>
                        <!-- <p class="subtitle">Update this timed watering run for {{ $device->name }}. Timezone: {{ $device->timezone ?? 'Asia/Dhaka' }}.</p> -->
                    </section>

                    <form action="{{ route('devices.schedules.update', [$device, $schedule]) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <section class="card">
                            <div class="field">
                                <label for="day_of_week" class="label">Day of Week</label>
                                <select name="day_of_week" id="day_of_week" class="select" required>
                                    <option value="1" @selected(old('day_of_week', $schedule->day_of_week) == 1)>Monday</option>
                                    <option value="2" @selected(old('day_of_week', $schedule->day_of_week) == 2)>Tuesday</option>
                                    <option value="3" @selected(old('day_of_week', $schedule->day_of_week) == 3)>Wednesday</option>
                                    <option value="4" @selected(old('day_of_week', $schedule->day_of_week) == 4)>Thursday</option>
                                    <option value="5" @selected(old('day_of_week', $schedule->day_of_week) == 5)>Friday</option>
                                    <option value="6" @selected(old('day_of_week', $schedule->day_of_week) == 6)>Saturday</option>
                                    <option value="7" @selected(old('day_of_week', $schedule->day_of_week) == 7)>Sunday</option>
                                </select>
                            </div>

                            <div class="field">
                                <label for="time_of_day" class="label">Watering Time</label>
                                <input type="time" name="time_of_day" id="time_of_day" value="{{ old('time_of_day', substr($schedule->time_of_day, 0, 5)) }}" class="input" required>
                                <p class="hint">This schedule time runs in {{ $device->timezone ?? 'Asia/Dhaka' }}.</p>
                            </div>

                            <div class="field">
                                <label for="duration_seconds" class="label">Duration (seconds)</label>
                                <input type="number" name="duration_seconds" id="duration_seconds" min="1" max="{{ $maxScheduleDuration }}" value="{{ old('duration_seconds', min($schedule->duration_seconds, $maxScheduleDuration)) }}" class="input" required>
                                <p class="hint">Maximum allowed: {{ $maxScheduleDuration }} seconds.</p>
                            </div>

                            <label class="toggle-row" for="is_enabled">
                                <input type="checkbox" name="is_enabled" id="is_enabled" value="1" @checked(old('is_enabled', $schedule->is_enabled))>
                                <span>Enable this schedule</span>
                            </label>
                        </section>

                        <button type="submit" class="save-button">Update Schedule</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>

</html>