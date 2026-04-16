<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart Plant Bed</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="max-w-md mx-auto py-12 px-4">
        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-2xl font-bold mb-6">Login</h1>

            @if (session('status'))
            <div class="mb-4 rounded bg-green-100 px-4 py-3 text-green-800">
                {{ session('status') }}
            </div>
            @endif

            @if ($errors->any())
            <div class="mb-4 rounded bg-red-100 px-4 py-3 text-red-800">
                <ul class="list-disc ml-5">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block font-medium mb-1">Email</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email') }}"
                        class="w-full rounded border px-3 py-2"
                        required
                        autofocus>
                </div>

                <div>
                    <label for="password" class="block font-medium mb-1">Password</label>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="w-full rounded border px-3 py-2"
                        required>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="remember" id="remember" class="rounded border-gray-300">
                    <label for="remember">Remember me</label>
                </div>

                <button
                    type="submit"
                    class="w-full rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    Login
                </button>
            </form>

            <div class="mt-4 text-sm">
                <a href="{{ route('password.request') }}" class="text-blue-600 hover:underline">Forgot password?</a>
            </div>

            <div class="mt-2 text-sm">
                <a href="{{ route('register') }}" class="text-blue-600 hover:underline">Create an account</a>
            </div>
        </div>
    </div>
</body>

</html>