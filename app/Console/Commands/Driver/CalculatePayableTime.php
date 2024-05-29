<?php

namespace App\Console\Commands\Driver;

use App\Services\DriverDataService;
use Illuminate\Console\Command;

class CalculatePayableTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'driver:calculate-payable-time {filename?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates the payable time of drivers and saves the result to a CSV file.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filename = $this->argument('filename');
        $driverDataService = new DriverDataService();
        $driverDataService->calculatePayableTime($filename);
    }
}
