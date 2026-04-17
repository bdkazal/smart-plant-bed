<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WateringRule extends Model
{
    protected $fillable = [
        'device_id',
        'watering_mode',
        'auto_mode_enabled',
        'soil_moisture_threshold',
        'max_watering_duration_seconds',
        'cooldown_minutes',
        'local_manual_duration_seconds',
    ];

    protected $casts = [
        'auto_mode_enabled' => 'boolean',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
