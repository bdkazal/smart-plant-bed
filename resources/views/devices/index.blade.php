<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Devices - Smart Plant Bed</title>
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
                radial-gradient(circle at bottom right, rgba(34, 197, 94, .12), transparent 28rem),
                linear-gradient(135deg, #f8fbff 0%, var(--page-bg) 55%, #dce8f5 100%);
        }

        .page-shell { width: min(100%, 980px); margin: 0 auto; padding: 24px 14px 38px; }
        .hero { position: relative; overflow: hidden; margin-bottom: 16px; padding: 20px; border-radius: 30px; color: #fff; background: radial-gradient(circle at 82% 18%, rgba(56,189,248,.48), transparent 9rem), radial-gradient(circle at 88% 88%, rgba(34,197,94,.20), transparent 9rem), linear-gradient(145deg, #132033 0%, #061225 100%); box-shadow: 0 24px 54px rgba(2,6,23,.24); }
        .hero::after { content: '🌿'; position: absolute; right: 22px; bottom: 16px; font-size: 52px; opacity: .14; pointer-events: none; }
        .hero-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; position: relative; z-index: 1; }
        .eyebrow { margin: 0 0 6px; color: #bae6fd; font-size: 12px; font-weight: 950; letter-spacing: .08em; text-transform: uppercase; }
        .title { margin: 0; font-size: 34px; line-height: 1.02; font-weight: 950; letter-spacing: -.06em; }
        .subtitle { margin: 9px 0 0; max-width: 430px; color: #cbd5e1; font-size: 14px; line-height: 1.42; }
        .hero-actions { display: flex; gap: 9px; flex-wrap: wrap; justify-content: flex-end; }
        .hero-btn { min-height: 42px; display: inline-flex; align-items: center; justify-content: center; border-radius: 999px; padding: 0 15px; border: 1px solid rgba(255,255,255,.16); font-size: 13px; font-weight: 950; text-decoration: none; cursor: pointer; }
        .hero-btn.primary { background: #fff; color: #0f172a; box-shadow: 0 14px 30px rgba(15,23,42,.20); }
        .hero-btn.dark { background: rgba(255,255,255,.10); color: #e2e8f0; }

        .notice { margin-bottom: 14px; border-radius: 18px; padding: 13px 15px; background: #dcfce7; color: #166534; font-size: 14px; font-weight: 750; }
        .devices-grid { display: grid; gap: 14px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .device-card { position: relative; overflow: hidden; display: block; min-height: 190px; border-radius: 26px; border: 1px solid rgba(255,255,255,.86); background: rgba(255,255,255,.82); color: var(--ink); text-decoration: none; box-shadow: 0 18px 40px rgba(15,23,42,.09), inset 0 1px 0 rgba(255,255,255,.88); backdrop-filter: blur(16px); transition: transform .18s ease, box-shadow .18s ease; }
        .device-card:hover { transform: translateY(-2px); box-shadow: 0 24px 54px rgba(15,23,42,.14), inset 0 1px 0 rgba(255,255,255,.88); }
        .device-card::before { content: ''; position: absolute; inset: 0 0 auto 0; height: 5px; background: linear-gradient(90deg, #38bdf8, #2563eb); }
        .device-card.smart-fountain::before { background: linear-gradient(90deg, #38bdf8, #8b5cf6, #ec4899); }
        .device-card.plant-bed::before { background: linear-gradient(90deg, #22c55e, #14b8a6); }
        .device-body { padding: 18px; }
        .device-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 15px; }
        .device-icon { width: 48px; height: 48px; display: grid; place-items: center; border-radius: 18px; background: #eff6ff; font-size: 24px; box-shadow: 0 10px 22px rgba(15,23,42,.07); }
        .device-name { margin: 0; font-size: 21px; line-height: 1.1; font-weight: 950; letter-spacing: -.045em; }
        .device-type { margin: 5px 0 0; color: var(--muted); font-size: 13px; font-weight: 760; }
        .status-pill { flex: 0 0 auto; border-radius: 999px; padding: 7px 10px; background: #dcfce7; color: #166534; font-size: 11px; font-weight: 950; text-transform: uppercase; }
        .info-list { display: grid; gap: 8px; }
        .info-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; border-radius: 15px; background: #f8fafc; border: 1px solid rgba(148,163,184,.18); padding: 9px 10px; font-size: 13px; }
        .info-row span:first-child { color: var(--muted); font-weight: 850; }
        .info-row span:last-child { text-align: right; color: var(--ink); font-weight: 900; }
        .empty-card { border-radius: 26px; border: 1px solid rgba(255,255,255,.86); background: rgba(255,255,255,.82); box-shadow: 0 18px 40px rgba(15,23,42,.09); padding: 20px; }
        .empty-card h2 { margin: 0; font-size: 22px; font-weight: 950; }
        .empty-card p { color: var(--muted); line-height: 1.45; }
        .add-empty { display: inline-flex; min-height: 44px; align-items: center; justify-content: center; border-radius: 999px; background: linear-gradient(135deg, #2aa8ff, #0877ef); color: #fff; padding: 0 16px; font-weight: 950; text-decoration: none; }

        @media (max-width: 720px) {
            .hero-top { flex-direction: column; }
            .hero-actions { justify-content: flex-start; }
            .devices-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>

<body>
    <div class="page-shell">
        <section class="hero">
            <div class="hero-top">
                <div>
                    <p class="eyebrow">Smart Device Hub</p>
                    <h1 class="title">My Devices</h1>
                    <p class="subtitle">Manage your connected plant beds, fountains, and smart growing devices.</p>
                </div>

                <div class="hero-actions">
                    <a href="{{ route('devices.add') }}" class="hero-btn primary">+ Add Device</a>
                    <form method="POST" action="/logout">
                        @csrf
                        <button type="submit" class="hero-btn dark">Logout</button>
                    </form>
                </div>
            </div>
        </section>

        @if (session('success'))
            <div class="notice">{{ session('success') }}</div>
        @endif

        @if ($devices->isEmpty())
            <section class="empty-card">
                <h2>No devices yet</h2>
                <p>You have not added any devices yet. Start by claiming a device with its short setup code.</p>
                <a href="{{ route('devices.add') }}" class="add-empty">Add Your First Device</a>
            </section>
        @else
            <section class="devices-grid">
                @foreach ($devices as $device)
                    @php
                        $typeName = strtolower($device->displayType());
                        $isFountain = str_contains($typeName, 'fountain');
                        $cardClass = $isFountain ? 'smart-fountain' : 'plant-bed';
                        $icon = $isFountain ? '⛲' : '🌱';
                    @endphp

                    <a href="{{ route('devices.show', $device) }}" class="device-card {{ $cardClass }}">
                        <div class="device-body">
                            <div class="device-head">
                                <div style="display:flex;gap:12px;align-items:center;">
                                    <div class="device-icon">{{ $icon }}</div>
                                    <div>
                                        <h2 class="device-name">{{ $device->name }}</h2>
                                        <p class="device-type">{{ $device->displayType() }}</p>
                                    </div>
                                </div>

                                <span class="status-pill">{{ ucfirst(str_replace('_', ' ', $device->status)) }}</span>
                            </div>

                            <div class="info-list">
                                <div class="info-row">
                                    <span>Location</span>
                                    <span>{{ $device->location_label ?? 'N/A' }}</span>
                                </div>
                                <div class="info-row">
                                    <span>Timezone</span>
                                    <span>{{ $device->timezone ?? 'Asia/Dhaka' }}</span>
                                </div>
                                <div class="info-row">
                                    <span>Last Seen</span>
                                    <span>{{ $device->last_seen_at?->diffForHumans() ?? 'Never' }}</span>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </section>
        @endif
    </div>
</body>

</html>
