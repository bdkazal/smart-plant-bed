<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WateringLog extends Model
{
    protected $fillable = [
        'device_id',
        'device_command_id',
        'trigger_type',
        'duration_seconds',
        'started_at',
        'ended_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function deviceCommand(): BelongsTo
    {
        return $this->belongsTo(DeviceCommand::class);
    }
}
