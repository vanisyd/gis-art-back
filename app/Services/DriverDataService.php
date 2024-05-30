<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\DriverPayableTime;
use App\Models\DriverTrip;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DriverDataService
{
    /**
     * Imports driver trips data from the specified CSV file.
     * @param string|null $filename
     * @return int
     * @throws Exception
     */
    public function importDriverData(string|null $filename = null): int
    {
        $importedCount = 0;

        if ($filename === null) {
            $filename = $this->getDataFilename();
        }
        $file = fopen($filename, 'r');
        if ($file === false) {
            throw new Exception("Could not open file: {$filename}");
        }

        // Skip the header line
        fgetcsv($file);
        while (($line = fgetcsv($file)) !== false) {
            try {
                $driverId = $line[1];
                // Ensure the driver exists
                Driver::firstOrCreate(['id' => $driverId]);
                $driverTrip = new DriverTrip([
                    'driver_id' => $driverId,
                    'pickup' => $line[2],
                    'dropoff' => $line[3]
                ]);
                $driverTrip->save();
                $importedCount++;
            } catch (\Exception $e) {
                Log::error("[Driver Data Service] Error importing row: " . $e->getMessage());
                continue;
            }
        }
        fclose($file);

        return $importedCount;
    }

    /**
     * Calculates the payable time of drivers and saves the result to a CSV file.
     * @param string|null $filename
     * @return Collection
     */
    public function calculatePayableTime(string|null $filename = null): Collection
    {
        $driverTrips = DriverTrip::query()
            ->orderBy('pickup')
            ->get();

        // Group trips by driver and merge overlapping intervals
        $payableTime = [];
        foreach ($driverTrips as $trip) {
            $driver_id = $trip->driver_id;
            if (!isset($payableTime[$driver_id])) {
                $payableTime[$driver_id] = [
                    ['pickup' => $trip->pickup, 'dropoff' => $trip->dropoff]
                ];
            } else {
                $lastInterval = &$payableTime[$driver_id][count($payableTime[$driver_id]) - 1];
                if ($trip->pickup <= $lastInterval['dropoff']) {
                    $lastInterval['dropoff'] = max($trip->dropoff, $lastInterval['dropoff']);
                } else {
                    $payableTime[$driver_id][] = ['pickup' => $trip->pickup, 'dropoff' => $trip->dropoff];
                }
            }
        }
        // Calculate total time with passenger for each driver
        $totalTime = [];
        foreach ($payableTime as $driver_id => $intervals) {
            $totalTime[$driver_id] = 0;
            foreach ($intervals as $interval) {
                $totalTime[$driver_id] += abs($interval['dropoff']->diffInSeconds($interval['pickup']));
            }
            $totalTime[$driver_id] = $this->formatSecondsToMinutes($totalTime[$driver_id]);
        }

        //Export data
        $data = $this->exportPayableTime($filename, $totalTime);

        return $data;
    }

    /**
     * @param string|null $filename
     * @param array $totalTime
     * @return Collection
     */
    private function exportPayableTime(string|null $filename = null, array $totalTime = []): Collection
    {
        DB::table('driver_payable_times')->truncate();
        $data = collect();
        if ($filename === null) {
            $filename = $this->getOutputFilename();
        }
        $file = fopen($filename, 'w');
        fputcsv($file, ['driver_id', 'total_minutes_with_passenger']);
        foreach ($totalTime as $driver_id => $value) {
            fputcsv($file, [$driver_id, $value]);
            $dbItem = DriverPayableTime::create([
                'driver_id' => $driver_id,
                'total_minutes_with_passenger' => $value
            ]);
            $data->push($dbItem);
        }
        fclose($file);

        return $data;
    }

    private function getDataFilename(): string
    {
        return config('services.driver-data.trips_filename');
    }

    private function getOutputFilename(): string
    {
        return config('services.driver-data.output_filename');
    }

    private function formatSecondsToMinutes(float|int $driver_id): float
    {
        return round($driver_id / 60);
    }
}
