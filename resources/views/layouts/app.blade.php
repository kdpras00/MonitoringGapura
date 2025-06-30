<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script src="//unpkg.com/alpinejs" defer></script>

    <!-- Livewire Styles -->
    @livewireStyles

    <!-- FullCalendar CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="font-sans antialiased">
    <div id="app" class="min-h-screen bg-gray-100">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Notifications -->
        <div class="relative">
            <button onclick="toggleNotifications()" class="p-2 text-gray-700 hover:text-gray-900">
                <i class="fas fa-bell"></i>
                @if ($unreadNotifications->count() > 0)
                    <span class="absolute top-0 right-0 bg-red-500 text-white rounded-full text-xs px-1">
                        {{ $unreadNotifications->count() }}
                    </span>
                @endif
            </button>

            <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg">
                @forelse($unreadNotifications as $notification)
                    <a href="{{ $notification->data['link'] }}" class="block px-4 py-2 hover:bg-gray-100">
                        {{ $notification->data['message'] }}
                    </a>
                @empty
                    <div class="px-4 py-2 text-gray-500">Tidak ada notifikasi baru.</div>
                @endforelse
            </div>
        </div>

        <script>
            function toggleNotifications() {
                document.getElementById('notificationDropdown').classList.toggle('hidden');
            }
        </script>

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>
    </div>

    @if (auth()->check() && auth()->user()->role === 'technician')
        <!-- Custom Technician Dashboard -->
        <div class="bg-blue-100 min-h-screen p-6">
            <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach (auth()->user()->assignedMaintenance ?? [] as $maintenance)
                    <div class="bg-white rounded-lg shadow p-4">
                        <h3 class="text-lg font-bold text-gray-800">
                            {{ $maintenance->equipment->name }}
                        </h3>
                        <div class="mt-2">
                            <span
                                class="px-2 py-1 bg-{{ $maintenance->status_color }}-100 text-{{ $maintenance->status_color }}-800 rounded-full">
                                {{ $maintenance->status }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Livewire Scripts -->
    @livewireScripts

    <!-- Stack untuk Scripts -->
    @stack('scripts')
</body>

</html>
