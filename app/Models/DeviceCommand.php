<?php

namespace App\Models;

use App\Services\SmartFountainSafetyService;
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

    protected static function booted(): void
    {
        static::creating(function (DeviceCommand $command): void {
            $device = $command->device ?: Device::find($command->device_id);

            if (! $device?->isSmartFountain()) {
                return;
            }

            $safety = app(SmartFountainSafetyService::class);

            if (! $safety->isWaterLow($device)) {
                return;
            }

            $payload = $command->payload ?? [];

            if ($command->command_type === 'output_set' && data_get($payload, 'output') === 'pump') {
                $state = (array) data_get($payload, 'state', []);

                if ($safety->pumpWouldStart($state)) {
                    data_set($payload, 'state', $safety->safePumpOffState([
                        'requested_state' => $state,
                    ]));
                    data_set($payload, 'safety_override', 'water_low');
                    data_set($payload, 'safety_message', 'Pump was kept off because low water was detected.');
                }
            }

            if ($command->command_type === 'scene_apply') {
                $outputs = data_get($payload, 'outputs', []);

                if (is_array($outputs)) {
                    data_set($payload, 'outputs', $safety->applyWaterLowOverrideToOutputs($device, $outputs));

                    if (data_get($payload, 'outputs.pump.safety_override') === 'water_low') {
                        data_set($payload, 'safety_override', 'water_low');
                        data_set($payload, 'safety_message', 'Scene was applied with pump kept off because low water was detected.');
                    }
                }
            }

            $command->payload = $payload;
        });
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function wateringLogs(): HasMany
    {
        return $this->hasMany(WateringLog::class);
    }
}
