<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $device->name }} - History</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="max-w-5xl mx-auto py-6 px-4">
        <div class="mb-4">
            <a href="{{ route('devices.show', $device) }}" class="text-blue-600 hover:underline">← Back to Device Home</a>
        </div>

        <div class="mb-4 flex flex-wrap gap-2">
            <a href="{{ route('devices.show', $device) }}" class="rounded bg-white px-3 py-2 text-sm border">Home</a>
            <a href="{{ route('devices.automation', $device) }}" class="rounded bg-white px-3 py-2 text-sm border">Automation</a>
            <a href="{{ route('devices.schedules.index', $device) }}" class="rounded bg-white px-3 py-2 text-sm border">Schedules</a>
            <a href="{{ route('devices.history', $device) }}" class="rounded bg-blue-600 px-3 py-2 text-sm text-white">History</a>
        </div>

        <h1 class="mb-6 text-2xl font-bold">History</h1>

        <div class="grid gap-6 md:grid-cols-2">
            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-4 text-xl font-semibold">Recent Watering Logs</h2>

                @forelse($device->wateringLogs as $log)
                <div class="border-b py-3 last:border-b-0">
                    <p><strong>Trigger:</strong> {{ ucfirst($log->trigger_type) }}</p>
                    <p><strong>Duration:</strong> {{ $log->duration_seconds }} sec</p>
                    <p><strong>Status:</strong> {{ ucfirst($log->status) }}</p>
                    <p><strong>Started At:</strong> {{ $log->started_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</p>
                    <p><strong>Ended At:</strong> {{ $log->ended_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</p>
                    <p><strong>Requested At:</strong> {{ $log->created_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</p>
                    <p><strong>Notes:</strong> {{ $log->notes ?? 'N/A' }}</p>
                </div>
                @empty
                <p class="text-gray-500">No watering logs found.</p>
                @endforelse
            </div>

            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-4 text-xl font-semibold">Recent Device Commands</h2>

                @forelse($device->deviceCommands as $command)
                <div class="border-b py-3 last:border-b-0">
                    <p><strong>Command Type:</strong> {{ $command->command_type }}</p>
                    <p><strong>Status:</strong> {{ ucfirst($command->status) }}</p>
                    <p><strong>Issued At:</strong> {{ $command->issued_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</p>
                    <p><strong>Acknowledged At:</strong> {{ $command->acknowledged_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</p>
                    <p><strong>Executed At:</strong> {{ $command->executed_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</p>
                </div>
                @empty
                <p class="text-gray-500">No device commands found.</p>
                @endforelse
            </div>
        </div>
    </div>
</body>

</html>