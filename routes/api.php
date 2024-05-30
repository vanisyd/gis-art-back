<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('driver')->group(function () {
    Route::get('/trips', [
        App\Http\Controllers\DriverTripsController::class, 'getTrips'
    ]);
    Route::get('/payable-time', [
        App\Http\Controllers\DriverTripsController::class, 'getPayableTime'
    ]);
    Route::post('/payable-time', [
        App\Http\Controllers\DriverTripsController::class, 'calculatePayableTime'
    ]);
});
