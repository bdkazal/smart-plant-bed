<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $device->name }} - Scenes</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="max-w-6xl mx-auto py-6 px-4">
        <div class="mb-4">
            <a href="{{ route('devices.index') }}" class="text-blue-600 hover:underline">← Back to Devices</a>
        </div>

        <div class="mb-4 flex flex-wrap gap-2">
            <a href="{{ route('devices.show', $device) }}" class="rounded bg-white px-3 py-2 text-sm border hover:bg-gray-50">Home</a>
            <a href="{{ route('devices.smart-fountain.scenes.index', $device) }}" class="rounded bg-blue-600 px-3 py-2 text-sm text-white">Scenes</a>
            <a href="{{ route('devices.smart-fountain.schedules.index', $device) }}" class="rounded bg-white px-3 py-2 text-sm border hover:bg-gray-50">Schedule</a>
            <a href="{{ route('devices.history', $device) }}" class="rounded bg-white px-3 py-2 text-sm border hover:bg-gray-50">History</a>
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
                <h1 class="text-2xl font-bold">Scenes</h1>
                <p class="text-gray-600">Saved Smart Fountain presets for pump, COB light, and RGB light.</p>
            </div>

            <a href="{{ route('devices.smart-fountain.scenes.create', $device) }}" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                + Create Scene
            </a>
        </div>

        @if ($device->scenes->isEmpty())
            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-2 text-lg font-semibold">No scenes yet</h2>
                <p class="mb-4 text-gray-600">Create a scene such as Day Fountain, Night Glow, Display Mode, or All Off.</p>
                <a href="{{ route('devices.smart-fountain.scenes.create', $device) }}" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    Create First Scene
                </a>
            </div>
        @else
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($device->scenes as $scene)
                    @php
                        $pump = data_get($scene->outputs, 'pump', []);
                        $cob = data_get($scene->outputs, 'cob_light', []);
                        $rgb = data_get($scene->outputs, 'rgb_light', []);
                    @endphp

                    <div class="rounded-lg bg-white p-5 shadow">
                        <div class="mb-4 flex items-start justify-between gap-3">
                            <div>
                                <h2 class="text-lg font-semibold">{{ $scene->name }}</h2>
                                <p class="text-sm text-gray-500">Scene preset</p>
                            </div>
                        </div>

                        <div class="mb-4 space-y-2 rounded border border-gray-200 bg-gray-50 px-4 py-3 text-sm">
                            <p><strong>Pump:</strong> {{ data_get($pump, 'enabled') ? 'ON' : 'OFF' }} {{ data_get($pump, 'speed_percent', 0) }}%</p>
                            <p><strong>COB:</strong> {{ data_get($cob, 'enabled') ? 'ON' : 'OFF' }} {{ data_get($cob, 'brightness_percent', 0) }}%</p>
                            <p><strong>RGB:</strong> {{ data_get($rgb, 'enabled') ? 'ON' : 'OFF' }} {{ data_get($rgb, 'brightness_percent', 0) }}%</p>
                            <p><strong>RGB Color:</strong> {{ data_get($rgb, 'color', 'N/A') }}</p>
                            <p><strong>RGB Effect:</strong> {{ ucwords(str_replace('_', ' ', data_get($rgb, 'effect', 'N/A'))) }}</p>
                        </div>

                        <form method="POST" action="{{ route('devices.smart-fountain.scenes.apply', [$device, $scene]) }}" class="mb-3">
                            @csrf
                            <button type="submit" class="w-full rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                                Apply Scene
                            </button>
                        </form>

                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('devices.smart-fountain.scenes.edit', [$device, $scene]) }}" class="rounded bg-white px-3 py-2 text-sm border hover:bg-gray-50">
                                Edit
                            </a>

                            <form method="POST" action="{{ route('devices.smart-fountain.scenes.destroy', [$device, $scene]) }}">
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
