<div>
    <div wire:ignore>
        <canvas id="maintenanceChart"></canvas>
    </div>

    @script
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('maintenanceChart')?.getContext('2d');
                if (ctx) {
                    const data = @json($chartData);

                    new Chart(ctx, {
                        type: 'line',
                        data: data,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                title: {
                                    display: true,
                                    text: 'Maintenance per Bulan'
                                }
                            }
                        }
                    });
                }
            });
        </script>
    @endscript
</div>
