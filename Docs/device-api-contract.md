# Device API Contract — V1

This document defines the current runtime contract between the **ESP32 device** and the **Laravel backend** for the Smart Plant Bed MVP.

It is written for implementation, not theory.

---

## 1. Purpose

The device must be able to:

- identify itself securely
- fetch config
- upload readings
- poll for commands
- acknowledge command progress
- send heartbeat
- sync current device state after reconnect or local execution

This contract is for the current MVP device type:

- `plant_bed_controller`

The structure should remain reusable for future device types.

---

## 2. Authentication

### Device identity

Each device uses:

- `device_uuid`
- `X-DEVICE-KEY`

### Header

Every authenticated device request must include:

```http
X-DEVICE-KEY: <device_api_key>
```

### Request body or query

Most endpoints also require:

```json
"device_uuid": "<device-uuid>"
```

### Rule

The device must **never** use normal user login/session auth.

---

## 3. Common Device Rules

### Device should call server regularly

Recommended current behavior:

- command poll every **5–10 seconds**
- heartbeat every **10–15 seconds**
- readings on normal sensor interval
- state sync:
    - after reconnect
    - after local completion if needed
    - when actual current state changes meaningfully

### Online/offline

Laravel currently treats device as online if it contacted the server recently.

Current practical rule:

- online window: about **20 seconds**

So device should keep contacting Laravel regularly.

---

## 4. Endpoint: Get Device Config

### Route

```http
GET /api/device/config?device_uuid=<uuid>
```

### Header

```http
X-DEVICE-KEY: <device_api_key>
```

### Purpose

Device fetches current config/settings from Laravel.

### Expected config areas

Current or future config may include:

- device metadata
- timezone
- automation mode
- soil moisture threshold
- max watering duration
- cooldown
- local manual duration
- schedule summary

### Device behavior

Device should fetch config:

- at boot
- after reconnect
- periodically if needed
- after claim/setup completion

---

## 5. Endpoint: Upload Sensor Readings

### Route

```http
POST /api/device/readings
```

### Header

```http
X-DEVICE-KEY: <device_api_key>
Content-Type: application/json
```

### Request body

```json
{
    "device_uuid": "YOUR-DEVICE-UUID",
    "temperature": 29.1,
    "humidity": 68,
    "soil_moisture": 31
}
```

### Purpose

Send latest sensor values to Laravel.

### Laravel behavior

Laravel currently:

- validates auth
- stores reading
- updates `last_seen_at`
- may trigger auto watering if:
    - mode = auto
    - moisture exists
    - moisture <= threshold
    - no active watering command exists
    - cooldown has passed

### Response example

```json
{
    "message": "Reading stored successfully.",
    "device_id": 1,
    "reading_id": 45,
    "auto_watering_triggered": true
}
```

### Device behavior

Device should:

- send real sensor values
- not assume auto command exists unless server later returns one through command polling
- handle null sensor values carefully when sensor unavailable

---

## 6. Endpoint: Poll Pending Commands

### Route

```http
GET /api/device/commands?device_uuid=<uuid>
```

### Header

```http
X-DEVICE-KEY: <device_api_key>
```

### Purpose

Ask Laravel whether a pending command exists.

### Laravel behavior

Laravel currently:

- validates auth
- updates `last_seen_at`
- cleans stale commands
- returns oldest pending command if one exists

### Response when command exists

```json
{
    "message": "Pending command found.",
    "command": {
        "id": 64,
        "command_type": "valve_on",
        "payload": {
            "duration_seconds": 30
        },
        "status": "pending",
        "issued_at": "2026-04-24 22:02:35"
    }
}
```

### Response when no command exists

```json
{
    "message": "No pending commands.",
    "command": null
}
```

### Device behavior

When command exists:

1. read `command_type`
2. process it
3. send ack/status update

When no command exists:

- do nothing
- continue polling later

---

## 7. Endpoint: Acknowledge Command Progress

### Route

```http
POST /api/device/commands/{command_id}/ack
```

### Header

```http
X-DEVICE-KEY: <device_api_key>
Content-Type: application/json
```

### Request body

```json
{
    "device_uuid": "YOUR-DEVICE-UUID",
    "status": "acknowledged"
}
```

or

```json
{
    "device_uuid": "YOUR-DEVICE-UUID",
    "status": "executed"
}
```

or

```json
{
    "device_uuid": "YOUR-DEVICE-UUID",
    "status": "failed",
    "message": "Valve jam or pump error"
}
```

### Allowed statuses

Current allowed request values:

- `acknowledged`
- `executed`
- `failed`

### Current transition rules

Laravel currently allows:

- `pending -> acknowledged`
- `pending -> failed`
- `acknowledged -> executed`
- `acknowledged -> failed`

Laravel rejects invalid transitions.

### Closed command protection

Laravel rejects updates to already-closed commands:

- `expired`
- `failed`
- `executed`

### Device behavior

#### For `valve_on`

Recommended flow:

1. device receives pending command
2. device starts the action
3. send `acknowledged`
4. when action fully completes, send `executed`
5. if action fails, send `failed`

#### For `valve_off`

Recommended flow:

1. device receives stop command
2. attempt stop
3. send `acknowledged`
4. when stop is complete, send `executed`
5. if stop fails, send `failed`

---

## 8. Endpoint: Heartbeat

### Route

```http
POST /api/device/heartbeat
```

### Header

```http
X-DEVICE-KEY: <device_api_key>
Content-Type: application/json
```

### Request body

```json
{
    "device_uuid": "YOUR-DEVICE-UUID"
}
```

### Purpose

Tell Laravel the device is alive, even when no readings or commands are happening.

### Laravel behavior

Laravel currently:

- validates auth
- updates `last_seen_at`

### Response example

```json
{
    "message": "Heartbeat received successfully.",
    "device_id": 1,
    "last_seen_at": "2026-04-24 22:10:00"
}
```

### Device behavior

Device should send heartbeat regularly:

- every **10–15 seconds**

---

## 9. Endpoint: Device State Sync / Reconnect Reconciliation

### Route

```http
POST /api/device/state
```

### Header

```http
X-DEVICE-KEY: <device_api_key>
Content-Type: application/json
```

### Purpose

Tell Laravel the device’s **current real runtime state**.

Use this when:

- device reconnects after disconnect
- device wants to correct stale server assumptions
- device has locally completed an action
- device wants to report current watering/valve truth

### Request body for plant-bed V1

```json
{
    "device_uuid": "YOUR-DEVICE-UUID",
    "device_type": "plant_bed_controller",
    "reported_at": "2026-04-24T14:40:00Z",
    "firmware_version": "v1.0.0",
    "operation_state": "watering",
    "valve_state": "open",
    "watering_state": "watering",
    "temperature": 29.1,
    "humidity": 68,
    "soil_moisture": 31
}
```

### Current plant-bed fields

- `device_type`: currently must match actual device type
- `operation_state`: generic operating state
- `valve_state`: `open` or `closed`
- `watering_state`: `idle` or `watering`
- optional latest sensor values
- optional `last_completed_command_id`

### Reconciliation request example

```json
{
    "device_uuid": "YOUR-DEVICE-UUID",
    "device_type": "plant_bed_controller",
    "reported_at": "2026-04-24T15:45:00Z",
    "firmware_version": "v1.0.0",
    "operation_state": "idle",
    "valve_state": "closed",
    "watering_state": "idle",
    "last_completed_command_id": 64
}
```

### Laravel behavior

Laravel currently:

- validates auth
- validates device type
- updates:
    - `last_seen_at`
    - `last_reported_at`
    - `last_reported_operation_state`
    - `last_reported_valve_state`
    - `last_reported_watering_state`
- optionally stores a reading if sensor values are included
- optionally reconciles an open command if `last_completed_command_id` is valid

### Important rule

This endpoint is for **current state sync**, not for blindly rewriting old history.

It should not reopen already-closed commands.

### Response example

```json
{
    "message": "Device state synced successfully.",
    "device_id": 1,
    "state": {
        "operation_state": "idle",
        "valve_state": "closed",
        "watering_state": "idle",
        "last_reported_at": "2026-04-24 22:02:47"
    },
    "reading_stored": false,
    "reading_id": null,
    "accepted_completed_command_id": 64
}
```

### Device behavior

Use state sync when:

- reconnecting after being offline
- local state changed and server may be stale
- local completion happened but normal ack path was interrupted

---

## 10. Current Laravel State Rules

### Presence

Laravel updates `last_seen_at` when device:

- uploads reading
- polls commands
- acks command
- sends heartbeat
- sends state sync

### Online/offline meaning

Current practical UI rule:

- online if device contacted server recently
- offline if not

### Manual control

Manual dashboard control is only intended when device is online.

### Auto watering

Triggered by readings and server-side rules.

### Schedule watering

Triggered by Laravel scheduler when schedule mode is active.

---

## 11. Device Runtime Loop — Recommended V1

### Suggested loop

#### Fast loop every 5–10 seconds

- poll `/api/device/commands`

#### Every 10–15 seconds

- send `/api/device/heartbeat`

#### On sensor interval

- send `/api/device/readings`

#### On reconnect or uncertain state

- send `/api/device/state`

#### On command execution steps

- send `/api/device/commands/{id}/ack`

---

## 12. Plant-Bed Device Command Meanings

### `valve_on`

Payload:

```json
{
    "duration_seconds": 30
}
```

Meaning:

- open valve
- keep watering for duration
- then stop
- report lifecycle properly

### `valve_off`

Payload:

```json
{}
```

Meaning:

- stop watering immediately
- close valve
- report lifecycle properly

---

## 13. Failure Handling Rules

### If command poll fails

- retry later
- do not assume no command forever

### If ack fails due to connection loss

- keep local knowledge
- after reconnect, use `/api/device/state`
- optionally include `last_completed_command_id`

### If device restarts during watering

On boot/reconnect:

- determine actual valve/watering state
- send `/api/device/state`
- let Laravel recover current truth

### If server returns closed-command conflict on ack

- do not keep retrying same ack forever
- instead rely on current state sync if needed

---

## 14. Current Contract Limits

These are current limitations, not bugs.

- state sync currently still expects a full state payload, not a tiny partial payload
- `device_type` checking is strict
- current V1 state sync is strongest for plant-bed controller
- future device types will need type-specific state fields

---

## 15. Recommended Next Implementation Work

### Laravel side

Mostly good enough now for MVP progress.

### Device side next

Implement:

- boot flow
- config fetch
- heartbeat loop
- command poll loop
- command ack flow
- state sync on reconnect

### Documentation next

This contract should live in:

- `docs/device-api-contract.md`

---

## 16. Final Summary

The current Laravel-device contract now supports:

- secure device authentication
- readings
- command polling
- command lifecycle updates
- heartbeat presence
- reconnect state sync
- open-command reconciliation

That is enough to begin **real ESP32 runtime design and implementation** against a stable backend contract.
