<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverTrip extends Model
{
    use HasFactory, HasTimestamps;

    protected $fillable = [
        'driver_id',
        'pickup',
        'dropoff'
    ];

    protected $casts = [
        'pickup' => 'datetime'
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }
}
