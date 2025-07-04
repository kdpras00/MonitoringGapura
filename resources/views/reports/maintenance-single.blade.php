<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Report #{{ $maintenance->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .date {
            font-size: 14px;
            margin-bottom: 20px;
            color: #666;
        }

        .maintenance-info {
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            width: 30%;
        }

        .section {
            margin-top: 30px;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .images {
            margin-top: 20px;
            text-align: center;
        }

        .image-container {
            display: inline-block;
            margin: 10px;
            vertical-align: top;
        }

        .image {
            max-width: 300px;
            max-height: 300px;
            border: 1px solid #ddd;
        }

        .image-caption {
            font-size: 14px;
            margin-top: 5px;
            text-align: center;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Laporan Maintenance #{{ $maintenance->id }}</h1>
        <div class="date">Dibuat pada: {{ now()->format('d M Y H:i') }}</div>
    </div>

    <div class="maintenance-info">
        <table>
            <tr>
                <th>ID</th>
                <td>{{ $maintenance->id }}</td>
            </tr>
            <tr>
                <th>Equipment</th>
                <td>{{ $maintenance->equipment->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Jadwal</th>
                <td>{{ $maintenance->schedule_date ? $maintenance->schedule_date->format('d M Y H:i') : 'N/A' }}</td>
            </tr>
            <tr>
                <th>Tanggal Aktual</th>
                <td>{{ $maintenance->actual_date ? $maintenance->actual_date->format('d M Y H:i') : 'N/A' }}</td>
            </tr>
            <tr>
                <th>Teknisi</th>
                <td>{{ $maintenance->technician->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Jenis</th>
                <td>{{ ucfirst($maintenance->maintenance_type ?? 'N/A') }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>{{ ucfirst($maintenance->status ?? 'N/A') }}</td>
            </tr>
            <tr>
                <th>Biaya</th>
                <td>{{ $maintenance->cost ? 'Rp ' . number_format($maintenance->cost, 2, ',', '.') : 'Rp 0,00' }}</td>
            </tr>
            <tr>
                <th>Durasi</th>
                <td>{{ $maintenance->duration ?? 'N/A' }} Menit</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Catatan</div>
        <p>{{ $maintenance->notes ?? 'Tidak ada catatan' }}</p>
    </div>

    <div class="section">
        <div class="section-title">Checklist</div>
        @if (is_array($maintenance->checklist) && count($maintenance->checklist) > 0)
            <ul>
                @foreach ($maintenance->checklist as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        @else
            <p>Tidak ada checklist</p>
        @endif
    </div>

    <div class="section">
        <div class="section-title">Foto</div>
        <div class="images">
            @if ($maintenance->before_image)
                <div class="image-container">
                    <img class="image" src="{{ public_path('storage/' . $maintenance->before_image) }}" alt="Foto Sebelum">
                    <div class="image-caption">Sebelum Maintenance</div>
                </div>
            @endif

            @if ($maintenance->after_image)
                <div class="image-container">
                    <img class="image" src="{{ public_path('storage/' . $maintenance->after_image) }}" alt="Foto Setelah">
                    <div class="image-caption">Setelah Maintenance</div>
                </div>
            @endif

            @if (!$maintenance->before_image && !$maintenance->after_image)
                <p>Tidak ada foto</p>
            @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Status Approval</div>
        <table>
            <tr>
                <th>Status</th>
                <td>{{ ucfirst($maintenance->approval_status ?? 'Belum disetujui') }}</td>
            </tr>
            <tr>
                <th>Disetujui oleh</th>
                <td>{{ $maintenance->approver->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Tanggal persetujuan</th>
                <td>{{ $maintenance->approval_date ? $maintenance->approval_date->format('d M Y H:i') : 'N/A' }}</td>
            </tr>
            <tr>
                <th>Catatan persetujuan</th>
                <td>{{ $maintenance->approval_notes ?? 'Tidak ada catatan' }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Laporan Maintenance ini digenerate secara otomatis oleh Sistem Monitoring Gapura pada {{ now()->format('d M Y H:i') }}</p>
    </div>
</body>

</html> 