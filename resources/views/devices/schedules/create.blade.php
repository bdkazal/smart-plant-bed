<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Schedule - {{ $device->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="max-w-3xl mx-auto py-10 px-4">
        <div class="mb-6">
            <a href="{{ route('devices.show', $device) }}" class="text-blue-600 hover:underline">← Back to Device</a>
        </div>

        <div class="rounded-lg bg-white p-6 shadow">
            <h1 class="mb-2 text-2xl font-bold">Create Watering Schedule</h1>
            <p class="mb-6 text-gray-600">Device: {{ $device->name }}</p>
            <p class="mb-6 text-gray-600">
                Schedule timezone: <strong>{{ $device->timezone ?? 'Asia/Dhaka' }}</strong>
            </p>

            @if ($errors->any())
            <div class="mb-6 rounded-lg bg-red-100 px-4 py-3 text-red-800">
                <ul class="list-disc ml-5">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('devices.schedules.store', $device) }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="day_of_week" class="mb-1 block font-medium">Day of Week</label>
                    <select name="day_of_week" id="day_of_week" class="w-full rounded border px-3 py-2" required>
                        <option value="">Select a day</option>
                        <option value="1" @selected(old('day_of_week')==1)>Monday</option>
                        <option value="2" @selected(old('day_of_week')==2)>Tuesday</option>
                        <option value="3" @selected(old('day_of_week')==3)>Wednesday</option>
                        <option value="4" @selected(old('day_of_week')==4)>Thursday</option>
                        <option value="5" @selected(old('day_of_week')==5)>Friday</option>
                        <option value="6" @selected(old('day_of_week')==6)>Saturday</option>
                        <option value="7" @selected(old('day_of_week')==7)>Sunday</option>
                    </select>
                </div>

                <div>
                    <label for="time_of_day" class="mb-1 block font-medium">Time</label>
                    <input
                        type="time"
                        name="time_of_day"
                        id="time_of_day"
                        value="{{ old('time_of_day') }}"
                        class="w-full rounded border px-3 py-2"
                        required>

                    <p class="mt-2 text-sm text-gray-500">
                        This schedule time will run in {{ $device->timezone ?? 'Asia/Dhaka' }}.
                    </p>
                </div>

                <div>
                    <label for="duration_seconds" class="mb-1 block font-medium">Duration (seconds)</label>
                    <input
                        type="number"
                        name="duration_seconds"
                        id="duration_seconds"
                        min="1"
                        max="300"
                        value="{{ old('duration_seconds', 30) }}"
                        class="w-full rounded border px-3 py-2"
                        required>
                </div>

                <div class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        name="is_enabled"
                        id="is_enabled"
                        value="1"
                        class="rounded border-gray-300"
                        @checked(old('is_enabled', true))>
                    <label for="is_enabled">Enable this schedule immediately</label>
                </div>

                <button
                    type="submit"
                    class="inline-flex items-center rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    Create Schedule
                </button>
            </form>
        </div>
    </div>
</body>

</html>