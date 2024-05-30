<?php

namespace App\Console\Commands\Driver;

use App\Services\DriverDataService;
use Exception;
use Illuminate\Console\Command;

class ImportDriverTrips extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'driver:import-trips {filename?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports driver trips data from a CSV file.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $filename = $this->argument('filename');
        $driverDataService = app(DriverDataService::class);
        try {
            $importedCount = $driverDataService->importDriverData($filename);
            $this->info("Imported {$importedCount} rows.");
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
