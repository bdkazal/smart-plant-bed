<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Device Setup - Smart Plant Bed</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="max-w-3xl mx-auto py-10 px-4">
        <div class="mb-6">
            <a href="{{ route('devices.show', $device) }}" class="text-blue-600 hover:underline">← Back to Device</a>
        </div>

        @if (session('success'))
        <div class="mb-6 rounded-lg bg-green-100 text-green-800 px-4 py-3">
            {{ session('success') }}
        </div>
        @endif

        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-2xl font-bold mb-4">Device Claimed Successfully</h1>

            <p class="mb-2"><strong>Device:</strong> {{ $device->name }}</p>
            <p class="mb-2"><strong>Claim Code:</strong> {{ $device->claim_code }}</p>
            <p class="mb-6"><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $device->status)) }}</p>

            <h2 class="text-xl font-semibold mb-3">Next Steps</h2>
            <ol class="list-decimal ml-5 space-y-2 text-gray-700">
                <li>Power on the device.</li>
                <li>Connect your phone or laptop to the device hotspot.</li>
                <li>If setup does not open automatically, go to <strong>192.168.4.1</strong>.</li>
                <li>Enter your Wi-Fi credentials.</li>
                <li>Wait for the device to connect to the server.</li>
            </ol>

            <div class="mt-6 rounded-lg bg-yellow-50 border border-yellow-200 p-4 text-sm text-yellow-900">
                The device is claimed, but it is still waiting for Wi-Fi setup. Once it reaches the server successfully, its status should move to active.
            </div>
        </div>
    </div>
</body>

</html>