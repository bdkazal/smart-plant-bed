<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devices - Smart Plant Bed</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="max-w-5xl mx-auto py-10 px-4">
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-3xl font-bold">Devices</h1>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button
                    type="submit"
                    class="rounded bg-gray-300 px-4 py-2 text-sm text-gray-900 hover:bg-gray-400">
                    Logout
                </button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3">ID</th>
                        <th class="text-left px-4 py-3">Name</th>
                        <th class="text-left px-4 py-3">UUID</th>
                        <th class="text-left px-4 py-3">Status</th>
                        <th class="text-left px-4 py-3">Location</th>
                        <th class="text-left px-4 py-3">Firmware</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($devices as $device)
                    <tr class="border-t">
                        <td class="px-4 py-3">{{ $device->id }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('devices.show', $device) }}" class="text-blue-600 hover:underline">
                                {{ $device->name }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $device->uuid }}</td>
                        <td class="px-4 py-3">{{ ucfirst($device->status) }}</td>
                        <td class="px-4 py-3">{{ $device->location_label ?? 'N/A' }}</td>
                        <td class="px-4 py-3">{{ $device->firmware_version ?? 'N/A' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                            No devices found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>