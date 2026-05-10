<?php

namespace App\Services;

use App\Models\Device;

class SmartFountainSafetyService
{
    public function isWaterLow(Device $device): bool
    {
        if (! $device->isSmartFountain()) {
            return false;
        }

        $latestWaterLow = $device->platformReadings()
            ->where('metric', 'water_low')
            ->latest('recorded_at')
            ->latest('id')
            ->first();

        return (int) ($latestWaterLow?->value ?? 0) === 1;
    }

    public function safePumpOffState(array $extra = []): array
    {
        return array_merge([
            'enabled' => false,
            'speed_percent' => 0,
            'safety_override' => 'water_low',
            'safety_message' => 'Pump forced OFF because low water was detected.',
        ], $extra);
    }

    public function applyWaterLowOverrideToOutputs(Device $device, array $outputs): array
    {
        if (! $this->isWaterLow($device)) {
            return $outputs;
        }

        if (! array_key_exists('pump', $outputs)) {
            return $outputs;
        }

        $outputs['pump'] = $this->safePumpOffState();

        return $outputs;
    }

    public function pumpWouldStart(array $state): bool
    {
        return (bool) data_get($state, 'enabled') === true
            && (int) data_get($state, 'speed_percent', 0) > 0;
    }
}
