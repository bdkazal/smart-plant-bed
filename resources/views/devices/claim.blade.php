<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Device - Smart Plant Bed</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="max-w-2xl mx-auto py-10 px-4">
        <div class="mb-6">
            <a href="{{ route('devices.add') }}" class="text-blue-600 hover:underline">← Back to Add Device</a>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-2xl font-bold mb-4">Claim Device</h1>

            <p class="mb-2"><strong>Device:</strong> {{ $device->name }}</p>
            <p class="mb-6"><strong>Claim Code:</strong> {{ $device->claim_code }}</p>

            <p class="text-gray-600 mb-6">
                Confirm that you want to add this device to your account.
            </p>

            <form action="{{ route('devices.claim.confirm', $device->claim_code) }}" method="POST">
                @csrf
                <button
                    type="submit"
                    class="inline-flex items-center rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    Confirm Claim
                </button>
            </form>
        </div>
    </div>
</body>

</html>