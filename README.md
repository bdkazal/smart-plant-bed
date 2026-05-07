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
users/devices ownership flow

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

This connection status belongs on the individual device dashboard.

### Correct UI behavior

On the **My Devices** list page:

```text
Smart Fountain Test 01
Status: Active
Last Seen: Never / 3 hours ago
```

This uses `devices.status`.

On the **device dashboard** page:

```text
Top badge: Online / Offline
Device Status card: Online / Offline
Last Seen: Never / time ago
```

This uses `last_seen_at` freshness.

Therefore, this is valid and expected:

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
```

The older `sensor_readings` table still exists for the current Plant Bed flow and should not be removed until the platform migration is mature.

---

## Smart Fountain Platform Notes

The Smart Fountain uses the platform model.

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

## Current MVP Scope

### Monitoring

- latest temperature
- latest humidity
- latest soil moisture
- device last seen time
- device online / offline status

### Manual Control

- start watering from dashboard
- stop watering from dashboard
- block manual control when device is offline

### Scheduled Watering

- create, edit, enable/disable, and delete schedules
- timezone-aware schedule execution
- schedule mode is separate from auto mode

### Auto Watering

- trigger watering when soil moisture goes below threshold
- configurable threshold
- configurable max watering duration
- configurable cooldown period
- auto mode requires a valid moisture reading

---

## Current Architecture

## Device Side

Planned ESP32 device responsibilities:

- read sensor values
- send sensor readings to Laravel API
- poll Laravel for pending commands
- acknowledge commands
- execute watering commands
- send final command result
- send heartbeat / presence updates
- cache last known config for offline-capable device behavior later

## Laravel Side

Laravel currently handles:

- user auth
- claimed device ownership
- dashboard pages
- device settings
- watering rules
- watering schedules
- sensor reading storage
- command creation
- command state transitions
- command timeout cleanup
- online / offline status using recent device contact

## Frontend

Current frontend is server-rendered Blade with light JavaScript polling for status refresh.

---

## Current Watering Model

The project currently uses two separate concepts:

### 1. Automation Mode

This is the configured operating mode:

- `auto`
- `schedule`

### 2. Watering State

This is the current watering action state shown on the dashboard:

- `idle`
- `waiting`
- `watering`
- `stopping`

Manual watering is **not** a persistent mode.  
Manual watering is a direct user action / trigger source.

---

## Current Command Lifecycle

The backend currently supports a stricter command lifecycle for watering commands.

### Command statuses

- `pending`
- `acknowledged`
- `executed`
- `failed`
- `expired`

### Current rules

- `pending -> acknowledged`
- `pending -> failed`
- `acknowledged -> executed`
- `acknowledged -> failed`

Closed commands are protected from late updates:

- `expired`
- `failed`
- `executed`

These closed states cannot be changed later by a reconnecting device.

### Timeout behavior

- `pending` commands expire after a short timeout if the device never confirms
- `acknowledged` commands fail after timeout if the device never reports completion

This protects the system from stale commands and broken device/network sessions.

---

## Online / Offline Presence

The system currently uses **device-to-server contact freshness**, not direct device IP checks.

The device is considered online when it contacts the server recently through endpoints such as:

- sensor reading upload
- command polling
- command acknowledgement
- heartbeat endpoint

The dashboard checks current status by polling Laravel periodically.

This is the current MVP presence strategy before websocket/MQTT complexity.

---

## Current Pages

### Device Home

Shows:

- online / offline
- latest reading
- watering state
- current automation mode
- manual start / stop controls
- started-by context when watering is active

### Automation

Shows:

- device settings
- timezone
- automation mode
- moisture threshold
- cooldown
- watering durations

### Schedules

Supports full CRUD for watering schedules.

### History

Shows:

- recent watering logs
- recent device commands
- paginated history

---

## API Endpoints (Current Direction)

### Device config

- device fetches latest config/rules/settings

### Device readings

- device posts temperature / humidity / soil moisture
- Laravel stores readings
- Laravel may trigger auto watering if conditions match

### Device commands

- device polls for pending commands
- device acknowledges command lifecycle updates

### Device heartbeat

- device updates its presence / last seen timestamp

---

## Current Business / Product Design Decisions

### Keep V1 simple

Do not overbuild the MVP.

### Manual control should feel reliable

If the device is offline, manual start/stop should not be offered as if it were immediately available.

### Auto and schedule are separate

The current design treats:

- auto mode
- schedule mode

as distinct automation modes.

### Manual control is always a direct user action

Manual is not stored as a persistent mode.

---

## Known Incomplete Areas

The Laravel side is strong enough for MVP progress, but the project is **not finished**.

Important unfinished areas:

### 1. Device State Sync / Reconnect Reconciliation

Goal:
When the device reconnects after being offline, Laravel should learn the device’s real current state again without blindly rewriting old closed command history.

This should be a separate state-sync flow, not a rewrite of closed command history.

### 2. ESP32 Runtime Design

The detailed device-side control loop still needs to be designed clearly:

- polling frequency
- heartbeat frequency
- ack flow
- execution flow
- offline fallback behavior
- local control rules

### 3. UI Cleanup / Product Polish

The dashboard is usable, but still needs polish and simplification.

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
- use capabilities/outputs/readings for new device types
- keep existing Plant Bed functionality working while adding platform features
