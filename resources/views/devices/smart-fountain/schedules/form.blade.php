<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit {{ $schedule->name }} Timeline - {{ $device->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="max-w-4xl mx-auto py-6 px-4">
        <div class="mb-4">
            <a href="{{ route('devices.smart-fountain.schedules.index', $device) }}" class="text-blue-600 hover:underline">← Back to Timeline</a>
        </div>

        <div class="mb-4 flex flex-wrap gap-2">
            <a href="{{ route('devices.show', $device) }}" class="rounded bg-white px-3 py-2 text-sm border">Home</a>
            <a href="{{ route('devices.smart-fountain.scenes.index', $device) }}" class="rounded bg-white px-3 py-2 text-sm border">Scenes</a>
            <a href="{{ route('devices.smart-fountain.schedules.index', $device) }}" class="rounded bg-blue-600 px-3 py-2 text-sm text-white">Schedules</a>
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
            <h1 class="text-2xl font-bold">Edit {{ $schedule->name }} Block</h1>
            <p class="text-gray-600">Choose the days, time range, and scene for this timeline block. Blocks cannot overlap.</p>
        </div>

        <form method="POST" action="{{ route('devices.smart-fountain.schedules.update', [$device, $schedule]) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="rounded-lg bg-white p-5 shadow">
                <h2 class="mb-3 text-lg font-semibold">Days</h2>
                <div class="grid gap-2 sm:grid-cols-2 md:grid-cols-4">
                    @foreach ($dayNames as $dayNumber => $dayName)
                        <label class="flex items-center gap-2 rounded border px-3 py-2">
                            <input type="checkbox" name="days_of_week[]" value="{{ $dayNumber }}" @checked(in_array($dayNumber, old('days_of_week', $schedule->days_of_week ?? [1,2,3,4,5,6,7])))>
                            <span>{{ $dayName }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-lg bg-white p-5 shadow">
                    <h2 class="mb-3 text-lg font-semibold">Time Range</h2>

                    <label for="start_time" class="mb-1 block text-sm font-medium">Start Time</label>
                    <input id="start_time" type="time" name="start_time" value="{{ old('start_time', substr($schedule->start_time, 0, 5)) }}" class="mb-3 w-full rounded border px-3 py-2" required>

                    <label for="end_time" class="mb-1 block text-sm font-medium">End Time</label>
                    <input id="end_time" type="time" name="end_time" value="{{ old('end_time', substr($schedule->end_time, 0, 5)) }}" class="w-full rounded border px-3 py-2" required>
                </div>

                <div class="rounded-lg bg-white p-5 shadow">
                    <h2 class="mb-3 text-lg font-semibold">Scene</h2>

                    <label for="start_scene_id" class="mb-1 block text-sm font-medium">Scene to Apply</label>
                    <select id="start_scene_id" name="start_scene_id" class="mb-3 w-full rounded border px-3 py-2" required>
                        @foreach ($scenes as $scene)
                            <option value="{{ $scene->id }}" @selected((int) old('start_scene_id', $schedule->start_scene_id) === $scene->id)>
                                {{ $scene->name }}
                            </option>
                        @endforeach
                    </select>

                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_enabled" value="1" @checked(old('is_enabled', $schedule->is_enabled))>
                        <span>Enable this block</span>
                    </label>
                </div>
            </div>

            <div class="rounded border border-yellow-300 bg-yellow-50 px-4 py-3 text-yellow-800">
                A block may end at the same time the next block starts, but enabled blocks cannot overlap.
            </div>

            <div class="flex gap-2">
                <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    Update Timeline Block
                </button>

                <a href="{{ route('devices.smart-fountain.schedules.index', $device) }}" class="rounded bg-white px-4 py-2 border hover:bg-gray-50">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</body>

</html>
