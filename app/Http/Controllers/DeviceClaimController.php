<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DeviceClaimController extends Controller
{
    public function create(): View
    {
        return view('devices.add');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'claim_code' => ['required', 'string', 'max:20'],
        ]);

        $claimCode = strtoupper(trim($validated['claim_code']));

        $device = Device::where('claim_code', $claimCode)->first();

        if (!$device) {
            return back()
                ->withErrors(['claim_code' => 'Invalid claim code.'])
                ->withInput();
        }

        if ($device->user_id) {
            return back()
                ->withErrors(['claim_code' => 'This device has already been claimed.'])
                ->withInput();
        }

        $this->claimDeviceForCurrentUser($device);

        return redirect()
            ->route('devices.setup', $device)
            ->with('success', 'Device claimed successfully. Please continue with Wi-Fi setup.');
    }

    public function show(string $code): View|RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->guest(route('login'));
        }

        $claimCode = strtoupper(trim($code));

        $device = Device::where('claim_code', $claimCode)->first();

        if (!$device) {
            return redirect()
                ->route('devices.add')
                ->withErrors(['claim_code' => 'Invalid claim code.']);
        }

        if ($device->user_id) {
            return redirect()
                ->route('devices.add')
                ->withErrors(['claim_code' => 'This device has already been claimed.']);
        }

        return view('devices.claim', compact('device'));
    }

    public function confirm(Request $request, string $code): RedirectResponse
    {
        $claimCode = strtoupper(trim($code));

        $device = Device::where('claim_code', $claimCode)->first();

        if (!$device) {
            return redirect()
                ->route('devices.add')
                ->withErrors(['claim_code' => 'Invalid claim code.']);
        }

        if ($device->user_id) {
            return redirect()
                ->route('devices.add')
                ->withErrors(['claim_code' => 'This device has already been claimed.']);
        }

        $this->claimDeviceForCurrentUser($device);

        return redirect()
            ->route('devices.setup', $device)
            ->with('success', 'Device claimed successfully. Please continue with Wi-Fi setup.');
    }

    public function setup(Device $device): View|RedirectResponse
    {
        $user = Auth::user();

        if (!$user || $device->user_id !== $user->id) {
            abort(403);
        }

        return view('devices.setup', compact('device'));
    }

    private function claimDeviceForCurrentUser(Device $device): void
    {
        $user = Auth::user();

        if (!$user) {
            abort(403);
        }

        $device->update([
            'user_id' => $user->id,
            'claimed_at' => now(),
            'status' => 'claimed_pending_wifi',
            'provisioning_token' => Str::random(64),
            'provisioning_expires_at' => now()->addMinutes(30),
        ]);
    }
}
