<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DeviceProfileController extends Controller
{
    public function edit(Device $device): View
    {
        $this->ensureOwner($device);

        $timezoneOptions = timezone_identifiers_list();

        return view('devices.profile.edit', compact('device', 'timezoneOptions'));
    }

    public function update(Request $request, Device $device): RedirectResponse
    {
        $this->ensureOwner($device);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'location_label' => ['nullable', 'string', 'max:100'],
            'timezone' => ['required', 'timezone'],
        ]);

        $device->update([
            'name' => $validated['name'],
            'location_label' => $validated['location_label'] ?: null,
            'timezone' => $validated['timezone'],
        ]);

        return redirect()
            ->route('devices.show', $device)
            ->with('success', 'Device settings updated successfully.');
    }

    private function ensureOwner(Device $device): void
    {
        if (Auth::id() !== $device->user_id) {
            abort(403);
        }
    }
}
