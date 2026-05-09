<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $device->name }} - Daily Timeline</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100">
    <div class="mx-auto max-w-6xl px-4 py-6">
        <div class="mb-4">
            <a href="{{ route('devices.index') }}" class="text-blue-600 hover:underline">← Back to Devices</a>
        </div>

        <div class="mb-5 flex flex-wrap gap-2">
            <a href="{{ route('devices.show', $device) }}" class="rounded-lg border bg-white px-4 py-2 text-sm hover:bg-slate-50">Home</a>
            <a href="{{ route('devices.smart-fountain.scenes.index', $device) }}" class="rounded-lg border bg-white px-4 py-2 text-sm hover:bg-slate-50">Scenes</a>
            <a href="{{ route('devices.smart-fountain.schedules.index', $device) }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white shadow-sm">Schedule</a>
            <a href="{{ route('devices.history', $device) }}" class="rounded-lg border bg-white px-4 py-2 text-sm hover:bg-slate-50">History</a>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-lg bg-green-100 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-100 px-4 py-3 text-red-800">
                <ul class="ml-5 list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-6 rounded-2xl bg-gradient-to-r from-blue-600 to-cyan-500 p-6 text-white shadow">
            <p class="mb-1 text-sm font-semibold uppercase tracking-wide text-blue-100">Smart Fountain Schedule</p>
            <h1 class="text-3xl font-bold">Daily Timeline</h1>
            <p class="mt-2 max-w-2xl text-blue-50">Choose which scene runs during Day, Evening, and Night. The three blocks cover the full 24 hours without gaps.</p>
        </div>

        <div class="mb-5 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-blue-800">
            The timeline is continuous: Day ends when Evening starts, Evening ends when Night starts, and Night ends when Day starts.
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            @foreach ($schedules as $schedule)
                @php
                    $periodIcon = match ($schedule->period_key) {
                        'day' => '☀️',
                        'evening' => '🌆',
                        'night' => '🌙',
                        default => '⏱️',
                    };

                    $periodColor = match ($schedule->period_key) {
                        'day' => 'from-amber-100 to-yellow-50 border-amber-200',
                        'evening' => 'from-orange-100 to-pink-50 border-orange-200',
                        'night' => 'from-indigo-100 to-slate-50 border-indigo-200',
                        default => 'from-slate-100 to-white border-slate-200',
                    };
                @endphp

                <div class="overflow-hidden rounded-2xl bg-white shadow">
                    <div class="border-b bg-gradient-to-br {{ $periodColor }} p-5">
                        <div class="mb-4 flex items-start justify-between gap-3">
                            <div>
                                <div class="mb-2 text-3xl">{{ $periodIcon }}</div>
                                <h2 class="text-xl font-bold text-slate-900">{{ $schedule->name }}</h2>
                                <p class="text-sm text-slate-600">{{ ucfirst($schedule->period_key) }} timeline block</p>
                            </div>

                            <span class="rounded-full px-3 py-1 text-sm font-medium {{ $schedule->is_enabled ? 'bg-green-100 text-green-800' : 'bg-slate-200 text-slate-700' }}">
                                {{ $schedule->is_enabled ? 'On' : 'Off' }}
                            </span>
                        </div>

                        <div class="text-2xl font-bold text-slate-900">
                            {{ substr($schedule->start_time, 0, 5) }} → {{ substr($schedule->end_time, 0, 5) }}
                        </div>
                    </div>

                    <div class="space-y-4 p-5">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                            <p class="mb-2"><strong>Scene:</strong> {{ $schedule->startScene?->name ?? 'Missing scene' }}</p>
                            <p class="mb-2"><strong>Days:</strong> {{ collect($schedule->days_of_week)->map(fn ($day) => $dayNames[$day] ?? $day)->join(', ') }}</p>
                            <p><strong>Last Applied:</strong> {{ $schedule->last_started_at?->format('Y-m-d H:i:s') ?? 'Never' }}</p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('devices.smart-fountain.schedules.edit', [$device, $schedule]) }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                                Edit Block
                            </a>

                            <form method="POST" action="{{ route('devices.smart-fountain.schedules.toggle', [$device, $schedule]) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="rounded-lg border bg-white px-4 py-2 text-sm hover:bg-slate-50">
                                    {{ $schedule->is_enabled ? 'Disable' : 'Enable' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</body>

</html>
