<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Device - Smart Plant Bed</title>
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

        .page-shell { width: min(100%, 620px); margin: 0 auto; padding: 24px 14px 38px; }
        .back-link { display: inline-flex; margin: 0 0 14px; color: #2563eb; font-size: 14px; font-weight: 750; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
        .hero { position: relative; overflow: hidden; margin-bottom: 14px; padding: 20px; border-radius: 30px; color: #fff; background: radial-gradient(circle at 82% 18%, rgba(56,189,248,.48), transparent 9rem), radial-gradient(circle at 88% 88%, rgba(34,197,94,.20), transparent 9rem), linear-gradient(145deg, #132033 0%, #061225 100%); box-shadow: 0 24px 54px rgba(2,6,23,.24); }
        .hero::after { content: '＋'; position: absolute; right: 22px; bottom: 12px; font-size: 58px; opacity: .14; pointer-events: none; }
        .eyebrow { margin: 0 0 6px; color: #bae6fd; font-size: 12px; font-weight: 950; letter-spacing: .08em; text-transform: uppercase; }
        .title { margin: 0; font-size: 34px; line-height: 1.02; font-weight: 950; letter-spacing: -.06em; }
        .subtitle { margin: 9px 0 0; max-width: 430px; color: #cbd5e1; font-size: 14px; line-height: 1.42; }
        .claim-card { overflow: hidden; border-radius: 28px; border: 1px solid rgba(255,255,255,.86); background: rgba(255,255,255,.84); box-shadow: 0 20px 46px rgba(15,23,42,.10), inset 0 1px 0 rgba(255,255,255,.88); backdrop-filter: blur(16px); }
        .accent { height: 5px; background: linear-gradient(90deg, #38bdf8, #8b5cf6, #22c55e); }
        .card-body { padding: 20px; }
        .scan-box { display: grid; grid-template-columns: 58px 1fr; gap: 13px; align-items: center; margin-bottom: 18px; border-radius: 20px; background: #f8fafc; border: 1px solid rgba(148,163,184,.18); padding: 13px; }
        .scan-icon { width: 58px; height: 58px; display: grid; place-items: center; border-radius: 20px; background: #eff6ff; font-size: 28px; }
        .scan-title { margin: 0; font-size: 16px; font-weight: 950; }
        .scan-note { margin: 4px 0 0; color: var(--muted); font-size: 13px; line-height: 1.4; font-weight: 720; }
        .notice.error { margin-bottom: 14px; border-radius: 18px; padding: 13px 15px; background: #fee2e2; color: #991b1b; font-size: 14px; font-weight: 750; }
        .field { margin-bottom: 15px; }
        .label { display: block; margin-bottom: 8px; color: #334155; font-size: 13px; font-weight: 950; }
        .input { width: 100%; min-height: 52px; border-radius: 16px; border: 1px solid rgba(148,163,184,.38); background: rgba(255,255,255,.94); color: var(--ink); padding: 0 15px; font-size: 18px; font-weight: 950; letter-spacing: .08em; text-transform: uppercase; outline: none; }
        .input:focus { border-color: var(--blue); box-shadow: 0 0 0 4px rgba(22,135,249,.13); }
        .helper { margin: 8px 0 0; color: var(--muted); font-size: 12px; font-weight: 720; }
        .submit-btn { width: 100%; min-height: 50px; border: 0; border-radius: 999px; cursor: pointer; background: linear-gradient(135deg, #2aa8ff, #0877ef); color: #fff; font-size: 13px; font-weight: 950; text-transform: uppercase; box-shadow: 0 16px 30px rgba(37,99,235,.28); }
        .tips { margin-top: 14px; border-radius: 20px; border: 1px solid rgba(37,99,235,.18); background: rgba(219,234,254,.78); color: #1e3a8a; padding: 13px; font-size: 13px; line-height: 1.42; font-weight: 750; }
    </style>
</head>

<body>
    <div class="page-shell">
        <a href="{{ route('devices.index') }}" class="back-link">← Back to Devices</a>

        <section class="hero">
            <p class="eyebrow">Device Claim</p>
            <h1 class="title">Add Device</h1>
            <p class="subtitle">Scan the QR code on your device, or enter the short claim code manually.</p>
        </section>

        <section class="claim-card">
            <div class="accent"></div>
            <div class="card-body">
                <div class="scan-box">
                    <div class="scan-icon">⌗</div>
                    <div>
                        <p class="scan-title">Short setup code</p>
                        <p class="scan-note">Use the code printed on the device label or shown during setup.</p>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="notice error">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('devices.claim') }}" method="POST">
                    @csrf

                    <div class="field">
                        <label for="claim_code" class="label">Claim Code</label>
                        <input
                            type="text"
                            name="claim_code"
                            id="claim_code"
                            value="{{ old('claim_code') }}"
                            placeholder="PB72K9"
                            class="input"
                            autocomplete="off"
                            required>
                        <p class="helper">Letters and numbers only. Example: PB72K9</p>
                    </div>

                    <button type="submit" class="submit-btn">Claim Device</button>
                </form>

                <div class="tips">
                    After claiming, the device will appear in your dashboard and can be renamed from Device Settings.
                </div>
            </div>
        </section>
    </div>
</body>

</html>
