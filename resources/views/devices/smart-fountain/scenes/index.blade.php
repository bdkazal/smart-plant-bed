<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $device->name }} - Scenes</title>
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
        .notice.success { background: #dcfce7; color: #166534; }
        .notice.error { background: #fee2e2; color: #991b1b; }

        .phone-frame { max-width: 520px; margin: 0 auto; padding: 16px; border-radius: 42px; background: rgba(255,255,255,.72); box-shadow: 0 34px 80px rgba(15,23,42,.16), inset 0 0 0 1px rgba(255,255,255,.92); }
        .app-screen { overflow: hidden; border-radius: 30px; min-height: 760px; background: linear-gradient(180deg, rgba(255,255,255,.96), rgba(239,246,255,.95)); border: 1px solid rgba(226,232,240,.95); }
        .app-content { padding: 20px 16px 18px; }
        .hero { position: relative; overflow: hidden; margin-bottom: 14px; padding: 18px; border-radius: 28px; color: #fff; background: radial-gradient(circle at 82% 18%, rgba(56,189,248,.55), transparent 8rem), radial-gradient(circle at 86% 86%, rgba(236,72,153,.20), transparent 9rem), linear-gradient(145deg, #132033 0%, #061225 100%); box-shadow: 0 22px 50px rgba(2,6,23,.28); }
        .hero-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; }
        .eyebrow { margin: 0 0 6px; color: #bae6fd; font-size: 12px; font-weight: 950; letter-spacing: .08em; text-transform: uppercase; }
        .title { margin: 0; font-size: 30px; line-height: 1.02; font-weight: 950; letter-spacing: -.06em; }
        .subtitle { margin: 8px 0 0; max-width: 320px; color: #cbd5e1; font-size: 13px; line-height: 1.42; }
        .create-pill { flex: 0 0 auto; display: inline-flex; align-items: center; justify-content: center; min-height: 42px; padding: 0 14px; border-radius: 999px; background: #fff; color: #0f172a; font-size: 13px; font-weight: 950; text-decoration: none; box-shadow: 0 14px 30px rgba(15,23,42,.20); }

        .empty-card, .scene-card { border-radius: 24px; border: 1px solid rgba(255,255,255,.88); background: rgba(255,255,255,.82); box-shadow: 0 18px 40px rgba(15,23,42,.09), inset 0 1px 0 rgba(255,255,255,.88); backdrop-filter: blur(16px); }
        .empty-card { padding: 18px; }
        .scene-grid { display: grid; gap: 13px; }
        .scene-card { overflow: hidden; }
        .scene-accent { height: 5px; background: linear-gradient(90deg, #38bdf8, #8b5cf6, #ec4899); }
        .scene-body { padding: 16px; }
        .scene-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 13px; }
        .scene-title { margin: 0; font-size: 20px; line-height: 1.12; font-weight: 950; letter-spacing: -.045em; }
        .scene-subtitle { margin: 4px 0 0; color: var(--muted); font-size: 13px; font-weight: 750; }
        .scene-icon { width: 44px; height: 44px; border-radius: 17px; display: grid; place-items: center; background: #eff6ff; font-size: 22px; }
        .outputs { display: grid; gap: 9px; margin-bottom: 14px; }
        .output-row { display: grid; grid-template-columns: 38px 1fr auto; align-items: center; gap: 10px; border-radius: 17px; border: 1px solid rgba(148,163,184,.20); background: #f8fafc; padding: 10px; }
        .output-icon { width: 38px; height: 38px; border-radius: 14px; display: grid; place-items: center; background: #fff; font-size: 18px; box-shadow: 0 8px 18px rgba(15,23,42,.06); }
        .output-name { display: block; font-size: 13px; font-weight: 950; }
        .output-detail { display: block; margin-top: 2px; color: var(--muted); font-size: 12px; font-weight: 700; }
        .state-pill { border-radius: 999px; padding: 6px 9px; font-size: 11px; font-weight: 950; text-transform: uppercase; }
        .state-on { background: #dcfce7; color: #166534; }
        .state-off { background: #e5e7eb; color: #475569; }
        .color-dot { display: inline-block; width: 10px; height: 10px; margin-right: 4px; border-radius: 999px; border: 1px solid rgba(15,23,42,.2); vertical-align: -1px; }
        .apply-btn { width: 100%; min-height: 48px; border: 0; border-radius: 999px; cursor: pointer; background: linear-gradient(135deg, #2aa8ff, #0877ef); color: #fff; font-size: 13px; font-weight: 950; text-transform: uppercase; box-shadow: 0 16px 30px rgba(37,99,235,.28); }
        .secondary-actions { display: flex; gap: 9px; margin-top: 12px; }
        .action-link, .delete-btn { min-height: 39px; display: inline-flex; align-items: center; justify-content: center; border-radius: 999px; padding: 0 15px; font-size: 13px; font-weight: 850; text-decoration: none; }
        .action-link { border: 1px solid rgba(148,163,184,.34); background: #fff; color: var(--ink); }
        .delete-btn { border: 0; background: #fee2e2; color: #991b1b; cursor: pointer; }

        @media (max-width: 430px) {
            .page-shell { padding-left: 10px; padding-right: 10px; }
            .phone-frame { padding: 10px; border-radius: 30px; }
            .app-screen { border-radius: 24px; }
            .app-content { padding: 16px 13px; }
            .title { font-size: 27px; }
            .create-pill { min-height: 38px; padding: 0 12px; }
        }
    </style>
</head>

<body>
    <div class="page-shell">
        <a href="{{ route('devices.index') }}" class="back-link">← Back to Devices</a>

        <nav class="tabs">
            <a href="{{ route('devices.show', $device) }}" class="tab">Home</a>
            <a href="{{ route('devices.smart-fountain.scenes.index', $device) }}" class="tab active">Scenes</a>
            <a href="{{ route('devices.smart-fountain.schedules.index', $device) }}" class="tab">Schedule</a>
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
                        <div class="hero-top">
                            <div>
                                <p class="eyebrow">Smart Fountain</p>
                                <h1 class="title">Scenes</h1>
                                <p class="subtitle">One-tap presets for pump, COB light, and RGB ambience.</p>
                            </div>
                            <a href="{{ route('devices.smart-fountain.scenes.create', $device) }}" class="create-pill">+ Create</a>
                        </div>
                    </section>

                    @if ($device->scenes->isEmpty())
                        <section class="empty-card">
                            <h2 class="scene-title">No scenes yet</h2>
                            <p class="scene-subtitle">Create presets such as Day Fountain, Night Glow, Display Mode, or All Off.</p>
                            <div style="margin-top: 16px;">
                                <a href="{{ route('devices.smart-fountain.scenes.create', $device) }}" class="create-pill" style="background:#1687f9;color:white;box-shadow:none;">Create First Scene</a>
                            </div>
                        </section>
                    @else
                        <section class="scene-grid">
                            @foreach ($device->scenes as $scene)
                                @php
                                    $pump = data_get($scene->outputs, 'pump', []);
                                    $cob = data_get($scene->outputs, 'cob_light', []);
                                    $rgb = data_get($scene->outputs, 'rgb_light', []);
                                    $sceneIcon = str_contains(strtolower($scene->name), 'off') ? '⏻' : (str_contains(strtolower($scene->name), 'night') ? '🌙' : (str_contains(strtolower($scene->name), 'display') ? '✨' : '⛲'));
                                @endphp

                                <article class="scene-card">
                                    <div class="scene-accent"></div>
                                    <div class="scene-body">
                                        <div class="scene-head">
                                            <div>
                                                <h2 class="scene-title">{{ $scene->name }}</h2>
                                                <p class="scene-subtitle">Full fountain preset</p>
                                            </div>
                                            <div class="scene-icon">{{ $sceneIcon }}</div>
                                        </div>

                                        <div class="outputs">
                                            <div class="output-row">
                                                <div class="output-icon">💧</div>
                                                <div>
                                                    <span class="output-name">Pump</span>
                                                    <span class="output-detail">Speed {{ data_get($pump, 'speed_percent', 0) }}%</span>
                                                </div>
                                                <span class="state-pill {{ data_get($pump, 'enabled') ? 'state-on' : 'state-off' }}">{{ data_get($pump, 'enabled') ? 'On' : 'Off' }}</span>
                                            </div>

                                            <div class="output-row">
                                                <div class="output-icon">☀️</div>
                                                <div>
                                                    <span class="output-name">COB Light</span>
                                                    <span class="output-detail">Brightness {{ data_get($cob, 'brightness_percent', 0) }}%</span>
                                                </div>
                                                <span class="state-pill {{ data_get($cob, 'enabled') ? 'state-on' : 'state-off' }}">{{ data_get($cob, 'enabled') ? 'On' : 'Off' }}</span>
                                            </div>

                                            <div class="output-row">
                                                <div class="output-icon">🌈</div>
                                                <div>
                                                    <span class="output-name">RGB Light</span>
                                                    <span class="output-detail"><span class="color-dot" style="background: {{ data_get($rgb, 'color', '#000000') }}"></span>{{ ucwords(str_replace('_', ' ', data_get($rgb, 'effect', 'N/A'))) }} · {{ data_get($rgb, 'brightness_percent', 0) }}%</span>
                                                </div>
                                                <span class="state-pill {{ data_get($rgb, 'enabled') ? 'state-on' : 'state-off' }}">{{ data_get($rgb, 'enabled') ? 'On' : 'Off' }}</span>
                                            </div>
                                        </div>

                                        <form method="POST" action="{{ route('devices.smart-fountain.scenes.apply', [$device, $scene]) }}">
                                            @csrf
                                            <button type="submit" class="apply-btn">Apply Scene</button>
                                        </form>

                                        <div class="secondary-actions">
                                            <a href="{{ route('devices.smart-fountain.scenes.edit', [$device, $scene]) }}" class="action-link">Edit</a>
                                            <form method="POST" action="{{ route('devices.smart-fountain.scenes.destroy', [$device, $scene]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="delete-btn">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </section>
                    @endif
                </div>
            </div>
        </main>
    </div>
</body>

</html>
