<?php

namespace App\Services;

use App\Models\Device;

class SmartFountainScenePresetService
{
    public function ensureDefaultScenes(Device $device): void
    {
        if (! $device->isSmartFountain()) {
            return;
        }

        if ($device->scenes()->exists()) {
            return;
        }

        foreach ($this->defaultScenes() as $scene) {
            $device->scenes()->create($scene);
        }
    }

    public function defaultScenes(): array
    {
        return [
            [
                'name' => 'Day Fountain',
                'outputs' => [
                    'pump' => [
                        'enabled' => true,
                        'speed_percent' => 60,
                    ],
                    'cob_light' => [
                        'enabled' => true,
                        'brightness_percent' => 40,
                    ],
                    'rgb_light' => [
                        'enabled' => true,
                        'brightness_percent' => 35,
                        'color' => '#FFB066',
                        'effect' => 'warm_glow',
                    ],
                ],
                'is_default' => true,
            ],
            [
                'name' => 'Night Glow',
                'outputs' => [
                    'pump' => [
                        'enabled' => false,
                        'speed_percent' => 0,
                    ],
                    'cob_light' => [
                        'enabled' => false,
                        'brightness_percent' => 0,
                    ],
                    'rgb_light' => [
                        'enabled' => true,
                        'brightness_percent' => 12,
                        'color' => '#335CFF',
                        'effect' => 'night_mode',
                    ],
                ],
                'is_default' => true,
            ],
            [
                'name' => 'Display Mode',
                'outputs' => [
                    'pump' => [
                        'enabled' => true,
                        'speed_percent' => 50,
                    ],
                    'cob_light' => [
                        'enabled' => true,
                        'brightness_percent' => 25,
                    ],
                    'rgb_light' => [
                        'enabled' => true,
                        'brightness_percent' => 45,
                        'color' => '#40CFFF',
                        'effect' => 'water_shimmer',
                    ],
                ],
                'is_default' => true,
            ],
            [
                'name' => 'All Off',
                'outputs' => [
                    'pump' => [
                        'enabled' => false,
                        'speed_percent' => 0,
                    ],
                    'cob_light' => [
                        'enabled' => false,
                        'brightness_percent' => 0,
                    ],
                    'rgb_light' => [
                        'enabled' => false,
                        'brightness_percent' => 0,
                        'color' => '#000000',
                        'effect' => 'solid',
                    ],
                ],
                'is_default' => true,
            ],
        ];
    }
}
