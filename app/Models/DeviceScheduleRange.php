<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceScheduleRange extends Model
{
    protected $fillable = [
        'device_id',
        'name',
        'period_key',
        'days_of_week',
        'start_time',
        'end_time',
        'start_scene_id',
        'end_scene_id',
        'is_enabled',
        'last_started_on',
        'last_ended_on',
        'last_started_at',
        'last_ended_at',
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'is_enabled' => 'boolean',
        'last_started_on' => 'date',
        'last_ended_on' => 'date',
        'last_started_at' => 'datetime',
        'last_ended_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function startScene(): BelongsTo
    {
        return $this->belongsTo(DeviceScene::class, 'start_scene_id');
    }

    public function endScene(): BelongsTo
    {
        return $this->belongsTo(DeviceScene::class, 'end_scene_id');
    }
}
