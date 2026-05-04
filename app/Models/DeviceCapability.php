<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceCapability extends Model
{
    protected $fillable = [
        'device_id',
        'capability',
        'config',
        'state',
    ];

    protected $casts = [
        'config' => 'array',
        'state' => 'array',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
