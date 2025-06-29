<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Monitoring Maintenance Gapura Angkasa</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full p-6 bg-white rounded-lg shadow-lg">
        <div class="text-center mb-6">
            <img src="{{ asset('img/gapura-background.jpg') }}" alt="Gapura Logo" class="h-20 mx-auto mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Monitoring Gapura Angkasa</h2>
            <p class="text-sm text-gray-600 mt-1">Login untuk melanjutkan</p>
        </div>

        @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
        @endif

        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
        @endif

        <form method="POST" action="{{ route('unified.login') }}" class="space-y-4">
            @csrf
            <div>
                <label for="login" class="block text-gray-700 text-sm font-bold mb-2">Email atau Username</label>
                <input id="login" type="text" name="login" value="{{ old('login') }}" required autofocus
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                @error('login')
                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <input id="password" type="password" name="password" required
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                @error('password')
                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember_me" type="checkbox" name="remember" class="h-4 w-4 text-blue-600">
                    <label for="remember_me" class="ml-2 block text-gray-700 text-sm">Ingat Saya</label>
                </div>

                @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:underline">
                    Lupa Password?
                </a>
                @endif
            </div>

            <div>
                <button type="submit"
                    class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Login
                </button>
            </div>
        </form>

        <div class="mt-6 text-center text-sm text-gray-600">
            &copy; {{ date('Y') }} PT Gapura Angkasa. All rights reserved.
        </div>
    </div>
</body>
</html> 