<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $device->name }} - Smart Fountain</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="max-w-6xl mx-auto py-6 px-4">
        <div class="mb-4">
            <a href="{{ route('devices.index') }}" class="text-blue-600 hover:underline">← Back to Devices</a>
        </div>

        <div class="mb-6 flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">{{ $device->name }}</h1>
                <p class="text-gray-600">{{ $device->displayType() }}</p>
            </div>

            <div class="rounded-full px-3 py-1 text-sm {{ $isOnline ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-700' }}">
                {{ $isOnline ? 'Online' : 'Offline' }}
            </div>
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

        @php
            $pump = $outputs->get('pump');
            $cobLight = $outputs->get('cob_light');
            $rgbLight = $outputs->get('rgb_light');

            $waterLow = $latestReadings->get('water_low')?->value;
            $waterLevel = $latestReadings->get('water_level_percent')?->value;
        @endphp

        <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-lg bg-white p-5 shadow">
                <h2 class="mb-3 text-lg font-semibold">Device Status</h2>
                <p><strong>Status:</strong> {{ $isOnline ? 'Online' : 'Offline' }}</p>
                <p><strong>Type:</strong> {{ $device->displayType() }}</p>
                <p><strong>Location:</strong> {{ $device->location_label ?? 'N/A' }}</p>
                <p><strong>Timezone:</strong> {{ $device->timezone ?? 'Asia/Dhaka' }}</p>
                <p><strong>Last Seen:</strong> {{ $device->last_seen_at?->diffForHumans() ?? 'Never' }}</p>
            </div>

            <div class="rounded-lg bg-white p-5 shadow">
                <h2 class="mb-3 text-lg font-semibold">Water Safety</h2>

                <p>
                    <strong>Water Low:</strong>
                    @if (is_null($waterLow))
                        N/A
                    @elseif ((int) $waterLow === 1)
                        <span class="font-semibold text-red-600">Yes</span>
                    @else
                        <span class="font-semibold text-green-700">No</span>
                    @endif
                </p>

                <p>
                    <strong>Water Level:</strong>
                    {{ is_null($waterLevel) ? 'N/A' : number_format($waterLevel, 0) . '%' }}
                </p>

                @if ((int) $waterLow === 1)
                    <div class="mt-4 rounded border border-red-300 bg-red-50 px-4 py-3 text-red-700">
                        Low water detected. Pump should stay OFF to protect the motor.
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-3">
            <div class="rounded-lg bg-white p-5 shadow">
                <h2 class="mb-3 text-lg font-semibold">Pump</h2>
                <p><strong>State:</strong> {{ data_get($pump?->state, 'enabled') ? 'ON' : 'OFF' }}</p>
                <p><strong>Speed:</strong> {{ data_get($pump?->state, 'speed_percent', 0) }}%</p>
                <p><strong>Source:</strong> {{ $pump?->last_changed_source ?? 'N/A' }}</p>
            </div>

            <div class="rounded-lg bg-white p-5 shadow">
                <h2 class="mb-3 text-lg font-semibold">COB Light</h2>
                <p><strong>State:</strong> {{ data_get($cobLight?->state, 'enabled') ? 'ON' : 'OFF' }}</p>
                <p><strong>Brightness:</strong> {{ data_get($cobLight?->state, 'brightness_percent', 0) }}%</p>
                <p><strong>Source:</strong> {{ $cobLight?->last_changed_source ?? 'N/A' }}</p>
            </div>

            <div class="rounded-lg bg-white p-5 shadow">
                <h2 class="mb-3 text-lg font-semibold">RGB Light</h2>
                <p><strong>State:</strong> {{ data_get($rgbLight?->state, 'enabled') ? 'ON' : 'OFF' }}</p>
                <p><strong>Brightness:</strong> {{ data_get($rgbLight?->state, 'brightness_percent', 0) }}%</p>
                <p><strong>Color:</strong> {{ data_get($rgbLight?->state, 'color', 'N/A') }}</p>
                <p><strong>Effect:</strong> {{ str_replace('_', ' ', data_get($rgbLight?->state, 'effect', 'N/A')) }}</p>
                <p><strong>Source:</strong> {{ $rgbLight?->last_changed_source ?? 'N/A' }}</p>
            </div>
        </div>

        <div class="mt-4 rounded-lg bg-white p-5 shadow">
            <h2 class="mb-3 text-lg font-semibold">Smart Fountain Controls</h2>

            @if (! $isOnline)
                <div class="rounded border border-yellow-300 bg-yellow-50 px-4 py-3 text-yellow-800">
                    Device is offline. Live pump/light control is unavailable right now.
                </div>
            @else
                <p class="text-gray-700">
                    Controls will be added next: pump speed, COB brightness, RGB color, and RGB effect.
                </p>
            @endif
        </div>
    </div>
</body>

</html>
