<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $device->name }} - Daily Timeline</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --page-bg: #eaf1f8;
            --ink: #0f172a;
            --muted: #64748b;
            --line: rgba(148, 163, 184, .28);
            --blue: #1687f9;
        }

        * {
            box-sizing: border-box;
        }

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

        .page-shell {
            width: min(100%, 980px);
            margin: 0 auto;
            padding: 18px 14px 38px;
        }

        .back-link {
            display: inline-flex;
            margin: 0 0 14px;
            color: #2563eb;
            font-size: 14px;
            font-weight: 750;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .tabs {
            display: flex;
            gap: 9px;
            overflow-x: auto;
            padding-bottom: 12px;
            margin-bottom: 8px;
        }

        .tab {
            flex: 0 0 auto;
            min-height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 16px;
            border-radius: 999px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, .82);
            color: var(--ink);
            font-size: 14px;
            font-weight: 850;
            text-decoration: none;
            box-shadow: 0 10px 26px rgba(15, 23, 42, .08);
        }

        .tab.active {
            background: var(--blue);
            border-color: var(--blue);
            color: #fff;
        }

        .notice {
            margin-bottom: 14px;
            border-radius: 18px;
            padding: 13px 15px;
            font-size: 14px;
            font-weight: 750;
        }

        .notice.success {
            background: #dcfce7;
            color: #166534;
        }

        .notice.error {
            background: #fee2e2;
            color: #991b1b;
        }

        .phone-frame {
            max-width: 520px;
            margin: 0 auto;
            padding: 16px;
            border-radius: 42px;
            background: rgba(255, 255, 255, .72);
            box-shadow: 0 34px 80px rgba(15, 23, 42, .16), inset 0 0 0 1px rgba(255, 255, 255, .92);
        }

        .app-screen {
            overflow: hidden;
            border-radius: 30px;
            min-height: 760px;
            background: linear-gradient(180deg, rgba(255, 255, 255, .96), rgba(239, 246, 255, .95));
            border: 1px solid rgba(226, 232, 240, .95);
        }

        .app-content {
            padding: 20px 16px 18px;
        }

        .hero {
            position: relative;
            overflow: hidden;
            margin-bottom: 14px;
            padding: 18px;
            border-radius: 28px;
            color: #fff;
            background: radial-gradient(circle at 82% 18%, rgba(251, 191, 36, .35), transparent 8rem), radial-gradient(circle at 88% 86%, rgba(99, 102, 241, .28), transparent 9rem), linear-gradient(145deg, #132033 0%, #061225 100%);
            box-shadow: 0 22px 50px rgba(2, 6, 23, .28);
        }

        .eyebrow {
            margin: 0 0 6px;
            color: #bae6fd;
            font-size: 12px;
            font-weight: 950;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .title {
            margin: 0;
            font-size: 30px;
            line-height: 1.02;
            font-weight: 950;
            letter-spacing: -.06em;
        }

        .subtitle {
            margin: 8px 0 0;
            max-width: 350px;
            color: #cbd5e1;
            font-size: 13px;
            line-height: 1.42;
        }

        .timeline-strip {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 13px;
        }

        .strip-segment {
            border-radius: 18px;
            padding: 11px 9px;
            color: #fff;
            box-shadow: 0 12px 26px rgba(15, 23, 42, .10);
        }

        .strip-segment.day {
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
        }

        .strip-segment.evening {
            background: linear-gradient(135deg, #f97316, #ec4899);
        }

        .strip-segment.night {
            background: linear-gradient(135deg, #312e81, #2563eb);
        }

        .strip-name {
            display: block;
            font-size: 12px;
            font-weight: 950;
        }

        .strip-time {
            display: block;
            margin-top: 4px;
            font-size: 11px;
            font-weight: 750;
            opacity: .92;
        }

        .timeline-grid {
            display: grid;
            gap: 13px;
        }

        .timeline-card {
            overflow: hidden;
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, .88);
            background: rgba(255, 255, 255, .82);
            box-shadow: 0 18px 40px rgba(15, 23, 42, .09), inset 0 1px 0 rgba(255, 255, 255, .88);
            backdrop-filter: blur(16px);
        }

        .timeline-accent {
            height: 5px;
        }

        .timeline-card.day .timeline-accent {
            background: linear-gradient(90deg, #f59e0b, #fbbf24);
        }

        .timeline-card.evening .timeline-accent {
            background: linear-gradient(90deg, #f97316, #ec4899);
        }

        .timeline-card.night .timeline-accent {
            background: linear-gradient(90deg, #312e81, #2563eb);
        }

        .timeline-body {
            padding: 16px;
        }

        .timeline-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 13px;
        }

        .period-icon {
            width: 46px;
            height: 46px;
            border-radius: 18px;
            display: grid;
            place-items: center;
            background: #eff6ff;
            font-size: 23px;
        }

        .period-title {
            margin: 0;
            font-size: 21px;
            line-height: 1.12;
            font-weight: 950;
            letter-spacing: -.045em;
        }

        .period-subtitle {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: 13px;
            font-weight: 750;
        }

        .state-pill {
            border-radius: 999px;
            padding: 6px 10px;
            font-size: 11px;
            font-weight: 950;
            text-transform: uppercase;
        }

        .state-on {
            background: #dcfce7;
            color: #166534;
        }

        .state-off {
            background: #e5e7eb;
            color: #475569;
        }

        .time-block {
            margin-bottom: 13px;
            border-radius: 22px;
            padding: 16px;
            background: #f8fafc;
            border: 1px solid rgba(148, 163, 184, .20);
            text-align: center;
        }

        .time-value {
            display: block;
            font-size: 28px;
            line-height: 1;
            font-weight: 950;
            letter-spacing: -.06em;
        }

        .scene-name {
            display: inline-flex;
            margin-top: 10px;
            border-radius: 999px;
            padding: 7px 11px;
            background: #dbeafe;
            color: #1d4ed8;
            font-size: 12px;
            font-weight: 950;
        }

        .detail-list {
            display: grid;
            gap: 8px;
            margin-bottom: 14px;
        }

        .detail-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            border-radius: 16px;
            background: rgba(248, 250, 252, .82);
            border: 1px solid rgba(148, 163, 184, .16);
            padding: 10px 12px;
            font-size: 13px;
        }

        .detail-row span:first-child {
            color: var(--muted);
            font-weight: 850;
        }

        .detail-row span:last-child {
            text-align: right;
            color: var(--ink);
            font-weight: 900;
        }

        .actions {
            display: flex;
            gap: 9px;
        }

        .primary-action,
        .secondary-action {
            min-height: 42px;
            display: inline-flex;
            flex: 1;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 0 14px;
            font-size: 13px;
            font-weight: 950;
            text-decoration: none;
        }

        .primary-action {
            border: 0;
            background: linear-gradient(135deg, #2aa8ff, #0877ef);
            color: #fff;
            box-shadow: 0 16px 30px rgba(37, 99, 235, .22);
        }

        .secondary-action {
            border: 1px solid rgba(148, 163, 184, .34);
            background: #fff;
            color: var(--ink);
            cursor: pointer;
        }

        @media (max-width: 430px) {
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
                padding: 16px 13px;
            }

            .title {
                font-size: 27px;
            }

            .timeline-strip {
                grid-template-columns: 1fr;
            }

            .time-value {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>
    <div class="page-shell">
        <a href="{{ route('devices.index') }}" class="back-link">← Back to Devices</a>

        <nav class="tabs">
            <a href="{{ route('devices.show', $device) }}" class="tab">Home</a>
            <a href="{{ route('devices.smart-fountain.scenes.index', $device) }}" class="tab">Scenes</a>
            <a href="{{ route('devices.smart-fountain.schedules.index', $device) }}" class="tab active">Schedule</a>
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
                        <p class="eyebrow">Smart Fountain Schedule</p>
                        <h1 class="title">Daily Timeline</h1>
                        <p class="subtitle">Day, Evening, and Night cover the full 24 hours without gaps.</p>
                    </section>

                    <section class="timeline-strip">
                        @foreach ($schedules as $schedule)
                        <div class="strip-segment {{ $schedule->period_key }}">
                            <span class="strip-name">{{ $schedule->name }}</span>
                            <span class="strip-time">{{ substr($schedule->start_time, 0, 5) }} → {{ substr($schedule->end_time, 0, 5) }}</span>
                        </div>
                        @endforeach
                    </section>

                    <section class="timeline-grid">
                        @foreach ($schedules as $schedule)
                        @php
                        $periodIcon = match ($schedule->period_key) {
                        'day' => '☀️',
                        'evening' => '🌆',
                        'night' => '🌙',
                        default => '⏱️',
                        };
                        @endphp

                        <article class="timeline-card {{ $schedule->period_key }}">
                            <div class="timeline-accent"></div>
                            <div class="timeline-body">
                                <div class="timeline-head">
                                    <div style="display:flex;gap:12px;align-items:center;">
                                        <div class="period-icon">{{ $periodIcon }}</div>
                                        <div>
                                            <h2 class="period-title">{{ $schedule->name }}</h2>
                                            <p class="period-subtitle">{{ ucfirst($schedule->period_key) }} timeline block</p>
                                        </div>
                                    </div>
                                    <span class="state-pill {{ $schedule->is_enabled ? 'state-on' : 'state-off' }}">{{ $schedule->is_enabled ? 'On' : 'Off' }}</span>
                                </div>

                                <div class="time-block">
                                    <span class="time-value">{{ substr($schedule->start_time, 0, 5) }} → {{ substr($schedule->end_time, 0, 5) }}</span>
                                    <span class="scene-name">{{ $schedule->startScene?->name ?? 'Missing scene' }}</span>
                                </div>

                                <div class="detail-list">
                                    <div class="detail-row">
                                        <span>Last Applied</span>
                                        <span>{{ $schedule->last_started_at?->format('M d, Y · h:i A') ?? 'Never' }}</span>
                                    </div>
                                </div>

                                <div class="actions">
                                    <a href="{{ route('devices.smart-fountain.schedules.edit', [$device, $schedule]) }}" class="primary-action">Edit Block</a>
                                    <form method="POST" action="{{ route('devices.smart-fountain.schedules.toggle', [$device, $schedule]) }}" style="flex:1;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="secondary-action" style="width:100%;">
                                            {{ $schedule->is_enabled ? 'Disable' : 'Enable' }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </article>
                        @endforeach
                    </section>
                </div>
            </div>
        </main>
    </div>
</body>

</html>