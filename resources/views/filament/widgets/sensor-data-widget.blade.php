<div class="p-4 bg-white shadow rounded-lg">
    <h3 class="text-lg font-semibold">Latest Sensor Data</h3>
    @if ($sensor_data->isEmpty())
        <p class="text-gray-500">No sensor data available.</p>
    @else
        <ul class="mt-2">
            @foreach ($sensor_data as $data)
                <li class="border-b py-2">
                    <strong>Equipment:</strong> {{ $data->equipment->name ?? 'Unknown' }} <br>
                    <strong>Value:</strong> {{ $data->value }} <br>
                    <strong>Timestamp:</strong> {{ $data->created_at->format('Y-m-d H:i:s') }}
                </li>
            @endforeach
        </ul>
    @endif
</div>
