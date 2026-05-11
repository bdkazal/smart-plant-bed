<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $device->name }} - History</title>
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
            max-width: 500px;
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

        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 16px;
        }

        .summary-card {
            border-radius: 18px;
            background: rgba(255, 255, 255, .78);
            border: 1px solid rgba(255, 255, 255, .86);
            padding: 13px;
            box-shadow: 0 14px 30px rgba(15, 23, 42, .07);
        }

        .summary-label {
            margin: 0 0 5px;
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 800;
        }

        .summary-value {
            margin: 0;
            font-size: 19px;
            font-weight: 950;
            letter-spacing: -.04em;
        }

        .filter-row {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }

        .filter-chip {
            flex: 0 0 auto;
            border-radius: 999px;
            border: 1px solid rgba(148, 163, 184, .32);
            background: rgba(255, 255, 255, .82);
            padding: 8px 12px;
            color: var(--text-main);
            font-size: 13px;
            font-weight: 850;
            text-decoration: none;
        }

        .filter-chip.active {
            background: #dbeafe;
            color: #1d4ed8;
            border-color: #bfdbfe;
        }

        .section-label {
            margin: 14px 0 10px;
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .activity-list {
            display: grid;
            gap: 12px;
        }

        .activity-card {
            display: grid;
            grid-template-columns: 42px 1fr;
            gap: 12px;
            border-radius: 22px;
            border: 1px solid rgba(255, 255, 255, .86);
            background: rgba(255, 255, 255, .80);
            box-shadow: 0 18px 38px rgba(15, 23, 42, .08), inset 0 1px 0 rgba(255, 255, 255, .86);
            backdrop-filter: blur(16px);
            padding: 14px;
        }

        .activity-icon {
            width: 42px;
            height: 42px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            background: #eff6ff;
        }

        .activity-title-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 5px;
        }

        .activity-title {
            margin: 0;
            font-size: 15px;
            font-weight: 950;
            letter-spacing: -.02em;
        }

        .activity-time {
            margin: 0 0 8px;
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 700;
        }

        .activity-details {
            margin: 0;
            color: #334155;
            font-size: 13px;
            line-height: 1.45;
        }

        .status-pill {
            flex: 0 0 auto;
            border-radius: 999px;
            padding: 5px 8px;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .status-executed,
        .status-completed,
        .status-finished {
            background: #dcfce7;
            color: #166534;
        }

        .status-pending,
        .status-waiting,
        .status-requested {
            background: #fef3c7;
            color: #92400e;
        }

        .status-acknowledged,
        .status-watering,
        .status-running {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .status-failed,
        .status-expired,
        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-default {
            background: #e5e7eb;
            color: #475569;
        }

        details.tech-details {
            margin-top: 10px;
        }

        details.tech-details summary {
            cursor: pointer;
            color: #2563eb;
            font-size: 12px;
            font-weight: 850;
            list-style: none;
        }

        details.tech-details summary::-webkit-details-marker {
            display: none;
        }

        .tech-pre {
            margin: 9px 0 0;
            overflow-x: auto;
            border-radius: 14px;
            background: #0f172a;
            color: #dbeafe;
            padding: 12px;
            font-size: 11px;
            line-height: 1.45;
        }

        .empty-state {
            border-radius: 22px;
            background: rgba(255, 255, 255, .78);
            border: 1px solid rgba(255, 255, 255, .86);
            padding: 20px;
            text-align: center;
            box-shadow: 0 18px 38px rgba(15, 23, 42, .08);
        }

        .empty-state h2 {
            margin: 0 0 6px;
            font-size: 18px;
            font-weight: 950;
        }

        .empty-state p {
            margin: 0;
            color: var(--text-muted);
            font-size: 14px;
            line-height: 1.45;
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

            .summary-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    @php
    $isSmartFountain = $device->isSmartFountain();
    $filter = $filter ?? request('filter', 'all');
    $filterUrl = fn (string $name) => route('devices.history', ['device' => $device, 'filter' => $name]);
    $visibleCount = fn ($value) => is_countable($value) ? count($value) : 0;

    $readingCount = $isSmartFountain ? $visibleCount($platformReadings) : $visibleCount($sensorReadings ?? collect());
    $actionCount = $isSmartFountain
    ? $visibleCount($deviceCommands)
    : ($visibleCount($wateringLogs) + $visibleCount($deviceCommands));

    $statusClass = function (?string $status) {
    $key = strtolower((string) $status);

    return match ($key) {
    'executed', 'completed', 'finished' => 'status-executed',
    'pending', 'waiting', 'requested' => 'status-pending',
    'acknowledged', 'watering', 'running' => 'status-acknowledged',
    'failed', 'expired', 'cancelled' => 'status-failed',
    default => 'status-default',
    };
    };

    $formatTime = fn ($time) => $time?->format('M d, Y · h:i A') ?? 'Time unknown';

    $commandTitle = function ($command) {
    $type = $command->command_type;
    $payload = $command->payload ?? [];

    if ($type === 'scene_apply') {
    return 'Scene applied: ' . (data_get($payload, 'scene_name') ?? 'Scene preset');
    }

    if ($type === 'output_set') {
    $output = str_replace('_', ' ', (string) data_get($payload, 'output', 'output'));
    return 'Output changed: ' . ucwords($output);
    }

    if ($type === 'valve_on') {
    return 'Start watering requested';
    }

    if ($type === 'valve_off') {
    return 'Stop watering requested';
    }

    return ucwords(str_replace('_', ' ', $type));
    };

    $commandDetails = function ($command) {
    $payload = $command->payload ?? [];

    if ($command->command_type === 'scene_apply') {
    $source = data_get($payload, 'schedule_period') ? 'Schedule' : 'Manual scene';
    $outputs = data_get($payload, 'outputs', []);
    $parts = [];

    if (isset($outputs['pump'])) {
    $parts[] = 'Pump ' . (data_get($outputs, 'pump.enabled') ? data_get($outputs, 'pump.speed_percent', 0) . '%' : 'Off');
    }

    if (isset($outputs['cob_light'])) {
    $parts[] = 'COB ' . (data_get($outputs, 'cob_light.enabled') ? data_get($outputs, 'cob_light.brightness_percent', 0) . '%' : 'Off');
    }

    if (isset($outputs['rgb_light'])) {
    $parts[] = 'RGB ' . (data_get($outputs, 'rgb_light.enabled') ? data_get($outputs, 'rgb_light.brightness_percent', 0) . '%' : 'Off');
    }

    return $source . (count($parts) ? ' • ' . implode(' • ', $parts) : '');
    }

    if ($command->command_type === 'valve_on') {
    return 'Duration: ' . data_get($payload, 'duration_seconds', 'N/A') . ' sec';
    }

    if ($command->command_type === 'output_set') {
    $state = data_get($payload, 'state', []);
    $parts = [];

    if (array_key_exists('enabled', $state)) {
    $parts[] = data_get($state, 'enabled') ? 'On' : 'Off';
    }

    if (array_key_exists('speed_percent', $state)) {
    $parts[] = data_get($state, 'speed_percent') . '% speed';
    }

    if (array_key_exists('brightness_percent', $state)) {
    $parts[] = data_get($state, 'brightness_percent') . '% brightness';
    }

    return count($parts) ? implode(' • ', $parts) : 'Output command sent to device.';
    }

    return 'Device command was sent from the platform.';
    };
    @endphp

    <div class="page-shell">
        <a href="{{ route('devices.show', $device) }}" class="back-link">← Back to Device Home</a>

        <nav class="tabs">
            @if ($isSmartFountain)
            <a href="{{ route('devices.show', $device) }}" class="tab">Home</a>
            <a href="{{ route('devices.smart-fountain.scenes.index', $device) }}" class="tab">Scenes</a>
            <a href="{{ route('devices.smart-fountain.schedules.index', $device) }}" class="tab">Schedule</a>
            <a href="{{ route('devices.history', $device) }}" class="tab active">History</a>
            @else
            <a href="{{ route('devices.show', $device) }}" class="tab">Home</a>
            <a href="{{ route('devices.automation', $device) }}" class="tab">Automation</a>
            <a href="{{ route('devices.schedules.index', $device) }}" class="tab">Schedules</a>
            <a href="{{ route('devices.history', $device) }}" class="tab active">History</a>
            @endif
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
                        <p class="eyebrow">{{ $device->displayType() }}</p>
                        <h1 class="title">Recent Activity</h1>
                        <!-- <p class="subtitle">Latest useful readings and actions. Older records stay in the database for debugging and cleanup later.</p> -->
                    </section>

                    <section class="summary-grid">
                        <div class="summary-card">
                            <p class="summary-label">{{ $isSmartFountain ? 'Recent Readings' : 'Recent Sensor Readings' }}</p>
                            <p class="summary-value">{{ $readingCount }} shown</p>
                        </div>
                        <div class="summary-card">
                            <p class="summary-label">{{ $isSmartFountain ? 'Recent Actions' : 'Recent Watering Actions' }}</p>
                            <p class="summary-value">{{ $actionCount }} shown</p>
                        </div>
                    </section>

                    <section class="filter-row" aria-label="History filters">
                        <a href="{{ $filterUrl('all') }}" class="filter-chip {{ $filter === 'all' ? 'active' : '' }}">All</a>
                        <a href="{{ $filterUrl('actions') }}" class="filter-chip {{ $filter === 'actions' ? 'active' : '' }}">Actions</a>
                        <a href="{{ $filterUrl('readings') }}" class="filter-chip {{ $filter === 'readings' ? 'active' : '' }}">Readings</a>
                        <a href="{{ $filterUrl('errors') }}" class="filter-chip {{ $filter === 'errors' ? 'active' : '' }}">Errors</a>
                    </section>

                    @if ($isSmartFountain && in_array($filter, ['all', 'readings', 'errors'], true))
                    <section>
                        <p class="section-label">Device Readings</p>
                        <div class="activity-list">
                            @forelse($platformReadings as $reading)
                            @php
                            $metricLabel = ucwords(str_replace('_', ' ', $reading->metric));
                            $isWaterLow = $reading->metric === 'water_low' && (int) $reading->value === 1;
                            @endphp
                            <article class="activity-card">
                                <div class="activity-icon">{{ $isWaterLow ? '⚠️' : '💧' }}</div>
                                <div>
                                    <div class="activity-title-row">
                                        <h2 class="activity-title">{{ $metricLabel }} updated</h2>
                                        <span class="status-pill {{ $isWaterLow ? 'status-failed' : 'status-executed' }}">{{ $isWaterLow ? 'Alert' : 'Logged' }}</span>
                                    </div>
                                    <p class="activity-time">{{ $formatTime($reading->recorded_at) }}</p>
                                    <p class="activity-details">{{ $metricLabel }}: {{ $reading->value }} {{ $reading->unit }}</p>
                                    <details class="tech-details">
                                        <summary>Technical details</summary>
                                        <pre class="tech-pre">{{ json_encode(['metric' => $reading->metric, 'value' => $reading->value, 'unit' => $reading->unit, 'metadata' => $reading->metadata], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </details>
                                </div>
                            </article>
                            @empty
                            <div class="empty-state">
                                <h2>No readings found</h2>
                                <p>Device readings matching this filter will appear here.</p>
                            </div>
                            @endforelse
                        </div>
                    </section>
                    @endif

                    @if (! $isSmartFountain && in_array($filter, ['all', 'readings'], true))
                    <section>
                        <p class="section-label">Sensor Readings</p>
                        <div class="activity-list">
                            @forelse($sensorReadings as $reading)
                            @php
                            $soil = is_null($reading->soil_moisture) ? 'N/A' : $reading->soil_moisture . '%';
                            $temperature = is_null($reading->temperature) ? 'N/A' : $reading->temperature . '°C';
                            $humidity = is_null($reading->humidity) ? 'N/A' : $reading->humidity . '%';
                            $soilStatus = is_null($reading->soil_moisture) ? 'No reading' : ((int) $reading->soil_moisture < 35 ? 'Dry' : ((int) $reading->soil_moisture > 85 ? 'Wet' : 'Optimal'));
                                @endphp
                                <article class="activity-card">
                                    <div class="activity-icon">🌱</div>
                                    <div>
                                        <div class="activity-title-row">
                                            <h2 class="activity-title">Soil sensor updated</h2>
                                            <span class="status-pill status-executed">{{ $soilStatus }}</span>
                                        </div>
                                        <p class="activity-time">{{ $formatTime($reading->recorded_at ?? $reading->created_at) }}</p>
                                        <p class="activity-details">Soil: {{ $soil }} • Temp: {{ $temperature }} • Humidity: {{ $humidity }}</p>
                                        <details class="tech-details">
                                            <summary>Technical details</summary>
                                            <pre class="tech-pre">{{ json_encode(['soil_moisture' => $reading->soil_moisture, 'temperature' => $reading->temperature, 'humidity' => $reading->humidity, 'recorded_at' => $reading->recorded_at?->format('Y-m-d H:i:s'), 'created_at' => $reading->created_at?->format('Y-m-d H:i:s')], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                        </details>
                                    </div>
                                </article>
                                @empty
                                <div class="empty-state">
                                    <h2>No sensor readings found</h2>
                                    <p>Soil moisture, temperature, and humidity readings will appear here.</p>
                                </div>
                                @endforelse
                        </div>
                    </section>
                    @endif

                    @if (! $isSmartFountain && in_array($filter, ['all', 'actions', 'errors'], true))
                    <section>
                        <p class="section-label">Watering Activity</p>
                        <div class="activity-list">
                            @forelse($wateringLogs as $log)
                            @php
                            $trigger = ucfirst(str_replace('_', ' ', $log->trigger_type));
                            $status = strtolower((string) $log->status);
                            $isError = in_array($status, ['failed', 'expired', 'cancelled'], true);
                            $title = match ($status) {
                            'completed', 'finished' => $trigger . ' watering completed',
                            'watering', 'running' => $trigger . ' watering running',
                            'failed' => $trigger . ' watering failed',
                            default => $trigger . ' watering ' . ($log->status ? strtolower($log->status) : 'logged'),
                            };
                            @endphp
                            <article class="activity-card">
                                <div class="activity-icon">{{ $isError ? '⚠️' : '💧' }}</div>
                                <div>
                                    <div class="activity-title-row">
                                        <h2 class="activity-title">{{ $title }}</h2>
                                        <span class="status-pill {{ $statusClass($log->status) }}">{{ ucfirst($log->status ?? 'Logged') }}</span>
                                    </div>
                                    <p class="activity-time">{{ $formatTime($log->started_at ?? $log->created_at) }}</p>
                                    <p class="activity-details">Duration: {{ $log->duration_seconds }} sec @if ($log->ended_at) • Ended {{ $log->ended_at->format('h:i A') }} @endif @if ($log->notes) • {{ $log->notes }} @endif</p>
                                    <details class="tech-details">
                                        <summary>Technical details</summary>
                                        <pre class="tech-pre">{{ json_encode(['trigger_type' => $log->trigger_type, 'duration_seconds' => $log->duration_seconds, 'status' => $log->status, 'started_at' => $log->started_at?->format('Y-m-d H:i:s'), 'ended_at' => $log->ended_at?->format('Y-m-d H:i:s'), 'created_at' => $log->created_at?->format('Y-m-d H:i:s'), 'notes' => $log->notes], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </details>
                                </div>
                            </article>
                            @empty
                            <div class="empty-state">
                                <h2>No watering activity found</h2>
                                <p>Watering logs matching this filter will appear here.</p>
                            </div>
                            @endforelse
                        </div>
                    </section>
                    @endif

                    @if (in_array($filter, ['all', 'actions', 'errors'], true))
                    <section>
                        <p class="section-label">Device Actions</p>
                        <div class="activity-list">
                            @forelse($deviceCommands as $command)
                            @php
                            $status = strtolower((string) $command->status);
                            $isError = in_array($status, ['failed', 'expired', 'cancelled'], true);
                            @endphp
                            <article class="activity-card">
                                <div class="activity-icon">{{ $isError ? '⚠️' : ($command->command_type === 'scene_apply' ? '🎬' : '⚙️') }}</div>
                                <div>
                                    <div class="activity-title-row">
                                        <h2 class="activity-title">{{ $commandTitle($command) }}</h2>
                                        <span class="status-pill {{ $statusClass($command->status) }}">{{ ucfirst($command->status ?? 'Pending') }}</span>
                                    </div>
                                    <p class="activity-time">{{ $formatTime($command->issued_at ?? $command->created_at) }}</p>
                                    <p class="activity-details">{{ $commandDetails($command) }}</p>
                                    <details class="tech-details">
                                        <summary>Technical details</summary>
                                        <pre class="tech-pre">{{ json_encode(['command_type' => $command->command_type, 'status' => $command->status, 'payload' => $command->payload, 'issued_at' => $command->issued_at?->format('Y-m-d H:i:s'), 'acknowledged_at' => $command->acknowledged_at?->format('Y-m-d H:i:s'), 'executed_at' => $command->executed_at?->format('Y-m-d H:i:s')], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </details>
                                </div>
                            </article>
                            @empty
                            <div class="empty-state">
                                <h2>No device actions found</h2>
                                <p>Commands matching this filter will appear here.</p>
                            </div>
                            @endforelse
                        </div>
                    </section>
                    @endif
                </div>
            </div>
        </main>
    </div>
</body>

</html>