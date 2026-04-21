<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Device extends Model
{
    public const TYPE_PLANT_BED_CONTROLLER = 'plant_bed_controller';
    public const TYPE_SOIL_MONITOR = 'soil_monitor';

    protected $fillable = [
        'user_id',
        'name',
        'device_type',
        'uuid',
        'api_key',
        'claim_code',
        'claimed_at',
        'status',
        'location_label',
        'timezone',
        'firmware_version',
        'last_seen_at',
        'provisioning_token',
        'provisioning_expires_at',
    ];

    protected $casts = [
        'claimed_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'provisioning_expires_at' => 'datetime',
    ];

    public static function typeOptions(): array
    {
        return [
            self::TYPE_PLANT_BED_CONTROLLER => 'Plant Bed Controller',
            self::TYPE_SOIL_MONITOR => 'Soil Moisture Monitor',
        ];
    }

    public function displayType(): string
    {
        return self::typeOptions()[$this->device_type] ?? 'Unknown Device';
    }

    public function isPlantBedController(): bool
    {
        return $this->device_type === self::TYPE_PLANT_BED_CONTROLLER;
    }

    public function isSoilMonitor(): bool
    {
        return $this->device_type === self::TYPE_SOIL_MONITOR;
    }

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

    public function wateringLogs(): HasMany
    {
        return $this->hasMany(WateringLog::class);
    }

    public function deviceCommands(): HasMany
    {
        return $this->hasMany(DeviceCommand::class);
    }
}
