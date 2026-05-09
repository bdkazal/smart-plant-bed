<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $scene ? 'Edit Scene' : 'Create Scene' }} - {{ $device->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="max-w-4xl mx-auto py-6 px-4">
        <div class="mb-4">
            <a href="{{ route('devices.smart-fountain.scenes.index', $device) }}" class="text-blue-600 hover:underline">← Back to Scenes</a>
        </div>

        <div class="mb-4 flex flex-wrap gap-2">
            <a href="{{ route('devices.show', $device) }}" class="rounded bg-white px-3 py-2 text-sm border">Home</a>
            <a href="{{ route('devices.smart-fountain.scenes.index', $device) }}" class="rounded bg-blue-600 px-3 py-2 text-sm text-white">Scenes</a>
            <a href="{{ route('devices.history', $device) }}" class="rounded bg-white px-3 py-2 text-sm border">History</a>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded bg-red-100 px-4 py-3 text-red-800">
                <ul class="list-disc ml-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-6">
            <h1 class="text-2xl font-bold">{{ $scene ? 'Edit Scene' : 'Create Scene' }}</h1>
            <p class="text-gray-600">Save a reusable Smart Fountain preset.</p>
        </div>

        <form method="POST" action="{{ $scene ? route('devices.smart-fountain.scenes.update', [$device, $scene]) : route('devices.smart-fountain.scenes.store', $device) }}" class="space-y-4">
            @csrf
            @if ($scene)
                @method('PUT')
            @endif

            <div class="rounded-lg bg-white p-5 shadow">
                <label for="name" class="mb-1 block text-sm font-medium">Scene Name</label>
                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name', $scene?->name) }}"
                    class="w-full rounded border px-3 py-2"
                    placeholder="Day Fountain"
                    required>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-lg bg-white p-5 shadow">
                    <h2 class="mb-3 text-lg font-semibold">Pump</h2>

                    <label class="mb-3 flex items-center gap-2">
                        <input type="checkbox" name="pump_enabled" value="1" @checked(old('pump_enabled', data_get($outputs, 'pump.enabled')))> 
                        <span>Enable pump</span>
                    </label>

                    <label for="pump_speed_percent" class="mb-1 block text-sm font-medium">Speed (%)</label>
                    <input
                        id="pump_speed_percent"
                        type="number"
                        name="pump_speed_percent"
                        min="0"
                        max="100"
                        value="{{ old('pump_speed_percent', data_get($outputs, 'pump.speed_percent', 0)) }}"
                        class="w-full rounded border px-3 py-2"
                        required>
                </div>

                <div class="rounded-lg bg-white p-5 shadow">
                    <h2 class="mb-3 text-lg font-semibold">COB Light</h2>

                    <label class="mb-3 flex items-center gap-2">
                        <input type="checkbox" name="cob_light_enabled" value="1" @checked(old('cob_light_enabled', data_get($outputs, 'cob_light.enabled')))> 
                        <span>Enable COB light</span>
                    </label>

                    <label for="cob_brightness_percent" class="mb-1 block text-sm font-medium">Brightness (%)</label>
                    <input
                        id="cob_brightness_percent"
                        type="number"
                        name="cob_brightness_percent"
                        min="0"
                        max="100"
                        value="{{ old('cob_brightness_percent', data_get($outputs, 'cob_light.brightness_percent', 0)) }}"
                        class="w-full rounded border px-3 py-2"
                        required>
                </div>

                <div class="rounded-lg bg-white p-5 shadow">
                    <h2 class="mb-3 text-lg font-semibold">RGB Light</h2>

                    <label class="mb-3 flex items-center gap-2">
                        <input type="checkbox" name="rgb_light_enabled" value="1" @checked(old('rgb_light_enabled', data_get($outputs, 'rgb_light.enabled')))> 
                        <span>Enable RGB light</span>
                    </label>

                    <label for="rgb_brightness_percent" class="mb-1 block text-sm font-medium">Brightness (%)</label>
                    <input
                        id="rgb_brightness_percent"
                        type="number"
                        name="rgb_brightness_percent"
                        min="0"
                        max="100"
                        value="{{ old('rgb_brightness_percent', data_get($outputs, 'rgb_light.brightness_percent', 0)) }}"
                        class="mb-3 w-full rounded border px-3 py-2"
                        required>

                    <label for="rgb_color" class="mb-1 block text-sm font-medium">Color</label>
                    <input
                        id="rgb_color"
                        type="color"
                        name="rgb_color"
                        value="{{ old('rgb_color', data_get($outputs, 'rgb_light.color', '#FFB066')) }}"
                        class="mb-3 h-10 w-full rounded border px-2 py-1"
                        required>

                    <label for="rgb_effect" class="mb-1 block text-sm font-medium">Effect</label>
                    <select id="rgb_effect" name="rgb_effect" class="w-full rounded border px-3 py-2" required>
                        @foreach (['solid', 'breathing', 'slow_rainbow', 'warm_glow', 'water_shimmer', 'night_mode'] as $effect)
                            <option value="{{ $effect }}" @selected(old('rgb_effect', data_get($outputs, 'rgb_light.effect', 'warm_glow')) === $effect)>
                                {{ ucwords(str_replace('_', ' ', $effect)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    {{ $scene ? 'Update Scene' : 'Save Scene' }}
                </button>

                <a href="{{ route('devices.smart-fountain.scenes.index', $device) }}" class="rounded bg-white px-4 py-2 border hover:bg-gray-50">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</body>

</html>
