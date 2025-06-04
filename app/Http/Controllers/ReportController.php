<?php

namespace App\Http\Controllers;

use App\Models\Maintenance;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function maintenanceReport()
    {
        $maintenances = Maintenance::all();
        $pdf = Pdf::loadView('reports.maintenance', compact('maintenances'));
        return $pdf->download('maintenance-report.pdf');
    }
}
