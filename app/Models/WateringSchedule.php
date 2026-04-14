<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WateringSchedule extends Model
{
    protected $fillable = [
        'device_id',
        'is_enabled',
        'day_of_week',
        'time_of_day',
        'duration_seconds',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
