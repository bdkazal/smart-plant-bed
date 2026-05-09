<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceCommand;
use App\Models\DeviceScene;
use App\Services\SmartFountainScenePresetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SmartFountainSceneController extends Controller
{
    public function index(Device $device): View
    {
        $this->authorizeSmartFountain($device);

        app(SmartFountainScenePresetService::class)->ensureDefaultScenes($device);

        $device->load([
            'scenes' => fn ($query) => $query->latest(),
        ]);

        return view('devices.smart-fountain.scenes.index', compact('device'));
    }

    public function create(Device $device): View
    {
        $this->authorizeSmartFountain($device);

        return view('devices.smart-fountain.scenes.form', [
            'device' => $device,
            'scene' => null,
            'outputs' => $this->defaultOutputs(),
        ]);
    }

    public function store(Request $request, Device $device): RedirectResponse
    {
        $this->authorizeSmartFountain($device);

        $device->scenes()->create([
            'name' => $request->validate([
                'name' => ['required', 'string', 'max:100', 'unique:device_scenes,name,NULL,id,device_id,' . $device->id],
            ])['name'],
            'outputs' => $this->validatedOutputs($request),
            'is_default' => false,
        ]);

        return redirect()
            ->route('devices.smart-fountain.scenes.index', $device)
            ->with('success', 'Scene created successfully.');
    }

    public function edit(Device $device, DeviceScene $scene): View
    {
        $this->authorizeScene($device, $scene);

        return view('devices.smart-fountain.scenes.form', [
            'device' => $device,
            'scene' => $scene,
            'outputs' => array_replace_recursive($this->defaultOutputs(), $scene->outputs ?? []),
        ]);
    }

    public function update(Request $request, Device $device, DeviceScene $scene): RedirectResponse
    {
        $this->authorizeScene($device, $scene);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:device_scenes,name,' . $scene->id . ',id,device_id,' . $device->id],
        ]);

        $scene->update([
            'name' => $validated['name'],
            'outputs' => $this->validatedOutputs($request),
        ]);

        return redirect()
            ->route('devices.smart-fountain.scenes.index', $device)
            ->with('success', 'Scene updated successfully.');
    }

    public function destroy(Device $device, DeviceScene $scene): RedirectResponse
    {
        $this->authorizeScene($device, $scene);

        $scene->delete();

        return redirect()
            ->route('devices.smart-fountain.scenes.index', $device)
            ->with('success', 'Scene deleted successfully.');
    }

    public function apply(Device $device, DeviceScene $scene): RedirectResponse
    {
        $this->authorizeScene($device, $scene);

        if ($device->status !== 'active') {
            return redirect()
                ->route('devices.smart-fountain.scenes.index', $device)
                ->withErrors([
                    'scene' => 'Scene can only be applied when the device is active in this account.',
                ]);
        }

        $outputs = collect($scene->outputs ?? [])
            ->filter(fn ($state, $outputKey) => is_string($outputKey) && is_array($state))
            ->filter(fn ($state, $outputKey) => $device->outputs()->where('key', $outputKey)->exists())
            ->all();

        if (empty($outputs)) {
            return redirect()
                ->route('devices.smart-fountain.scenes.index', $device)
                ->withErrors([
                    'scene' => 'This scene has no valid outputs to apply.',
                ]);
        }

        DeviceCommand::create([
            'device_id' => $device->id,
            'command_type' => 'scene_apply',
            'payload' => [
                'scene_id' => $scene->id,
                'scene_name' => $scene->name,
                'source' => 'scene:' . $scene->id,
                'outputs' => $outputs,
            ],
            'status' => 'pending',
            'issued_at' => now(),
        ]);

        return redirect()
            ->route('devices.show', $device)
            ->with('success', "Scene '{$scene->name}' queued successfully. Waiting for device confirmation.");
    }

    private function validatedOutputs(Request $request): array
    {
        $validated = $request->validate([
            'pump_enabled' => ['nullable', 'boolean'],
            'pump_speed_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'cob_light_enabled' => ['nullable', 'boolean'],
            'cob_brightness_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'rgb_light_enabled' => ['nullable', 'boolean'],
            'rgb_brightness_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'rgb_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'rgb_effect' => ['required', 'in:solid,breathing,slow_rainbow,warm_glow,water_shimmer,night_mode'],
        ]);

        return [
            'pump' => [
                'enabled' => $request->boolean('pump_enabled'),
                'speed_percent' => (int) $validated['pump_speed_percent'],
            ],
            'cob_light' => [
                'enabled' => $request->boolean('cob_light_enabled'),
                'brightness_percent' => (int) $validated['cob_brightness_percent'],
            ],
            'rgb_light' => [
                'enabled' => $request->boolean('rgb_light_enabled'),
                'brightness_percent' => (int) $validated['rgb_brightness_percent'],
                'color' => strtoupper($validated['rgb_color']),
                'effect' => $validated['rgb_effect'],
            ],
        ];
    }

    private function defaultOutputs(): array
    {
        return [
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
                'color' => '#FFB066',
                'effect' => 'warm_glow',
            ],
        ];
    }

    private function authorizeScene(Device $device, DeviceScene $scene): void
    {
        $this->authorizeSmartFountain($device);

        if ($scene->device_id !== $device->id) {
            abort(404);
        }
    }

    private function authorizeSmartFountain(Device $device): void
    {
        $user = Auth::user();

        if (! $user || $device->user_id !== $user->id) {
            abort(403);
        }

        if (! $device->isSmartFountain()) {
            abort(404);
        }
    }
}
