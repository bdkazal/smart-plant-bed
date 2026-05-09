<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $schedule ? 'Edit Schedule' : 'Create Schedule' }} - {{ $device->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="max-w-4xl mx-auto py-6 px-4">
        <div class="mb-4">
            <a href="{{ route('devices.smart-fountain.schedules.index', $device) }}" class="text-blue-600 hover:underline">← Back to Schedules</a>
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
            <h1 class="text-2xl font-bold">{{ $schedule ? 'Edit Schedule Range' : 'Create Schedule Range' }}</h1>
            <p class="text-gray-600">Choose when to apply a start scene and an end scene.</p>
        </div>

        <form method="POST" action="{{ $schedule ? route('devices.smart-fountain.schedules.update', [$device, $schedule]) : route('devices.smart-fountain.schedules.store', $device) }}" class="space-y-4">
            @csrf
            @if ($schedule)
                @method('PUT')
            @endif

            <div class="rounded-lg bg-white p-5 shadow">
                <label for="name" class="mb-1 block text-sm font-medium">Schedule Name</label>
                <input id="name" type="text" name="name" value="{{ old('name', $schedule?->name) }}" class="w-full rounded border px-3 py-2" placeholder="Daily Fountain" required>
            </div>

            <div class="rounded-lg bg-white p-5 shadow">
                <h2 class="mb-3 text-lg font-semibold">Days</h2>
                <div class="grid gap-2 sm:grid-cols-2 md:grid-cols-4">
                    @foreach ($dayNames as $dayNumber => $dayName)
                        <label class="flex items-center gap-2 rounded border px-3 py-2">
                            <input type="checkbox" name="days_of_week[]" value="{{ $dayNumber }}" @checked(in_array($dayNumber, old('days_of_week', $schedule?->days_of_week ?? [1,2,3,4,5,6,7])))>
                            <span>{{ $dayName }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-lg bg-white p-5 shadow">
                    <h2 class="mb-3 text-lg font-semibold">Start</h2>

                    <label for="start_time" class="mb-1 block text-sm font-medium">Start Time</label>
                    <input id="start_time" type="time" name="start_time" value="{{ old('start_time', $schedule ? substr($schedule->start_time, 0, 5) : '06:00') }}" class="mb-3 w-full rounded border px-3 py-2" required>

                    <label for="start_scene_id" class="mb-1 block text-sm font-medium">Start Scene</label>
                    <select id="start_scene_id" name="start_scene_id" class="w-full rounded border px-3 py-2" required>
                        @foreach ($scenes as $scene)
                            <option value="{{ $scene->id }}" @selected((int) old('start_scene_id', $schedule?->start_scene_id) === $scene->id)>
                                {{ $scene->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="rounded-lg bg-white p-5 shadow">
                    <h2 class="mb-3 text-lg font-semibold">End</h2>

                    <label for="end_time" class="mb-1 block text-sm font-medium">End Time</label>
                    <input id="end_time" type="time" name="end_time" value="{{ old('end_time', $schedule ? substr($schedule->end_time, 0, 5) : '20:00') }}" class="mb-3 w-full rounded border px-3 py-2" required>

                    <label for="end_scene_id" class="mb-1 block text-sm font-medium">End Scene</label>
                    <select id="end_scene_id" name="end_scene_id" class="w-full rounded border px-3 py-2" required>
                        @foreach ($scenes as $scene)
                            <option value="{{ $scene->id }}" @selected((int) old('end_scene_id', $schedule?->end_scene_id) === $scene->id)>
                                {{ $scene->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="rounded-lg bg-white p-5 shadow">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_enabled" value="1" @checked(old('is_enabled', $schedule?->is_enabled ?? true))>
                    <span>Enable this schedule</span>
                </label>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    {{ $schedule ? 'Update Schedule' : 'Save Schedule' }}
                </button>

                <a href="{{ route('devices.smart-fountain.schedules.index', $device) }}" class="rounded bg-white px-4 py-2 border hover:bg-gray-50">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</body>

</html>
