# Smart Plant Bed MVP

A Laravel + ESP32 smart plant-bed system for monitoring sensor data and controlling watering through manual, scheduled, and soil-moisture-based automation.

## Project Status

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

The ESP32/device-side runtime logic is **not fully designed or implemented yet**.  
The next major backend design step is:

**Device State Sync / Reconnect Reconciliation**

---

## Product Goal

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

This is the next major backend design topic.

Goal:
When the device reconnects after being offline, Laravel should learn the device’s real current state again without blindly rewriting old closed command history.

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

## Next Planned Step

### Device State Sync / Reconnect Reconciliation

Next, the Laravel side should define and implement a clean reconnect/state-sync flow so the server can recover from stale assumptions after device reconnect.

This will likely include a device state endpoint where the device reports things like:

- current valve state
- current watering state
- maybe last completed command id
- maybe latest sensor values

This should be a separate state-sync flow, not a rewrite of closed command history.

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

---

## Tech Stack

### Backend

- Laravel
- MySQL
- Blade
- REST-style device API

### Device

- ESP32
- soil moisture sensor
- temperature / humidity sensor
- solenoid valve with proper driver hardware

### Hosting

- VPS / custom domain

---

## Development Notes

This project is being built as a **commercial-minded MVP**, not as a hobby-only experiment.

Priority order remains:

1. reliable core watering behavior
2. clear device/server contract
3. simple customer UX
4. clean future expansion path

---

## Current Summary

This project is no longer just an idea.

The Laravel MVP now already includes:

- monitoring
- manual watering
- schedule watering
- auto watering
- command lifecycle rules
- timeout handling
- schedule management
- presence tracking direction

The next important step is to complete the server-side design for:

**Device State Sync / Reconnect Reconciliation**
