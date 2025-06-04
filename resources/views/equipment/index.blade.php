@foreach ($equipment as $item)
    <tr>
        <td>{{ $item->name }}</td>
        <td>{{ $item->serial_number }}</td>
        <td>
            <a href="{{ route('filament.resources.equipment.view', $item->id) }}" class="text-blue-500 underline">
                Lihat Detail
            </a>
        </td>
    </tr>
@endforeach
