<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Device Settings - {{ $device->name }}</title>
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
        .app-screen { overflow: hidden; border-radius: 30px; min-height: 680px; background: linear-gradient(180deg, rgba(255,255,255,.96), rgba(239,246,255,.95)); border: 1px solid rgba(226,232,240,.95); }
        .app-content { padding: 20px 16px 18px; }
        .hero { position: relative; overflow: hidden; margin-bottom: 14px; padding: 18px; border-radius: 28px; color: #fff; background: radial-gradient(circle at 82% 18%, rgba(56,189,248,.55), transparent 8rem), radial-gradient(circle at 86% 86%, rgba(34,197,94,.18), transparent 9rem), linear-gradient(145deg, #132033 0%, #061225 100%); box-shadow: 0 22px 50px rgba(2,6,23,.28); }
        .eyebrow { margin: 0 0 6px; color: #bae6fd; font-size: 12px; font-weight: 950; letter-spacing: .08em; text-transform: uppercase; }
        .title { margin: 0; font-size: 30px; line-height: 1.02; font-weight: 950; letter-spacing: -.06em; }
        .subtitle { margin: 8px 0 0; max-width: 350px; color: #cbd5e1; font-size: 13px; line-height: 1.42; }

        .glass-card { border-radius: 24px; border: 1px solid rgba(255,255,255,.88); background: rgba(255,255,255,.82); box-shadow: 0 18px 40px rgba(15,23,42,.09), inset 0 1px 0 rgba(255,255,255,.88); backdrop-filter: blur(16px); overflow: hidden; }
        .card-accent { height: 5px; background: linear-gradient(90deg, #38bdf8, #2563eb); }
        .card-body { padding: 16px; }
        .card-head { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
        .icon-box { width: 44px; height: 44px; border-radius: 17px; display: grid; place-items: center; background: #eff6ff; font-size: 22px; }
        .card-title { margin: 0; font-size: 18px; font-weight: 950; letter-spacing: -.035em; }
        .card-subtitle { margin: 4px 0 0; color: var(--muted); font-size: 13px; font-weight: 750; line-height: 1.35; }
        .field { margin-bottom: 13px; }
        .field:last-child { margin-bottom: 0; }
        .field-label { display: block; margin-bottom: 7px; color: #334155; font-size: 13px; font-weight: 900; }
        .input, .select { width: 100%; min-height: 48px; border-radius: 15px; border: 1px solid rgba(148,163,184,.38); background: rgba(255,255,255,.94); color: var(--ink); padding: 0 14px; font-size: 15px; font-weight: 800; outline: none; }
        .input:focus, .select:focus { border-color: var(--blue); box-shadow: 0 0 0 4px rgba(22,135,249,.13); }
        .helper { margin: 7px 0 0; color: var(--muted); font-size: 12px; font-weight: 700; line-height: 1.35; }
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
    <div class="page-shell">
        <a href="{{ route('devices.show', $device) }}" class="back-link">← Back to Device</a>

        <nav class="tabs">
            <a href="{{ route('devices.show', $device) }}" class="tab">Home</a>
            @if ($device->isSmartFountain())
                <a href="{{ route('devices.smart-fountain.scenes.index', $device) }}" class="tab">Scenes</a>
                <a href="{{ route('devices.smart-fountain.schedules.index', $device) }}" class="tab">Schedule</a>
            @else
                <a href="{{ route('devices.automation', $device) }}" class="tab">Automation</a>
                <a href="{{ route('devices.schedules.index', $device) }}" class="tab">Schedules</a>
            @endif
            <a href="{{ route('devices.history', $device) }}" class="tab">History</a>
            <a href="{{ route('devices.profile.edit', $device) }}" class="tab active">Settings</a>
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
                        <p class="eyebrow">Device Settings</p>
                        <h1 class="title">Edit Device</h1>
                        <p class="subtitle">Rename this device, update its area, and keep schedules aligned with the correct timezone.</p>
                    </section>

                    <form method="POST" action="{{ route('devices.profile.update', $device) }}">
                        @csrf
                        @method('PUT')

                        <section class="glass-card">
                            <div class="card-accent"></div>
                            <div class="card-body">
                                <div class="card-head">
                                    <div class="icon-box">⚙️</div>
                                    <div>
                                        <h2 class="card-title">Profile</h2>
                                        <p class="card-subtitle">Customer-facing device information.</p>
                                    </div>
                                </div>

                                <div class="field">
                                    <label for="name" class="field-label">Device Name</label>
                                    <input id="name" type="text" name="name" value="{{ old('name', $device->name) }}" class="input" placeholder="Smart Fountain 1" required>
                                </div>

                                <div class="field">
                                    <label for="location_label" class="field-label">Area / Location</label>
                                    <input id="location_label" type="text" name="location_label" value="{{ old('location_label', $device->location_label) }}" class="input" placeholder="Living Room">
                                    <p class="helper">Example: Living Room, Balcony, Office, Entrance.</p>
                                </div>

                                <div class="field">
                                    <label for="timezone" class="field-label">Timezone</label>
                                    <select id="timezone" name="timezone" class="select" required>
                                        @foreach ($timezoneOptions as $timezone)
                                            <option value="{{ $timezone }}" @selected(old('timezone', $device->timezone ?? config('app.timezone')) === $timezone)>
                                                {{ $timezone }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="helper">Used for daily schedules and local time display.</p>
                                </div>
                            </div>
                        </section>

                        <div class="actions">
                            <button type="submit" class="primary-btn">Save Settings</button>
                            <a href="{{ route('devices.show', $device) }}" class="cancel-link">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>

</html>
