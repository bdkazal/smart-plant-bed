<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Smart Plant Bed</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="max-w-md mx-auto py-12 px-4">
        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-2xl font-bold mb-6">Reset Password</h1>

            @if ($errors->any())
            <div class="mb-4 rounded bg-red-100 px-4 py-3 text-red-800">
                <ul class="list-disc ml-5">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('password.update') }}" method="POST" class="space-y-4">
                @csrf

                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div>
                    <label for="email" class="block font-medium mb-1">Email</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email', $request->email) }}"
                        class="w-full rounded border px-3 py-2"
                        required>
                </div>

                <div>
                    <label for="password" class="block font-medium mb-1">New Password</label>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="w-full rounded border px-3 py-2"
                        required>
                </div>

                <div>
                    <label for="password_confirmation" class="block font-medium mb-1">Confirm Password</label>
                    <input
                        type="password"
                        name="password_confirmation"
                        id="password_confirmation"
                        class="w-full rounded border px-3 py-2"
                        required>
                </div>

                <button
                    type="submit"
                    class="w-full rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    Reset Password
                </button>
            </form>
        </div>
    </div>
</body>

</html>