<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Devices - Smart Plant Bed</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="max-w-5xl mx-auto py-8 px-4">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold">My Devices</h1>
                <p class="mt-1 text-gray-600">Manage your connected smart devices.</p>
            </div>

            <div class="flex gap-2">
                <a
                    href="{{ route('devices.add') }}"
                    class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    Add Device
                </a>

                <form method="POST" action="/logout">
                    @csrf
                    <button
                        type="submit"
                        class="rounded bg-gray-700 px-4 py-2 text-white hover:bg-gray-800">
                        Logout
                    </button>
                </form>
            </div>
        </div>

        @if (session('success'))
        <div class="mb-4 rounded bg-green-100 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
        @endif

        @if ($devices->isEmpty())
        <div class="rounded-lg bg-white p-6 shadow">
            <h2 class="text-xl font-semibold">No devices yet</h2>
            <p class="mt-2 text-gray-600">
                You have not added any devices yet. Start by claiming a device.
            </p>

            <a
                href="{{ route('devices.add') }}"
                class="mt-4 inline-flex rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                Add Your First Device
            </a>
        </div>
        @else
        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($devices as $device)
            <a
                href="{{ route('devices.show', $device) }}"
                class="block rounded-lg bg-white p-5 shadow hover:bg-gray-50">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold">{{ $device->name }}</h2>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ $device->displayType() }}
                        </p>
                    </div>

                    <span class="rounded bg-gray-100 px-3 py-1 text-sm text-gray-700">
                        {{ ucfirst(str_replace('_', ' ', $device->status)) }}
                    </span>
                </div>

                <div class="mt-4 space-y-1 text-sm text-gray-700">
                    <p><strong>Location:</strong> {{ $device->location_label ?? 'N/A' }}</p>
                    <p><strong>Timezone:</strong> {{ $device->timezone ?? 'Asia/Dhaka' }}</p>
                    <p><strong>Last Seen:</strong> {{ $device->last_seen_at?->diffForHumans() ?? 'Never' }}</p>
                </div>
            </a>
            @endforeach
        </div>
        @endif
    </div>
</body>

</html>