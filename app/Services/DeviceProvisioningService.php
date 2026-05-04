<?php

namespace App\Services;

use App\Models\Device;

class DeviceProvisioningService
{
    public function provision(Device $device): void
    {
        match ($device->device_type) {
            Device::TYPE_SMART_FOUNTAIN => $this->provisionSmartFountain($device),
            default => null,
        };
    }

    private function provisionSmartFountain(Device $device): void
    {
        $this->syncCapabilities($device, [
            [
                'capability' => 'pump_output',
                'config' => [
                    'label' => 'Water Pump',
                    'supports_pwm' => true,
                    'requires_water_level_ok' => true,
                ],
                'state' => [
                    'available' => true,
                ],
            ],
            [
                'capability' => 'dimmable_light',
                'config' => [
                    'label' => 'COB Ring Light',
                    'supports_pwm' => true,
                ],
                'state' => [
                    'available' => true,
                ],
            ],
            [
                'capability' => 'rgb_light',
                'config' => [
                    'label' => 'Decorative RGB Light',
                    'supported_effects' => [
                        'solid',
                        'breathing',
                        'slow_rainbow',
                        'warm_glow',
                        'water_shimmer',
                        'night_mode',
                    ],
                ],
                'state' => [
                    'available' => true,
                ],
            ],
            [
                'capability' => 'water_level_sensor',
                'config' => [
                    'label' => 'Water Level Sensor',
                    'hardware' => 'capacitive_sensor',
                    'used_for' => 'dry_run_protection',
                    'low_water_threshold_percent' => 20,
                ],
                'state' => [
                    'available' => true,
                    'water_low' => null,
                    'water_level_percent' => null,
                ],
            ],
        ]);

        $this->syncOutputs($device, [
            [
                'key' => 'pump',
                'type' => 'pwm_motor',
                'name' => 'Water Pump',
                'config' => [
                    'min_percent' => 0,
                    'max_percent' => 100,
                    'safe_default_enabled' => false,
                    'requires_water_level_ok' => true,
                ],
                'state' => [
                    'enabled' => false,
                    'speed_percent' => 0,
                ],
            ],
            [
                'key' => 'cob_light',
                'type' => 'pwm_light',
                'name' => 'COB Ring Light',
                'config' => [
                    'min_percent' => 0,
                    'max_percent' => 100,
                    'safe_default_enabled' => false,
                ],
                'state' => [
                    'enabled' => false,
                    'brightness_percent' => 0,
                ],
            ],
            [
                'key' => 'rgb_light',
                'type' => 'rgb_light',
                'name' => 'Decorative RGB Light',
                'config' => [
                    'supported_effects' => [
                        'solid',
                        'breathing',
                        'slow_rainbow',
                        'warm_glow',
                        'water_shimmer',
                        'night_mode',
                    ],
                ],
                'state' => [
                    'enabled' => false,
                    'brightness_percent' => 0,
                    'color' => '#FFB066',
                    'effect' => 'warm_glow',
                ],
            ],
        ]);
    }

    private function syncCapabilities(Device $device, array $capabilities): void
    {
        foreach ($capabilities as $capability) {
            $device->capabilities()->updateOrCreate(
                [
                    'capability' => $capability['capability'],
                ],
                [
                    'config' => $capability['config'] ?? null,
                    'state' => $capability['state'] ?? null,
                ]
            );
        }
    }

    private function syncOutputs(Device $device, array $outputs): void
    {
        foreach ($outputs as $output) {
            $device->outputs()->updateOrCreate(
                [
                    'key' => $output['key'],
                ],
                [
                    'type' => $output['type'],
                    'name' => $output['name'],
                    'config' => $output['config'] ?? null,
                    'state' => $output['state'] ?? null,
                    'last_changed_source' => 'system',
                    'last_changed_at' => now(),
                ]
            );
        }
    }
}
