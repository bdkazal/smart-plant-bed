<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit {{ $schedule->name }} Timeline - {{ $device->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

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

        .page-shell { width: min(100%, 980px); margin: 0 auto; padding: 18px 14px 38px; }
        .back-link { display: inline-flex; margin: 0 0 14px; color: #2563eb; font-size: 14px; font-weight: 750; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
        .tabs { display: flex; gap: 9px; overflow-x: auto; padding-bottom: 12px; margin-bottom: 8px; }
        .tab { flex: 0 0 auto; min-height: 40px; display: inline-flex; align-items: center; justify-content: center; padding: 0 16px; border-radius: 999px; border: 1px solid var(--line); background: rgba(255,255,255,.82); color: var(--ink); font-size: 14px; font-weight: 850; text-decoration: none; box-shadow: 0 10px 26px rgba(15,23,42,.08); }
        .tab.active { background: var(--blue); border-color: var(--blue); color: #fff; }
        .notice { margin-bottom: 14px; border-radius: 18px; padding: 13px 15px; font-size: 14px; font-weight: 750; }
        .notice.error { background: #fee2e2; color: #991b1b; }

        .phone-frame { max-width: 520px; margin: 0 auto; padding: 16px; border-radius: 42px; background: rgba(255,255,255,.72); box-shadow: 0 34px 80px rgba(15,23,42,.16), inset 0 0 0 1px rgba(255,255,255,.92); }
        .app-screen { overflow: hidden; border-radius: 30px; min-height: 760px; background: linear-gradient(180deg, rgba(255,255,255,.96), rgba(239,246,255,.95)); border: 1px solid rgba(226,232,240,.95); }
        .app-content { padding: 20px 16px 18px; }

        .hero { position: relative; overflow: hidden; margin-bottom: 14px; padding: 18px; border-radius: 28px; color: #fff; background: radial-gradient(circle at 82% 18%, rgba(251,191,36,.35), transparent 8rem), radial-gradient(circle at 88% 86%, rgba(99,102,241,.28), transparent 9rem), linear-gradient(145deg, #132033 0%, #061225 100%); box-shadow: 0 22px 50px rgba(2,6,23,.28); }
        .hero-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; }
        .eyebrow { margin: 0 0 6px; color: #bae6fd; font-size: 12px; font-weight: 950; letter-spacing: .08em; text-transform: uppercase; }
        .title { margin: 0; font-size: 30px; line-height: 1.02; font-weight: 950; letter-spacing: -.06em; }
        .subtitle { margin: 8px 0 0; max-width: 350px; color: #cbd5e1; font-size: 13px; line-height: 1.42; }
        .period-icon { flex: 0 0 auto; width: 50px; height: 50px; display: grid; place-items: center; border-radius: 19px; background: rgba(255,255,255,.13); border: 1px solid rgba(255,255,255,.14); font-size: 25px; }

        .form-grid { display: grid; gap: 13px; }
        .glass-card { border-radius: 24px; border: 1px solid rgba(255,255,255,.88); background: rgba(255,255,255,.82); box-shadow: 0 18px 40px rgba(15,23,42,.09), inset 0 1px 0 rgba(255,255,255,.88); backdrop-filter: blur(16px); overflow: hidden; }
        .card-accent { height: 5px; background: linear-gradient(90deg, #f59e0b, #ec4899, #2563eb); }
        .card-body { padding: 16px; }
        .card-head { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
        .icon-box { width: 44px; height: 44px; border-radius: 17px; display: grid; place-items: center; background: #eff6ff; font-size: 22px; }
        .card-title { margin: 0; font-size: 18px; font-weight: 950; letter-spacing: -.035em; }
        .card-subtitle { margin: 4px 0 0; color: var(--muted); font-size: 13px; font-weight: 750; line-height: 1.35; }
        .field { margin-bottom: 13px; }
        .field:last-child { margin-bottom: 0; }
        .field-label, .switch-label { display: block; margin-bottom: 7px; color: #334155; font-size: 13px; font-weight: 900; }
        .input, .select { width: 100%; min-height: 48px; border-radius: 15px; border: 1px solid rgba(148,163,184,.38); background: rgba(255,255,255,.94); color: var(--ink); padding: 0 14px; font-size: 15px; font-weight: 800; outline: none; }
        .input:focus, .select:focus { border-color: var(--blue); box-shadow: 0 0 0 4px rgba(22,135,249,.13); }
        .helper { margin: 7px 0 0; color: var(--muted); font-size: 12px; font-weight: 700; line-height: 1.35; }

        .switch-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: 12px; }
        .switch-label { margin: 0; }
        .switch { position: relative; width: 54px; height: 31px; flex: 0 0 auto; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; inset: 0; background: #cbd5e1; border-radius: 999px; transition: .2s; }
        .slider::before { content: ""; position: absolute; width: 25px; height: 25px; left: 3px; top: 3px; border-radius: 50%; background: #fff; box-shadow: 0 3px 10px rgba(15,23,42,.22); transition: .2s; }
        .switch input:checked + .slider { background: var(--blue); }
        .switch input:checked + .slider::before { transform: translateX(23px); }

        .time-preview { margin-top: 12px; border-radius: 18px; border: 1px solid rgba(148,163,184,.20); background: #f8fafc; padding: 13px; }
        .time-preview-label { margin: 0 0 6px; color: var(--muted); font-size: 12px; font-weight: 900; text-transform: uppercase; }
        .time-preview-value { margin: 0; color: var(--ink); font-size: 20px; font-weight: 950; letter-spacing: -.04em; }
        .scene-preview { border-radius: 18px; border: 1px solid rgba(37,99,235,.18); background: #dbeafe; color: #1d4ed8; padding: 13px; font-size: 13px; font-weight: 850; line-height: 1.4; }
        .rule-note { border-radius: 20px; border: 1px solid #fde68a; background: #fffbeb; color: #92400e; padding: 13px; font-size: 13px; line-height: 1.42; font-weight: 750; }
        .actions { display: grid; grid-template-columns: 1fr auto; gap: 10px; margin-top: 14px; }
        .primary-btn, .cancel-link { min-height: 48px; display: inline-flex; align-items: center; justify-content: center; border-radius: 999px; padding: 0 18px; font-size: 13px; font-weight: 950; text-decoration: none; }
        .primary-btn { border: 0; cursor: pointer; background: linear-gradient(135deg, #2aa8ff, #0877ef); color: #fff; text-transform: uppercase; box-shadow: 0 16px 30px rgba(37,99,235,.28); }
        .cancel-link { border: 1px solid rgba(148,163,184,.34); background: #fff; color: var(--ink); }

        @media (max-width: 430px) {
            .page-shell { padding-left: 10px; padding-right: 10px; }
            .phone-frame { padding: 10px; border-radius: 30px; }
            .app-screen { border-radius: 24px; }
            .app-content { padding: 16px 13px; }
            .title { font-size: 27px; }
            .actions { grid-template-columns: 1fr; }
        }
    </style>
</head>

<body>
    @php
        $periodIcon = match ($schedule->period_key) {
            'day' => '☀️',
            'evening' => '🌆',
            'night' => '🌙',
            default => '⏱️',
        };
    @endphp

    <div class="page-shell">
        <a href="{{ route('devices.smart-fountain.schedules.index', $device) }}" class="back-link">← Back to Timeline</a>

        <nav class="tabs">
            <a href="{{ route('devices.show', $device) }}" class="tab">Home</a>
            <a href="{{ route('devices.smart-fountain.scenes.index', $device) }}" class="tab">Scenes</a>
            <a href="{{ route('devices.smart-fountain.schedules.index', $device) }}" class="tab active">Schedule</a>
            <a href="{{ route('devices.history', $device) }}" class="tab">History</a>
        </nav>

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
                                <p class="eyebrow">Smart Fountain Timeline</p>
                                <h1 class="title">Edit {{ $schedule->name }}</h1>
                                <p class="subtitle">Choose when this block starts and which scene runs. End time follows the next block automatically.</p>
                            </div>
                            <div class="period-icon">{{ $periodIcon }}</div>
                        </div>
                    </section>

                    <form method="POST" action="{{ route('devices.smart-fountain.schedules.update', [$device, $schedule]) }}">
                        @csrf
                        @method('PUT')

                        <section class="form-grid">
                            <div class="glass-card">
                                <div class="card-accent"></div>
                                <div class="card-body">
                                    <div class="card-head">
                                        <div class="icon-box">⏱️</div>
                                        <div>
                                            <h2 class="card-title">Start Time</h2>
                                            <p class="card-subtitle">The next timeline block decides the end time.</p>
                                        </div>
                                    </div>

                                    <div class="field">
                                        <label for="start_time" class="field-label">Start Time</label>
                                        <input id="start_time" type="time" name="start_time" value="{{ old('start_time', substr($schedule->start_time, 0, 5)) }}" class="input" required>
                                    </div>

                                    <div class="time-preview">
                                        <p class="time-preview-label">Current Range</p>
                                        <p class="time-preview-value">{{ substr($schedule->start_time, 0, 5) }} → {{ substr($schedule->end_time, 0, 5) }}</p>
                                        <p class="helper">After saving, the end time is recalculated from the next block start time.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="glass-card">
                                <div class="card-accent"></div>
                                <div class="card-body">
                                    <div class="card-head">
                                        <div class="icon-box">✨</div>
                                        <div>
                                            <h2 class="card-title">Scene</h2>
                                            <p class="card-subtitle">Pick the preset that starts this block.</p>
                                        </div>
                                    </div>

                                    <div class="field">
                                        <label for="start_scene_id" class="field-label">Scene to Apply</label>
                                        <select id="start_scene_id" name="start_scene_id" class="select" required>
                                            @foreach ($scenes as $scene)
                                                <option value="{{ $scene->id }}" @selected((int) old('start_scene_id', $schedule->start_scene_id) === $scene->id)>
                                                    {{ $scene->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="scene-preview">
                                        Current scene: {{ $schedule->startScene?->name ?? 'Missing scene' }}
                                    </div>

                                    <div class="switch-row">
                                        <span class="switch-label">Enable this block</span>
                                        <label class="switch">
                                            <input type="checkbox" name="is_enabled" value="1" @checked(old('is_enabled', $schedule->is_enabled))>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="rule-note">
                                Timeline order must stay Day start &lt; Evening start &lt; Night start. Day ends when Evening starts, Evening ends when Night starts, and Night ends when Day starts.
                            </div>
                        </section>

                        <div class="actions">
                            <button type="submit" class="primary-btn">Update Timeline Block</button>
                            <a href="{{ route('devices.smart-fountain.schedules.index', $device) }}" class="cancel-link">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>

</html>