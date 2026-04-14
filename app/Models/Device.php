<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Device extends Model
{
    protected $fillable = [
        'uuid',
        'name',
        'api_key',
        'status',
        'last_seen_at',
        'firmware_version',
        'location_label',
        'timezone',
        'user_id',
        'claim_code',
        'claimed_at',
        'provisioning_token',
        'provisioning_expires_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'claimed_at' => 'datetime',
        'provisioning_expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sensorReadings(): HasMany
    {
        return $this->hasMany(SensorReading::class);
    }

    public function wateringRule(): HasOne
    {
        return $this->hasOne(WateringRule::class);
    }

    public function wateringSchedules(): HasMany
    {
        return $this->hasMany(WateringSchedule::class);
    }

    public function deviceCommands(): HasMany
    {
        return $this->hasMany(DeviceCommand::class);
    }

    public function wateringLogs(): HasMany
    {
        return $this->hasMany(WateringLog::class);
    }
}
