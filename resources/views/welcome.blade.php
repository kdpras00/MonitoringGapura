<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Monitoring Gapura') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('{{ asset('img/gapura-background.jpg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        .backdrop {
            background-color: rgba(0, 0, 0, 0.6);
        }
    </style>
</head>
<body>
    <div class="backdrop min-h-screen flex flex-col items-center justify-center">
        <div class="max-w-4xl w-full px-4">
            <div class="bg-white rounded-lg shadow-xl overflow-hidden">
                <div class="p-8">
                    <div class="text-center">
                        <h1 class="text-4xl font-bold text-gray-800 mb-3">Sistem Monitoring Gapura</h1>
                        <p class="text-xl text-gray-600 mb-8">Manajemen dan Monitoring Peralatan, Maintenance, dan Inspeksi</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-12">
                            <div class="p-6 border border-gray-200 rounded-lg shadow-md bg-blue-50 hover:bg-blue-100 transition">
                                <h3 class="text-xl font-semibold text-blue-800 mb-3">Pelaporan Kerusakan</h3>
                                <p class="text-gray-600 mb-4">Laporkan kerusakan peralatan untuk ditindaklanjuti oleh tim maintenance</p>
                                <a href="{{ route('public.report.create') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition">
                                    Laporkan Kerusakan
                                </a>
                            </div>
                            
                            <div class="p-6 border border-gray-200 rounded-lg shadow-md bg-green-50 hover:bg-green-100 transition">
                                <h3 class="text-xl font-semibold text-green-800 mb-3">Portal Admin</h3>
                                <p class="text-gray-600 mb-4">Login ke sistem untuk teknisi, supervisor dan admin maintenance</p>
                                <a href="{{ url('/admin') }}" class="inline-block bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-6 rounded-lg transition">
                                    Login Admin
                                </a>
                            </div>
                        </div>
                        
                        <div class="mt-12 pt-6 border-t border-gray-200">
                            <div class="text-center text-gray-600 text-sm">
                                <p>&copy; {{ date('Y') }} Sistem Monitoring Gapura. All rights reserved.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 