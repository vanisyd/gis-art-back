<?php

namespace App\Repositories;

use App\Models\Driver;
use App\Models\DriverPayableTime;
use App\Models\DriverTrip;

class DriverRepository
{
    public function createDriver(array $data)
    {
        return Driver::firstOrCreate($data);
    }

    public function createDriverTrip(array $data)
    {
        return DriverTrip::create($data);
    }

    public function getDriverTrips(string $orderBy = null)
    {
        return DriverTrip::query()
            ->when($orderBy, fn($query) => $query->orderBy($orderBy))
            ->get();
    }

    public function createDriverPayableTime(array $data)
    {
        return DriverPayableTime::create($data);
    }
}
