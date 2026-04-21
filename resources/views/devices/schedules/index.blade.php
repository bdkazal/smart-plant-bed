<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $device->name }} - Schedules</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="max-w-4xl mx-auto py-6 px-4">
        <div class="mb-4">
            <a href="{{ route('devices.show', $device) }}" class="text-blue-600 hover:underline">← Back to Device Home</a>
        </div>

        <div class="mb-4 flex flex-wrap gap-2">
            <a href="{{ route('devices.show', $device) }}" class="rounded bg-white px-3 py-2 text-sm border">Home</a>
            <a href="{{ route('devices.automation', $device) }}" class="rounded bg-white px-3 py-2 text-sm border">Automation</a>
            <a href="{{ route('devices.schedules.index', $device) }}" class="rounded bg-blue-600 px-3 py-2 text-sm text-white">Schedules</a>
            <a href="{{ route('devices.history', $device) }}" class="rounded bg-white px-3 py-2 text-sm border">History</a>
        </div>

        @if (session('success'))
        <div class="mb-4 rounded bg-green-100 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
        @endif

        <div class="rounded-lg bg-white p-6 shadow">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Watering Schedules</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        All schedule times use device timezone: <strong>{{ $device->timezone ?? 'Asia/Dhaka' }}</strong>
                    </p>
                </div>

                <a
                    href="{{ route('devices.schedules.create', $device) }}"
                    class="rounded bg-blue-600 px-3 py-2 text-sm text-white hover:bg-blue-700">
                    Add Schedule
                </a>
            </div>

            @php
            $days = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday',
            ];
            @endphp

            @forelse($device->wateringSchedules as $schedule)
            <div class="border-b py-3 last:border-b-0">
                <p><strong>Status:</strong> {{ $schedule->is_enabled ? 'Enabled' : 'Disabled' }}</p>
                <p><strong>Day:</strong> {{ $days[$schedule->day_of_week] ?? 'Unknown' }}</p>
                <p><strong>Time:</strong> {{ $schedule->time_of_day }}</p>
                <p><strong>Duration:</strong> {{ $schedule->duration_seconds }} sec</p>

                <div class="mt-3 flex flex-wrap gap-2">
                    <a
                        href="{{ route('devices.schedules.edit', [$device, $schedule]) }}"
                        class="rounded bg-blue-600 px-3 py-2 text-sm text-white hover:bg-blue-700">
                        Edit
                    </a>

                    <form action="{{ route('devices.schedules.toggle', [$device, $schedule]) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button
                            type="submit"
                            class="rounded bg-yellow-600 px-3 py-2 text-sm text-white hover:bg-yellow-700">
                            {{ $schedule->is_enabled ? 'Disable' : 'Enable' }}
                        </button>
                    </form>

                    <form
                        action="{{ route('devices.schedules.destroy', [$device, $schedule]) }}"
                        method="POST"
                        onsubmit="return confirm('Are you sure you want to delete this schedule?');">
                        @csrf
                        @method('DELETE')
                        <button
                            type="submit"
                            class="rounded bg-red-600 px-3 py-2 text-sm text-white hover:bg-red-700">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div>
                <p class="text-gray-500">No watering schedules found.</p>
                <a
                    href="{{ route('devices.schedules.create', $device) }}"
                    class="mt-3 inline-flex rounded bg-blue-600 px-3 py-2 text-sm text-white hover:bg-blue-700">
                    Create First Schedule
                </a>
            </div>
            @endforelse
        </div>
    </div>
</body>

</html>