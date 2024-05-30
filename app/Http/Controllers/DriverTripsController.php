<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetDriversPayableTimeRequest;
use App\Http\Requests\GetDriverTripsRequest;
use App\Http\Resources\DriverPayableTimeResource;
use App\Http\Resources\DriverTripsResource;
use App\Models\DriverPayableTime;
use App\Models\DriverTrip;
use App\Services\DriverDataService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DriverTripsController extends Controller
{
    private readonly DriverDataService $driverDataService;

    public function __construct(DriverDataService $driverDataService)
    {
        $this->driverDataService = $driverDataService;
    }

    public function getTrips(GetDriverTripsRequest $request): AnonymousResourceCollection
    {
        $driverTrips = DriverTrip::query();
        $request->whenHas('driver_id', fn ($driverId) => $driverTrips->where('driver_id', $driverId));
        $request->whenHas('pickup', fn ($pickup) => $driverTrips->where('pickup', '>=', $pickup));
        $request->whenHas('dropoff', fn ($dropoff) => $driverTrips->where('dropoff', '<=', $dropoff));
        if ($request->has('sort_by')) {
            $driverTrips->orderBy($request->input('sort_by'), $request->input('sort_order', 'asc'));
        }
        $driverTrips = $driverTrips->get();

        return DriverTripsResource::collection($driverTrips);
    }

    public function getPayableTime(GetDriversPayableTimeRequest $request): AnonymousResourceCollection
    {
        $payableTime = DriverPayableTime::query();
        $request->whenHas('driver_id', fn ($driverId) => $payableTime->where('driver_id', $driverId));
        if ($request->has('total_minutes_with_passenger')) {
            $payableTime->where('total_minutes_with_passenger', $request->input('total_minutes_with_passenger'));
        }
        if ($request->has('sort_by')) {
            $payableTime->orderBy($request->input('sort_by'), $request->input('sort_order', 'asc'));
        }
        $driverTrips = $payableTime->get();

        return DriverPayableTimeResource::collection($driverTrips);
    }

    public function calculatePayableTime(): AnonymousResourceCollection
    {
        $data = $this->driverDataService->calculatePayableTime();

        return DriverPayableTimeResource::collection($data);
    }
}
