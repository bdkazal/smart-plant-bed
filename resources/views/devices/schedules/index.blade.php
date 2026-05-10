<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $device->name }} - Schedules</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --page-bg: #eef3f8;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-soft: rgba(148, 163, 184, 0.30);
            --blue: #1687f9;
            --green: #16a34a;
            --red: #dc2626;
            --amber: #d97706;
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
            background: #dcfce7;
            color: #166534;
        }

        .add-button {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 50px;
            margin-bottom: 14px;
            border-radius: 999px;
            background: linear-gradient(135deg, #2aa8ff, #0877ef);
            color: #fff;
            font-size: 14px;
            font-weight: 900;
            text-transform: uppercase;
            text-decoration: none;
            box-shadow: 0 16px 30px rgba(37, 99, 235, .35);
        }

        .empty-card,
        .schedule-card {
            margin-bottom: 13px;
            border-radius: 22px;
            border: 1px solid rgba(255, 255, 255, .86);
            background: rgba(255, 255, 255, .78);
            box-shadow: 0 18px 38px rgba(15, 23, 42, .09), inset 0 1px 0 rgba(255, 255, 255, .86);
            backdrop-filter: blur(16px);
            padding: 16px;
        }

        .schedule-top {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .day-name {
            margin: 0;
            font-size: 20px;
            font-weight: 900;
            letter-spacing: -.04em;
        }

        .status-pill {
            border-radius: 999px;
            padding: 7px 10px;
            font-size: 12px;
            font-weight: 850;
            text-transform: uppercase;
        }

        .status-pill.on {
            background: #dcfce7;
            color: #166534;
        }

        .status-pill.off {
            background: #e5e7eb;
            color: #475569;
        }

        .time-line {
            display: flex;
            align-items: baseline;
            gap: 8px;
            margin: 12px 0 10px;
        }

        .time-value {
            font-size: 36px;
            line-height: 1;
            font-weight: 950;
            letter-spacing: -.06em;
        }

        .time-label {
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 800;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 14px;
        }

        .detail-box {
            border-radius: 16px;
            background: #f8fafc;
            border: 1px solid rgba(148, 163, 184, .22);
            padding: 12px;
        }

        .detail-label {
            margin: 0 0 4px;
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 800;
        }

        .detail-value {
            margin: 0;
            font-size: 15px;
            font-weight: 900;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .btn {
            border: 0;
            border-radius: 999px;
            padding: 10px 14px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 850;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn.edit {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .btn.toggle {
            background: #fef3c7;
            color: #92400e;
        }

        .btn.delete {
            background: #fee2e2;
            color: #991b1b;
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
            <a href="{{ route('devices.automation', $device) }}" class="tab">Automation</a>
            <a href="{{ route('devices.schedules.index', $device) }}" class="tab active">Schedules</a>
            <a href="{{ route('devices.history', $device) }}" class="tab">History</a>
        </nav>

        @if (session('success'))
        <div class="notice">{{ session('success') }}</div>
        @endif

        <main class="phone-frame">
            <div class="app-screen">
                <div class="app-content">
                    <section class="hero">
                        <p class="eyebrow">Smart Plant Bed</p>
                        <h1 class="title">Schedule Watering</h1>
                        <!-- <p class="subtitle">Automated watering times for {{ $device->name }}. Timezone: {{ $device->timezone ?? 'Asia/Dhaka' }}.</p> -->
                    </section>

                    <a href="{{ route('devices.schedules.create', $device) }}" class="add-button">+ Add Schedule</a>

                    @php
                    $days = [
                    1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday',
                    5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday',
                    ];
                    @endphp

                    @forelse($device->wateringSchedules as $schedule)
                    <article class="schedule-card">
                        <div class="schedule-top">
                            <div>
                                <h2 class="day-name">{{ $days[$schedule->day_of_week] ?? 'Unknown Day' }}</h2>
                                <!-- <p style="margin: 5px 0 0; color: var(--text-muted); font-size: 13px; font-weight: 700;">Watering schedule</p> -->
                            </div>
                            <span class="status-pill {{ $schedule->is_enabled ? 'on' : 'off' }}">{{ $schedule->is_enabled ? 'On' : 'Off' }}</span>
                        </div>

                        <div class="time-line">
                            <span class="time-value">{{ substr($schedule->time_of_day, 0, 5) }}</span>
                            <!-- <span class="time-label">local time</span> -->
                        </div>

                        <div class="detail-grid">
                            <div class="detail-box">
                                <p class="detail-label">Duration</p>
                                <p class="detail-value">{{ $schedule->duration_seconds }} sec</p>
                            </div>
                            <div class="detail-box">
                                <p class="detail-label">Timezone</p>
                                <p class="detail-value">{{ $device->timezone ?? 'Asia/Dhaka' }}</p>
                            </div>
                        </div>

                        <div class="actions">
                            <a href="{{ route('devices.schedules.edit', [$device, $schedule]) }}" class="btn edit">Edit</a>

                            <form action="{{ route('devices.schedules.toggle', [$device, $schedule]) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn toggle">{{ $schedule->is_enabled ? 'Disable' : 'Enable' }}</button>
                            </form>

                            <form action="{{ route('devices.schedules.destroy', [$device, $schedule]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this schedule?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn delete">Delete</button>
                            </form>
                        </div>
                    </article>
                    @empty
                    <div class="empty-card">
                        <h2 class="day-name">No schedules yet</h2>
                        <p style="color: var(--text-muted); font-size: 14px; line-height: 1.45;">Create your first watering schedule to run the pump automatically.</p>
                        <a href="{{ route('devices.schedules.create', $device) }}" class="add-button" style="margin-bottom: 0;">Create First Schedule</a>
                    </div>
                    @endforelse
                </div>
            </div>
        </main>
    </div>
</body>

</html>