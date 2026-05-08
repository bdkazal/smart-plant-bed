# Biztola IoT Platform / Smart Plant Bed MVP

This Laravel project is evolving from a single **Smart Plant Bed** backend into the shared **Biztola IoT Platform**.

The platform should support multiple Biztola IoT products under one Laravel app, including:

- Smart Plant Bed
- Smart Planter
- Smart Fountain
- Soil Monitor modules/probes
- Fan & Light Controller
- Smart Bathroom Controller
- future Biztola devices

The original Smart Plant Bed watering system remains the first working product, but new development should follow the platform model: one user can own many devices, devices can have different capabilities, and devices may later interact with each other.

---

## Critical Platform Rule: Device Status vs Connection Status

Do **not** confuse account/device lifecycle status with live hardware connection status.

### 1. Device lifecycle / account status

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

Meaning:

- `claimed_pending_wifi` = user claimed the device, but setup is not complete yet.
- `active` = the device is enabled in the user's account and should appear as an active product in **My Devices**.
- `deactivated` = user temporarily disabled the device.
- future transfer/unclaim status = device is removed or prepared for another owner.

Important:

```text
active does NOT mean the physical hardware is online right now.
```

A device can be **Active** in the device list while still being **Offline** on its dashboard.

### 2. Connection / online status

Calculated from:

```text
last_seen_at
```

The app treats a device as online only if it contacted Laravel recently through an authenticated API request, such as heartbeat, readings, command polling, command acknowledgement, or state sync.

Example logic:

```php
$device->last_seen_at?->gt(now()->subSeconds(20)) ?? false;
```

Meaning:

- `Online` = hardware contacted Laravel recently.
- `Offline` = hardware has not contacted Laravel recently.

Correct expected UI behavior:

```text
My Devices page: Active
Device dashboard: Offline
```

This means the device belongs to the user and is enabled in the account, but the hardware is not currently connected to Laravel.

---

## Claim → Setup → Active Flow

The existing app already follows a claim/setup design.

When a user claims a real device, Laravel sets:

```php
'status' => 'claimed_pending_wifi',
'provisioning_token' => Str::random(64),
'provisioning_expires_at' => now()->addMinutes(30),
```

The user is redirected to the setup page.

Expected lifecycle:

```text
pre-created / unclaimed device
→ claimed_pending_wifi
→ active
```

For Laravel-side development before hardware exists, test devices may be created with Tinker/Postman and manually set to:

```php
App\Models\Device::find($id)->update(['status' => 'active']);
```

This is acceptable during backend development. Hardware connection can be tested later with heartbeat/state API calls.

---

## Critical Platform Rule: Timed Action Devices vs Persistent State Devices

The platform must not treat every product like the Smart Plant Bed.

There are two main control behavior types.

### 1. Timed Action Devices

Used by products such as:

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

Plant Bed example:

```text
Water for 30 seconds.
Do not exceed 300 seconds.
Do not water again for 60 minutes after an auto run.
```

These products need:

```text
duration_seconds
max_runtime_seconds
cooldown_minutes
safety stop
watering/action logs
auto trigger protection
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

So for Plant Bed, `valve_on` becoming `executed` means the watering run is done and the valve/pump has been turned off after the requested duration.

### 2. Persistent State Devices

Used by products such as:

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

These products generally do **not** use Plant Bed-style runtime duration/cooldown for normal operation.

They need:

```text
current output state
manual on/off
schedule time ranges
sensor automation
scene apply
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

Example:

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

For a Smart Fountain, when this command becomes `executed`, it means:

```text
the device applied pump ON at 60%
```

The pump should continue running until another command changes the state:

```json
{
  "command_type": "output_set",
  "payload": {
    "output": "pump",
    "state": {
      "enabled": false,
      "speed_percent": 0
    },
    "source": "dashboard"
  }
}
```

### Schedule difference

Plant Bed schedule:

```text
Monday 06:00
Water for 30 seconds
```

This is an instant trigger with a duration.

Smart Fountain / Fan / Bathroom schedule:

```text
Every day 06:00 to 20:00
Apply daytime scene/state
After 20:00 apply off/night scene
```

This is a time range / state range.

Future generic schedule design should support at least:

```text
timed_action
state_range
```

### Bathroom Controller difference

A Smart Bathroom Controller is still mostly a persistent state device, but it has sensor automation rules on top.

Outputs may include:

```text
bathroom_light
exhaust_fan
night_light
optional_rgb_light
```

Sensors/readings may include:

```text
presence
humidity
temperature
ambient_light
```

Automation examples:

```text
Human detected → light ON
No human for 5 minutes → light OFF
Humidity >= 75% → exhaust fan ON
Humidity <= 60% for 5 minutes → exhaust fan OFF
```

The auto-off is not the same as Plant Bed's watering runtime. It is a later automation decision that sends another `output_set` command to turn the output off.

---

## Smart Fountain Command Findings

These rules were confirmed while testing Smart Fountain with the dashboard and Postman as a mock device.

### 1. Dashboard commands are requests, not proof of hardware state

When the user submits a Smart Fountain control form, Laravel should create a pending command:

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

This keeps Smart Fountain closer to the Plant Bed pattern where the dashboard shows command progress instead of pretending the action already happened.

### 2. Failed and expired commands are closed

If a Smart Fountain command fails or expires:

```text
old failed/expired command is not replayed later
user must send a new command if still needed
```

This is safer than automatically running old commands when a device reconnects later.

Example:

```text
User requested pump ON.
Device missed the command.
Later the water level may be low.
Laravel must not auto-run the old pump command.
```

### 3. `executed` can update confirmed output state for V1

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

This updates `device_outputs.state` using the command payload.

A real firmware can still send `/api/device/state` after that to sync the full actual hardware state and readings.

Recommended real-device flow:

```text
1. Poll pending command
2. ACK acknowledged
3. Apply output locally
4. ACK executed
5. POST /api/device/state with actual outputs/readings
```

### 4. Dashboard should show simple command status near each control

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

This follows the same practical idea as the Plant Bed dashboard showing:

```text
Idle
Waiting
Watering
Stopping
```

### 5. Auto-refresh is required

Smart Fountain dashboard should auto-refresh like Plant Bed.

Current Smart Fountain web status route:

```http
GET /devices/{device}/smart-fountain/status
```

The dashboard should poll it periodically to update:

```text
online/offline
last seen
water safety
output states
last command status badges
```

---

## Platform Foundation Direction

New products should not be implemented by hardcoding every feature into the `devices` table or by creating a separate Laravel app per product.

Use the platform concepts:

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

### Platform Readings

Stored in:

```text
device_readings
```

This generic table is for future platform-level readings.

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

---

## Current Project Status

This project is currently in **active MVP backend development**.

The Laravel side already includes:

- user authentication
- device claiming / ownership flow
- device dashboard pages
- sensor reading upload
- manual watering commands
- scheduled watering
- auto watering by soil moisture threshold
- command lifecycle handling
- command timeout / expiry handling
- schedule CRUD
- timezone-aware schedules
- basic online / offline device presence using `last_seen_at`
- early IoT platform foundation models/tables:
  - `device_capabilities`
  - `device_outputs`
  - `device_readings`
  - `devices.last_reported_state`
- early Smart Fountain dashboard using generic `output_set` commands
- Smart Fountain platform config API
- Smart Fountain platform state sync API
- Smart Fountain dashboard auto-refresh/status endpoint

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

## Current Watering Model

The Smart Plant Bed currently uses two separate concepts:

### 1. Automation Mode

This is the configured operating mode:

```text
auto
schedule
```

### 2. Watering State

This is the current watering action state shown on the dashboard:

```text
idle
waiting
watering
stopping
```

Manual watering is **not** a persistent mode. Manual watering is a direct user action / trigger source.

---

## Current Command Lifecycle

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

These closed states cannot be changed later by a reconnecting device.

Command status meaning depends on device behavior type:

```text
Timed action device:
executed = timed action completed

Persistent state device:
executed = requested state applied
```

---

## Online / Offline Presence

The system currently uses **device-to-server contact freshness**, not direct device IP checks.

The device is considered online when it contacts the server recently through endpoints such as:

- sensor reading upload
- command polling
- command acknowledgement
- heartbeat endpoint
- state sync endpoint

This is the current MVP presence strategy before websocket/MQTT complexity.

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

Important unfinished areas:

### 1. Shared Command Lifecycle Service

Command cleanup/timeout logic should be moved into a shared service.

Current risk:

```text
web controller cleanup and API controller cleanup can drift apart
```

Recommended future service:

```text
app/Services/DeviceCommandLifecycleService.php
```

Use it from:

```text
DeviceController
DeviceCommandController
DeviceReadingController or future automation services
```

### 2. Platform State Sync

For new persistent state devices, `/api/device/state` supports platform-style output states and generic readings.

Desired Smart Fountain state shape:

```json
{
  "device_uuid": "...",
  "device_type": "smart_fountain",
  "firmware_version": "fountain-dev-0.1",
  "operation_state": "running",
  "outputs": {
    "pump": {
      "enabled": true,
      "speed_percent": 60,
      "source": "dashboard"
    },
    "cob_light": {
      "enabled": true,
      "brightness_percent": 30,
      "source": "dashboard"
    },
    "rgb_light": {
      "enabled": true,
      "brightness_percent": 40,
      "color": "#FFB066",
      "effect": "warm_glow",
      "source": "dashboard"
    }
  },
  "readings": {
    "water_level_percent": {
      "value": 75,
      "unit": "percent"
    },
    "water_low": {
      "value": 0,
      "unit": "boolean"
    }
  }
}
```

### 3. Desired State vs Reported State

Do not expose these words to normal users, but the backend may eventually need separate state fields:

```text
desired_state = what Laravel/user/schedule requested
reported_state = what device confirmed as actual hardware state
```

For V1, the command lifecycle plus `/api/device/state` is enough. Before serious production hardware, consider adding explicit desired/reported state separation if command/state mismatch becomes common.

### 4. Generic Schedules / Scenes

Plant Bed currently has watering schedules. Persistent state devices need state-range schedules and scenes.

Future direction:

```text
device_schedules
device_scenes
device_automation_rules
```

### 5. ESP32 Runtime Design Per Product

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
- show simple user-facing command states, not internal state jargon
- use capabilities/outputs/readings for new device types
- keep existing Plant Bed functionality working while adding platform features
