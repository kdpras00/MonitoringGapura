@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-6">Maintenance Alat/Barang</h1>

        <!-- Grafik Maintenance -->
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-xl font-semibold mb-4">Grafik Maintenance Bulanan</h2>
            <canvas id="maintenanceChart" class="w-full h-64"></canvas>
        </div>

        <!-- Tabel Data Maintenance -->
        <div class="mt-8 bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-xl font-semibold mb-4">Data Maintenance</h2>
            <table class="min-w-full bg-white">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b">No</th>
                        <th class="py-2 px-4 border-b">Nama Alat/Barang</th>
                        <th class="py-2 px-4 border-b">Tanggal Maintenance</th>
                        <th class="py-2 px-4 border-b">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="py-2 px-4 border-b text-center">1</td>
                        <td class="py-2 px-4 border-b">Mesin Produksi A</td>
                        <td class="py-2 px-4 border-b">2023-10-01</td>
                        <td class="py-2 px-4 border-b text-green-600">Selesai</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-4 border-b text-center">2</td>
                        <td class="py-2 px-4 border-b">Mesin Produksi B</td>
                        <td class="py-2 px-4 border-b">2023-10-05</td>
                        <td class="py-2 px-4 border-b text-yellow-600">Dalam Proses</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-4 border-b text-center">3</td>
                        <td class="py-2 px-4 border-b">Mesin Produksi C</td>
                        <td class="py-2 px-4 border-b">2023-10-10</td>
                        <td class="py-2 px-4 border-b text-red-600">Belum Dimulai</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Script untuk Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('maintenanceChart').getContext('2d');
        const maintenanceChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: [{
                    label: 'Jumlah Maintenance',
                    data: [12, 19, 3, 5, 2, 3, 7, 8, 9, 10, 14, 6],
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
@endsection
