<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Device - Smart Plant Bed</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="max-w-2xl mx-auto py-10 px-4">
        <div class="mb-6">
            <a href="{{ route('devices.index') }}" class="text-blue-600 hover:underline">← Back to Devices</a>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-2xl font-bold mb-4">Add Device</h1>
            <p class="text-gray-600 mb-6">
                Scan the QR code on your device, or enter the short claim code manually.
            </p>

            @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-100 text-red-800 px-4 py-3">
                <ul class="list-disc ml-5">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('devices.claim') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="claim_code" class="block font-medium mb-1">Claim Code</label>
                    <input
                        type="text"
                        name="claim_code"
                        id="claim_code"
                        value="{{ old('claim_code') }}"
                        placeholder="e.g. PB72K9"
                        class="w-full rounded border px-3 py-2 uppercase"
                        required>
                </div>

                <button
                    type="submit"
                    class="inline-flex items-center rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    Claim Device
                </button>
            </form>
        </div>
    </div>
</body>

</html>