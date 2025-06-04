<div>
    @if (!empty($predictions) && count($predictions) > 0)
        @foreach ($predictions as $prediction)
            <div class="p-6 bg-black shadow-md rounded-lg mb-4 text-white">
                <h3 class="text-lg font-semibold">{{ $prediction['equipment']->name ?? '-' }}</h3>
                <div class="mt-2 text-sm">
                    <p><strong>Last Maintenance:</strong> {{ $prediction['last_maintenance_date']->format('Y-m-d') }}
                    </p>
                    <p><strong>Next Maintenance:</strong> {{ $prediction['next_maintenance_date']->format('Y-m-d') }}
                    </p>
                    <p><strong>Condition Score:</strong> {{ $prediction['condition_score'] ?? 'Unknown' }}</p>
                    <p><strong>Recommendation:</strong> {{ $prediction['recommendation'] ?? 'Unknown' }}</p>
                    <p><strong>Prediction:</strong>
                        {{ $prediction['condition_score'] < 50 ? 'Maintenance Required' : 'No Maintenance Required' }}
                    </p>
                    <p><strong>Message:</strong> {{ $prediction['recommendation'] }}</p>
                </div>
            </div>
        @endforeach
    @else
        <p class="text-red-600 mt-2">Data maintenance tidak tersedia.</p>
    @endif
</div>
