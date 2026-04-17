<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devices - Smart Plant Bed</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="max-w-6xl mx-auto py-10 px-4">
        <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold">Devices</h1>
                <p class="text-gray-600">Manage your claimed devices and continue setup when needed.</p>
            </div>

            <div class="flex items-center gap-3">
                <a
                    href="{{ route('devices.add') }}"
                    class="rounded bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">
                    Add Device
                </a>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button
                        type="submit"
                        class="rounded bg-gray-300 px-4 py-2 text-sm text-gray-900 hover:bg-gray-400">
                        Logout
                    </button>
                </form>
            </div>
        </div>

        @if (session('success'))
        <div class="mb-6 rounded-lg bg-green-100 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
        @endif

        @if ($devices->isEmpty())
        <div class="rounded-lg bg-white p-8 shadow">
            <h2 class="text-xl font-semibold">No devices yet</h2>
            <p class="mt-2 text-gray-600">
                You have not claimed any device yet. Add a device using your claim code or QR flow.
            </p>

            <div class="mt-5">
                <a
                    href="{{ route('devices.add') }}"
                    class="inline-flex items-center rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    Add Your First Device
                </a>
            </div>
        </div>
        @else
        <div class="overflow-hidden rounded-lg bg-white shadow">
            <table class="min-w-full">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left">ID</th>
                        <th class="px-4 py-3 text-left">Name</th>
                        <th class="px-4 py-3 text-left">UUID</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Location</th>
                        <th class="px-4 py-3 text-left">Firmware</th>
                        <th class="px-4 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($devices as $device)
                    <tr class="border-t align-top">
                        <td class="px-4 py-3">{{ $device->id }}</td>

                        <td class="px-4 py-3">
                            <a href="{{ route('devices.show', $device) }}" class="text-blue-600 hover:underline">
                                {{ $device->name }}
                            </a>
                        </td>

                        <td class="px-4 py-3 text-sm text-gray-600">{{ $device->uuid }}</td>

                        <td class="px-4 py-3">
                            <div>{{ ucfirst(str_replace('_', ' ', $device->status)) }}</div>

                            @if ($device->status === 'claimed_pending_wifi')
                            <div class="mt-1 text-sm text-yellow-700">
                                Waiting for Wi-Fi setup
                            </div>
                            @endif
                        </td>

                        <td class="px-4 py-3">{{ $device->location_label ?? 'N/A' }}</td>
                        <td class="px-4 py-3">{{ $device->firmware_version ?? 'N/A' }}</td>

                        <td class="px-4 py-3">
                            <div class="flex flex-col gap-2">
                                <a
                                    href="{{ route('devices.show', $device) }}"
                                    class="text-blue-600 hover:underline">
                                    View Device
                                </a>

                                @if ($device->status === 'claimed_pending_wifi')
                                <a
                                    href="{{ route('devices.setup', $device) }}"
                                    class="text-yellow-700 hover:underline">
                                    Continue Setup
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</body>

</html>