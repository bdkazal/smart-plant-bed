<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeviceCommand extends Model
{
    protected $fillable = [
        'device_id',
        'command_type',
        'payload',
        'status',
        'issued_at',
        'acknowledged_at',
        'executed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'issued_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'executed_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function wateringLogs(): HasMany
    {
        return $this->hasMany(WateringLog::class);
    }
}
