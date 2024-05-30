<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverPayableTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'total_minutes_with_passenger'
    ];
}
