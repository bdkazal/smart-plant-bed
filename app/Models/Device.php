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
    public const TYPE_SMART_PLANTER = 'smart_planter';
    public const TYPE_SMART_FOUNTAIN = 'smart_fountain';
    public const TYPE_FAN_LIGHT_CONTROLLER = 'fan_light_controller';
    public const TYPE_SMART_BATHROOM_CONTROLLER = 'smart_bathroom_controller';


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
        'last_reported_at',
        'last_reported_operation_state',
        'last_reported_valve_state',
        'last_reported_watering_state',
        'provisioning_token',
        'provisioning_expires_at',
        'last_reported_state',
        'metadata',
    ];

    protected $casts = [
        'claimed_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'last_reported_at' => 'datetime',
        'provisioning_expires_at' => 'datetime',
        'last_reported_state' => 'array',
        'metadata' => 'array',
    ];

    public static function typeOptions(): array
    {
        return [
            self::TYPE_PLANT_BED_CONTROLLER => 'Plant Bed Controller',
            self::TYPE_SOIL_MONITOR => 'Soil Moisture Monitor',
            self::TYPE_SMART_PLANTER => 'Smart Planter',
            self::TYPE_SMART_FOUNTAIN => 'Smart Fountain',
            self::TYPE_FAN_LIGHT_CONTROLLER => 'Fan & Light Controller',
            self::TYPE_SMART_BATHROOM_CONTROLLER => 'Smart Bathroom Controller',
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

    public function isSmartPlanter(): bool
    {
        return $this->device_type === self::TYPE_SMART_PLANTER;
    }

    public function isSmartFountain(): bool
    {
        return $this->device_type === self::TYPE_SMART_FOUNTAIN;
    }

    public function isFanLightController(): bool
    {
        return $this->device_type === self::TYPE_FAN_LIGHT_CONTROLLER;
    }

    public function isSmartBathroomController(): bool
    {
        return $this->device_type === self::TYPE_SMART_BATHROOM_CONTROLLER;
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

    public function capabilities(): HasMany
    {
        return $this->hasMany(DeviceCapability::class);
    }

    public function outputs(): HasMany
    {
        return $this->hasMany(DeviceOutput::class);
    }

    public function platformReadings(): HasMany
    {
        return $this->hasMany(DeviceReading::class);
    }
}
