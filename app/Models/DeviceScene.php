<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceScene extends Model
{
    protected $fillable = [
        'device_id',
        'name',
        'outputs',
        'is_default',
    ];

    protected $casts = [
        'outputs' => 'array',
        'is_default' => 'boolean',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
