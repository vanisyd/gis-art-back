<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\DriverTrip;
use Exception;
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
     */
    public function calculatePayableTime(string|null $filename = null): void
    {
        $driverTrips = DB::table('driver_trips')
            ->orderBy('pickup')
            ->get();

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
                    $payableTime[$driver_id][] =  ['pickup' => $trip->pickup, 'dropoff' => $trip->dropoff];
                }
            }
        }

        $totalTime = [];

        foreach ($payableTime as $driver_id => $intervals) {
            $totalTime[$driver_id] = 0;
            foreach($intervals as $interval) {
                $totalTime[$driver_id] += strtotime($interval['dropoff']) - strtotime($interval['pickup']);
            }
            $totalTime[$driver_id] = $this->formatSecondsToTime($totalTime[$driver_id]);
        }

        if ($filename === null) {
            $filename = $this->getOutputFilename();
        }
        $file = fopen($filename, 'w');
        fputcsv($file, ['driver_id', 'total_minutes_with_passenger']);
        foreach ($totalTime as $driver_id => $value) {
            fputcsv($file, [$driver_id, $value]);
        }
        fclose($file);
    }

    private function getDataFilename(): string
    {
        return config('services.driver-data.trips_filename');
    }

    private function getOutputFilename(): string
    {
        return config('services.driver-data.output_filename');
    }

    private function formatSecondsToTime($total_seconds): string
    {
        $hours = floor($total_seconds / 3600);
        $minutes = floor(($total_seconds / 60) % 60);
        $seconds = $total_seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
}
