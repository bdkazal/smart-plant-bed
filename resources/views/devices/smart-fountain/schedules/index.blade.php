<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $device->name }} - Schedules</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="max-w-6xl mx-auto py-6 px-4">
        <div class="mb-4">
            <a href="{{ route('devices.index') }}" class="text-blue-600 hover:underline">← Back to Devices</a>
        </div>

        <div class="mb-4 flex flex-wrap gap-2">
            <a href="{{ route('devices.show', $device) }}" class="rounded bg-white px-3 py-2 text-sm border">Home</a>
            <a href="{{ route('devices.smart-fountain.scenes.index', $device) }}" class="rounded bg-white px-3 py-2 text-sm border">Scenes</a>
            <a href="{{ route('devices.smart-fountain.schedules.index', $device) }}" class="rounded bg-blue-600 px-3 py-2 text-sm text-white">Schedules</a>
            <a href="{{ route('devices.history', $device) }}" class="rounded bg-white px-3 py-2 text-sm border">History</a>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded bg-green-100 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded bg-red-100 px-4 py-3 text-red-800">
                <ul class="list-disc ml-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-6 flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Schedule Ranges</h1>
                <p class="text-gray-600">Apply Smart Fountain scenes automatically by time range.</p>
            </div>

            <a href="{{ route('devices.smart-fountain.schedules.create', $device) }}" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                + Create Schedule
            </a>
        </div>

        @if ($schedules->isEmpty())
            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-2 text-lg font-semibold">No schedule ranges yet</h2>
                <p class="mb-4 text-gray-600">Create a range such as 06:00 Day Fountain → 20:00 Night Glow.</p>
                <a href="{{ route('devices.smart-fountain.schedules.create', $device) }}" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    Create First Schedule
                </a>
            </div>
        @else
            <div class="grid gap-4 md:grid-cols-2">
                @foreach ($schedules as $schedule)
                    <div class="rounded-lg bg-white p-5 shadow">
                        <div class="mb-4 flex items-start justify-between gap-3">
                            <div>
                                <h2 class="text-lg font-semibold">{{ $schedule->name }}</h2>
                                <p class="text-sm text-gray-500">{{ $schedule->is_enabled ? 'Enabled' : 'Disabled' }}</p>
                            </div>
                            <span class="rounded-full px-3 py-1 text-sm {{ $schedule->is_enabled ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-700' }}">
                                {{ $schedule->is_enabled ? 'On' : 'Off' }}
                            </span>
                        </div>

                        <div class="mb-4 space-y-2 rounded border border-gray-200 bg-gray-50 px-4 py-3 text-sm">
                            <p><strong>Days:</strong>
                                {{ collect($schedule->days_of_week)->map(fn ($day) => $dayNames[$day] ?? $day)->join(', ') }}
                            </p>
                            <p><strong>Start:</strong> {{ substr($schedule->start_time, 0, 5) }} → {{ $schedule->startScene?->name ?? 'Missing scene' }}</p>
                            <p><strong>End:</strong> {{ substr($schedule->end_time, 0, 5) }} → {{ $schedule->endScene?->name ?? 'Missing scene' }}</p>
                            <p><strong>Last Started:</strong> {{ $schedule->last_started_at?->format('Y-m-d H:i:s') ?? 'Never' }}</p>
                            <p><strong>Last Ended:</strong> {{ $schedule->last_ended_at?->format('Y-m-d H:i:s') ?? 'Never' }}</p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('devices.smart-fountain.schedules.edit', [$device, $schedule]) }}" class="rounded bg-white px-3 py-2 text-sm border hover:bg-gray-50">
                                Edit
                            </a>

                            <form method="POST" action="{{ route('devices.smart-fountain.schedules.toggle', [$device, $schedule]) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="rounded bg-white px-3 py-2 text-sm border hover:bg-gray-50">
                                    {{ $schedule->is_enabled ? 'Disable' : 'Enable' }}
                                </button>
                            </form>

                            <form method="POST" action="{{ route('devices.smart-fountain.schedules.destroy', [$device, $schedule]) }}" onsubmit="return confirm('Delete this schedule range?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded bg-red-600 px-3 py-2 text-sm text-white hover:bg-red-700">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</body>

</html>
