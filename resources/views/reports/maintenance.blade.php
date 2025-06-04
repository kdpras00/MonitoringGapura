<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
            text-align: center;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid black;
            padding: 10px;
            text-align: left;
            word-wrap: break-word;
        }

        th {
            background-color: #f2f2f2;
        }

        /* Supaya lebih bagus saat dicetak */
        @media print {
            body {
                margin: 10px;
            }

            h1 {
                font-size: 20px;
            }

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
    </style>
</head>

<body>
    <h1>Maintenance Report</h1>
    <table>
        <thead>
            <tr>
                <th>Equipment</th>
                <th>Schedule Date</th>
                <th>Technician</th>
                <th>Status</th>
                <th>Cost</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($maintenances as $maintenance)
                <tr>
                    <td>{{ $maintenance->equipment->name ?? 'N/A' }}</td>
                    <td>{{ $maintenance->schedule_date ? $maintenance->schedule_date->format('d M Y H:i') : 'N/A' }}
                    </td>
                    <td>{{ $maintenance->technician->name ?? 'N/A' }}</td>
                    <td>{{ ucfirst($maintenance->status ?? 'N/A') }}</td>
                    <td>{{ $maintenance->cost ? 'Rp ' . number_format($maintenance->cost, 2, ',', '.') : 'Rp 0,00' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
