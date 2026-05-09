# Biztola IoT Platform / Smart Plant Bed MVP

This Laravel project is evolving from a single **Smart Plant Bed** backend into the shared **Biztola IoT Platform**.

The goal is one Laravel app where a user can own and manage many Biztola IoT products, including:

- Smart Plant Bed
- Smart Planter
- Smart Fountain
- Soil Monitor modules/probes
- Fan & Light Controller
- Smart Bathroom Controller
- future Biztola devices

The original Smart Plant Bed watering system remains the first working product, but new development should follow the platform model: devices have types, capabilities, outputs, readings, commands, and product-specific behavior.

---

## Critical Platform Rule: Device Status vs Connection Status

Do **not** confuse account/device lifecycle status with live hardware connection status.

### Device lifecycle / account status

Stored in:

```text
devices.status
```

This describes the user's relationship with the device inside Laravel.

Common statuses:

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

A device can be **Active** in the My Devices page while still being **Offline** on its dashboard.

### Connection / online status

Calculated from:

```text
last_seen_at
```

The app treats a device as online only if it contacted Laravel recently through an authenticated API request, such as heartbeat, readings, command polling, command acknowledgement, or state sync.

Current MVP online check:

```php
$device->last_seen_at?->gt(now()->subSeconds(20)) ?? false;
```

Correct expected UI behavior:

```text
My Devices page: Active
Device dashboard: Offline
```

This means the device belongs to the user and is enabled in the account, but the hardware is not currently connected to Laravel.

---

## Claim → Setup → Active Flow

The existing app follows a claim/setup design.

Expected lifecycle:

```text
pre-created / unclaimed device
→ claimed_pending_wifi
→ active
```

When a user claims a real device, Laravel can set:

```php
'status' => 'claimed_pending_wifi',
'provisioning_token' => Str::random(64),
'provisioning_expires_at' => now()->addMinutes(30),
```

For Laravel-side development before hardware exists, test devices may be created with Tinker/Postman and manually activated:

```php
App\Models\Device::find($id)->update(['status' => 'active']);
```

---

## Timed Action Devices vs Persistent State Devices

The platform must not treat every product like the Smart Plant Bed.

### Timed Action Devices

Examples:

```text
Smart Plant Bed
Smart Planter watering
future fertilizer/dosing devices
```

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

### Persistent State Devices

Examples:

```text
Smart Fountain
Fan & Light Controller
Smart Bathroom Controller outputs
RGB/light/pump/fan devices
```

Behavior:

```text
set output state
keep that state until another command changes it
```

Examples:

```text
Pump ON at 60%
Light ON at 40%
Fan ON
RGB warm_glow
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

It does **not** mean the output finished running.

Example `output_set` command:

```json
{
  "command_type": "output_set",
  "payload": {
    "output": "pump",
    "state": {
      "enabled": true,
      "speed_percent": 60
    },
    "source": "dashboard"
  }
}
```

For a Smart Fountain, when this command becomes `executed`, it means the device applied pump ON at 60%. The pump continues running until another command changes the state.

---

## Command Lifecycle

The backend supports this command lifecycle:

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

Closed commands are not replayed automatically when a device reconnects. This is important for safety.

Example:

```text
User requested pump ON.
Device missed the command.
Later the water level may be low.
Laravel must not auto-run the old pump command.
```

### Shared command lifecycle service

Command cleanup/timeout logic is centralized in:

```text
app/Services/DeviceCommandLifecycleService.php
```

Used from:

```text
DeviceController
DeviceCommandController
DeviceReadingController
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

---

## Smart Fountain Command Findings

These rules were confirmed while testing Smart Fountain with the dashboard and Postman as a mock device.

### Dashboard command = request, not proof of hardware state

When the user submits a Smart Fountain control form, Laravel creates a pending command:

```text
command_type = output_set
status = pending
```

The dashboard must not treat that request as proof that the real pump/light already changed.

Current intended flow:

```text
Dashboard sends command
→ Laravel creates pending output_set
→ Device polls command
→ Device acknowledges command
→ Device executes command
→ Laravel applies the output state
→ Dashboard auto-refresh shows the new state
```

### V1 executed ACK behavior

For Smart Fountain `output_set`, an executed ACK means the device says the requested state was applied.

For V1/Postman testing, Laravel applies the output state when the device sends:

```json
{
  "device_uuid": "...",
  "status": "executed"
}
```

to:

```http
POST /api/device/commands/{command}/ack
```

A real firmware should still send `/api/device/state` after that to sync full actual hardware state and readings.

Recommended real-device flow:

```text
1. Poll pending command
2. ACK acknowledged
3. Apply output locally
4. ACK executed
5. POST /api/device/state with actual outputs/readings
```

### Dashboard UX

Do not expose internal terms like `desired_state` and `reported_state` to normal users.

User-facing labels should stay simple:

```text
Waiting for device
Applying
Applied
Failed
Expired
```

Smart Fountain control cards should show:

```text
Current State
Source
Last Command
Control inputs
Send button
```

The dashboard should auto-refresh like Plant Bed using:

```http
GET /devices/{device}/smart-fountain/status
```

Smart Fountain currently has ready tabs:

```text
Home
History
```

Automation and Schedules are intentionally hidden until Smart Fountain-specific behavior is designed.

---

## Platform Concepts

### Device

A physical product owned by a user.

Common fields live in `devices`, such as:

- user ownership
- name
- device type
- uuid
- api key
- claim code
- lifecycle status
- location
- timezone
- firmware version
- last seen time
- last reported state JSON

### Capabilities

Stored in:

```text
device_capabilities
```

Capabilities describe what a device can do or sense.

Examples:

```text
pump_output
dimmable_light
rgb_light
water_level_sensor
soil_moisture_sensor
battery_status
relay_output
humidity_sensor
presence_sensor
```

### Outputs

Stored in:

```text
device_outputs
```

Outputs are controllable parts of a device.

Examples:

```text
pump
cob_light
rgb_light
valve
bathroom_light
exhaust_fan
```

Each output can store JSON config and JSON state.

### Device Readings

Stored in:

```text
device_readings
```

This generic table is for platform-level readings.

Examples:

```text
soil_moisture
water_level_percent
water_low
temperature
humidity
battery_percent
battery_voltage
presence
ambient_light
```

The older `sensor_readings` table still exists for the current Plant Bed flow and should not be removed until the platform migration is mature.

---

## `/api/device/state` Contract

Endpoint:

```http
POST /api/device/state
```

Meaning:

```text
The device reports its latest actual hardware condition to Laravel.
```

For persistent state devices such as Smart Fountain, the request can include:

```json
{
  "device_uuid": "...",
  "device_type": "smart_fountain",
  "firmware_version": "fountain-dev-0.1",
  "operation_state": "running",
  "last_completed_command_id": 233,
  "outputs": {
    "pump": {
      "enabled": true,
      "speed_percent": 70,
      "source": "dashboard"
    }
  },
  "readings": {
    "water_level_percent": {
      "value": 80,
      "unit": "percent"
    },
    "water_low": {
      "value": 0,
      "unit": "boolean"
    }
  }
}
```

Response is now device/request-specific. Smart Fountain does not receive unnecessary Plant Bed legacy fields.

Example Smart Fountain response:

```json
{
  "message": "Device state synced successfully.",
  "device_id": 2,
  "device_type": "smart_fountain",
  "state": {
    "operation_state": "running",
    "last_reported_at": "2026-05-09 21:35:31",
    "outputs": []
  },
  "accepted_completed_command_id": null,
  "platform_outputs_updated": 1,
  "device_readings_stored": 2
}
```

Meaning:

```text
platform_outputs_updated = number of outputs updated from the outputs payload
device_readings_stored  = number of generic device_readings rows stored
accepted_completed_command_id = command completed by this state sync, or null if already closed/not found
```

Plant Bed legacy sensor response fields only appear if the request includes legacy Plant Bed fields:

```text
temperature
humidity
soil_moisture
```

Those legacy response fields are:

```text
legacy_sensor_reading_stored
legacy_sensor_reading_id
```

They should not appear in normal Smart Fountain state-sync responses.

---

## Smart Fountain Platform Notes

The Smart Fountain uses the persistent state platform model.

Device type:

```text
smart_fountain
```

Default capabilities:

```text
pump_output
dimmable_light
rgb_light
water_level_sensor
```

Default outputs:

```text
pump
cob_light
rgb_light
```

Important water-level note:

The Smart Fountain may physically use a capacitive soil moisture sensor, but in Laravel its product role is:

```text
water_level_sensor
```

Do not store this as `soil_moisture` for the fountain, because the measurement meaning is water-level / dry-run protection, not soil moisture.

Recommended Smart Fountain readings:

```text
water_level_percent
water_level_raw
water_low
```

If `water_low` is true, the pump should remain off to protect the motor.

Smart Fountain history currently uses the shared history page:

```http
GET /devices/{device}/history
```

For Smart Fountain, the shared history page shows:

```text
Device Readings
Device Commands
```

For Plant Bed, the same history page shows:

```text
Watering Logs
Device Commands
```

---

## Current Project Status

This project is currently in **active MVP backend development**.

The Laravel side already includes:

- user authentication
- device claiming / ownership flow
- device dashboard pages
- sensor reading upload
- manual watering commands
- scheduled watering for Plant Bed
- auto watering by soil moisture threshold for Plant Bed
- command lifecycle handling
- shared command timeout / expiry handling service
- schedule CRUD for Plant Bed
- timezone-aware schedules for Plant Bed
- basic online / offline device presence using `last_seen_at`
- early IoT platform foundation models/tables:
  - `device_capabilities`
  - `device_outputs`
  - `device_readings`
  - `devices.last_reported_state`
- Smart Fountain dashboard using generic `output_set` commands
- Smart Fountain platform config API
- Smart Fountain platform state sync API
- Smart Fountain dashboard auto-refresh/status endpoint
- shared history page that adapts by device type

The ESP32/device-side runtime logic is designed after the Laravel/API contract is stable.

---

## Original Smart Plant Bed Product Goal

Build a simple, reliable, commercial-friendly smart watering system that allows a customer to:

- monitor plant-bed conditions remotely
- water manually from a dashboard
- automate watering by schedule
- automate watering by soil moisture threshold

The MVP should stay simple, practical, and expandable.

---

## Current Plant Bed Watering Model

The Smart Plant Bed currently uses two separate concepts:

### Automation Mode

```text
auto
schedule
```

### Watering State

```text
idle
waiting
watering
stopping
```

Manual watering is **not** a persistent mode. Manual watering is a direct user action / trigger source.

---

## API Endpoints

The current backend/device contract uses:

```http
GET  /api/device/config
POST /api/device/readings
GET  /api/device/commands
POST /api/device/commands/{command}/ack
POST /api/device/heartbeat
POST /api/device/state
```

Device authentication uses:

```http
X-DEVICE-KEY: <device_api_key>
```

The device identifies itself using:

```json
{
  "device_uuid": "..."
}
```

---

## Known Incomplete Areas

### 1. Generic schedules / scenes

Plant Bed currently has watering schedules. Persistent state devices need state-range schedules and scenes.

Future direction:

```text
device_schedules
device_scenes
device_automation_rules
```

Schedule difference:

```text
Plant Bed:
Monday 06:00 → Water for 30 seconds

Smart Fountain / Fan / Bathroom:
Every day 06:00 to 20:00 → Apply daytime scene/state
After 20:00 → Apply off/night scene
```

### 2. Desired State vs Reported State

Do not expose these words to normal users, but the backend may eventually need separate state fields:

```text
desired_state = what Laravel/user/schedule requested
reported_state = what device confirmed as actual hardware state
```

For V1, the command lifecycle plus `/api/device/state` is enough. Before serious production hardware, consider adding explicit desired/reported state separation if command/state mismatch becomes common.

### 3. Output event history

Smart Fountain currently stores current output state and command history.

It does not yet store a separate event log such as:

```text
Pump changed to ON 64% by dashboard
COB light changed to 35%
RGB changed to warm_glow
Pump forced OFF by water safety
```

This may later need:

```text
device_output_events
```

### 4. ESP32 Runtime Design Per Product

The detailed device-side control loop should be designed per product after the Laravel/API contract is stable.

---

## Future Possibilities

Not MVP priorities, but possible later:

- multiple devices per user
- charts and analytics
- notifications / alerts
- tank level monitoring
- fertilizer control
- rain-aware schedule suppression
- light sensor support
- PWA improvements
- MQTT / realtime transport
- reconnect state sync improvements
- admin dashboard
- billing / subscriptions
- device-to-device automation rules
- reusable scene engine
- generic schedule engine

---

## Tech Stack

### Backend

- Laravel
- MySQL
- Blade
- REST-style device API

### Device

- ESP32 / ESP32-C3 depending on product
- sensor modules
- relay/MOSFET/output drivers as needed

### Hosting

- VPS / custom domain

---

## Development Notes

This project is being built as a **commercial-minded MVP**, not as a hobby-only experiment.

Priority order remains:

1. reliable core behavior
2. clear device/server contract
3. simple customer UX
4. clean future expansion path

When opening a new thread for a new device, preserve these platform rules:

- one Laravel app acts as the Biztola IoT Platform
- do not design a separate product-specific Laravel app
- use `devices.status` for account/device lifecycle
- use `last_seen_at` freshness for online/offline connection state
- distinguish timed action devices from persistent state devices
- use `executed = completed` only for timed action devices
- use `executed = state applied` for persistent state devices
- keep failed/expired commands closed; do not replay them automatically on reconnect
- use the shared command lifecycle service for timeout/cleanup behavior
- show simple user-facing command states, not internal state jargon
- use capabilities/outputs/readings for new device types
- make API responses device/request-specific; do not return unrelated Plant Bed fields for new devices
- keep existing Plant Bed functionality working while adding platform features
