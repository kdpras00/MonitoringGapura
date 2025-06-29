<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ShowMaintenanceStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'show:maintenance-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show maintenance status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $maintenances = DB::table('maintenances')
            ->select('id', 'equipment_id', 'technician_id', 'status')
            ->get();

        $headers = ['ID', 'Equipment ID', 'Technician ID', 'Status'];
        $data = [];

        foreach ($maintenances as $maintenance) {
            $data[] = [
                'id' => $maintenance->id,
                'equipment_id' => $maintenance->equipment_id,
                'technician_id' => $maintenance->technician_id,
                'status' => $maintenance->status,
            ];
        }

        $this->table($headers, $data);

        return Command::SUCCESS;
    }
}
