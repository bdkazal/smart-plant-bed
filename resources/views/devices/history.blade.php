<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $device->name }} - History</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="max-w-6xl mx-auto py-6 px-4">
        <div class="mb-4">
            <a href="{{ route('devices.show', $device) }}" class="text-blue-600 hover:underline">← Back to Device Home</a>
        </div>

        <div class="mb-4 flex flex-wrap gap-2">
            <a href="{{ route('devices.show', $device) }}" class="rounded bg-white px-3 py-2 text-sm border">Home</a>
            <a href="{{ route('devices.automation', $device) }}" class="rounded bg-white px-3 py-2 text-sm border">Automation</a>
            <a href="{{ route('devices.schedules.index', $device) }}" class="rounded bg-white px-3 py-2 text-sm border">Schedules</a>
            <a href="{{ route('devices.history', $device) }}" class="rounded bg-blue-600 px-3 py-2 text-sm text-white">History</a>
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

        <h1 class="mb-6 text-2xl font-bold">History</h1>

        <div class="grid gap-6 md:grid-cols-2">
            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-4 text-xl font-semibold">Watering Logs</h2>

                @forelse($wateringLogs as $log)
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

                <div class="mt-4">
                    {{ $wateringLogs->links() }}
                </div>
            </div>

            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-4 text-xl font-semibold">Device Commands</h2>

                @forelse($deviceCommands as $command)
                <div class="border-b py-3 last:border-b-0">
                    <p><strong>Command Type:</strong> {{ $command->command_type }}</p>
                    <p><strong>Status:</strong> {{ ucfirst($command->status) }}</p>
                    <p><strong>Payload:</strong></p>
                    <pre class="overflow-x-auto rounded bg-gray-100 p-3 text-sm">{{ json_encode($command->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    <p><strong>Issued At:</strong> {{ $command->issued_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</p>
                    <p><strong>Acknowledged At:</strong> {{ $command->acknowledged_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</p>
                    <p><strong>Executed At:</strong> {{ $command->executed_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</p>
                </div>
                @empty
                <p class="text-gray-500">No device commands found.</p>
                @endforelse

                <div class="mt-4">
                    {{ $deviceCommands->links() }}
                </div>
            </div>
        </div>
    </div>
</body>

</html>