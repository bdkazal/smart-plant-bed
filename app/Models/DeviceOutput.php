<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceOutput extends Model
{
    protected $fillable = [
        'device_id',
        'key',
        'type',
        'name',
        'config',
        'state',
        'last_changed_source',
        'last_changed_at',
    ];

    protected $casts = [
        'config' => 'array',
        'state' => 'array',
        'last_changed_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
