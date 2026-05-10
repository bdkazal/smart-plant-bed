# Platform Rules

These rules protect the project from mixing product-specific behavior incorrectly.

## Device status vs connection status

Do not confuse account/device lifecycle status with live hardware connection status.

Lifecycle/account status is stored in:

```text
devices.status
```

Common values:

```text
claimed_pending_wifi
active
deactivated
unclaimed / transferred later
```

Important:

```text
active does NOT mean the physical hardware is online right now.
```

Connection/online status is calculated from:

```text
last_seen_at
```

Current MVP online check:

```php
$device->last_seen_at?->gt(now()->subSeconds(20)) ?? false;
```

Expected UI behavior:

```text
My Devices page: Active
Device dashboard: Offline
```

This means the device belongs to the user and is enabled in Laravel, but the hardware is not currently connected.

## Claim → setup → active flow

Expected lifecycle:

```text
pre-created / unclaimed device
→ claimed_pending_wifi
→ active
```

For Laravel-side development before hardware exists, test devices may be created with Tinker/Postman and manually activated:

```php
App\Models\Device::find($id)->update(['status' => 'active']);
```

## Timed action devices vs persistent state devices

The platform must not treat every product like the Smart Plant Bed.

### Timed action devices

Examples:

- Smart Plant Bed
- Smart Planter watering
- future fertilizer/dosing devices

Behavior:

```text
start action
run for a duration
stop automatically
respect safety max runtime
respect cooldown before next automatic run
```

Current Plant Bed commands:

```text
valve_on
valve_off
```

For timed action devices:

```text
executed = the timed action completed
```

For Plant Bed, `valve_on` becoming `executed` means the watering run is done and the valve/pump has been turned off after the requested duration.

### Persistent state devices

Examples:

- Smart Fountain
- Fan & Light Controller
- Smart Bathroom Controller outputs
- RGB/light/pump/fan devices

Behavior:

```text
set output state
keep that state until another command changes it
```

Generic platform commands:

```text
output_set
scene_apply
```

For persistent state devices:

```text
executed = the requested state was applied
```

It does not mean the output finished running.

## Plant Bed isolation rule

Smart Fountain additions must not change existing Plant Bed behavior.

Plant Bed uses:

```text
valve_on / valve_off commands
watering_logs
sensor_readings
Plant Bed automation page
Plant Bed watering schedules
Plant Bed dashboard/status flow
```

Smart Fountain uses:

```text
output_set / scene_apply commands
device_outputs
device_readings
device_scenes
device_schedule_ranges
Smart Fountain Home / Scenes / Schedule / History tabs
Smart Fountain status endpoint
```

Implementation rules:

```text
Do not make Plant Bed pages show Smart Fountain scenes.
Do not make Smart Fountain pages show Plant Bed automation or watering schedules.
Do not make platform commands update watering_logs.
Do not replay failed/expired commands when a device reconnects.
```

## Shared history page product awareness

Plant Bed:

- Sensor Readings from `sensor_readings`
- Watering Activity from `watering_logs`
- Device Actions from `device_commands`

Smart Fountain:

- Device Readings from `device_readings`
- Device Actions from `device_commands`

## Command lifecycle

The backend supports:

```text
pending
acknowledged
executed
failed
expired
```

Current rules:

```text
pending -> acknowledged
pending -> failed
acknowledged -> executed
acknowledged -> failed
```

Closed commands are protected from late updates:

```text
expired
failed
executed
```

Closed commands are not replayed automatically when a device reconnects.

## Shared command lifecycle service

Command cleanup/timeout logic is centralized in:

```text
app/Services/DeviceCommandLifecycleService.php
```

Used from:

```text
DeviceController
DeviceCommandController
DeviceReadingController
SmartFountainStatusController
```

Current timeout behavior:

```text
pending valve_on       → expired after 60s
pending valve_off      → expired after 60s
pending output_set     → expired after 60s
pending scene_apply    → expired after 60s

acknowledged valve_on    → failed after duration_seconds + 60s
acknowledged valve_off   → failed after 60s
acknowledged output_set  → failed after 60s
acknowledged scene_apply → failed after 90s
```

Only Plant Bed watering commands update `watering_logs`:

```text
valve_on
valve_off
```

Platform commands such as `output_set` and `scene_apply` do not update watering logs.
