<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - Smart Plant Bed</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="max-w-md mx-auto py-12 px-4">
        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-2xl font-bold mb-6">Verify Your Email</h1>

            @if (session('status') === 'verification-link-sent')
            <div class="mb-4 rounded bg-green-100 px-4 py-3 text-green-800">
                A new verification link has been sent to your email address.
            </div>
            @endif

            <p class="text-gray-700 mb-6">
                Please verify your email address by clicking the link we sent you.
            </p>

            <form action="{{ route('verification.send') }}" method="POST" class="mb-4">
                @csrf
                <button
                    type="submit"
                    class="w-full rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    Resend Verification Email
                </button>
            </form>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button
                    type="submit"
                    class="w-full rounded bg-gray-300 px-4 py-2 text-gray-900 hover:bg-gray-400">
                    Logout
                </button>
            </form>
        </div>
    </div>
</body>

</html>