# Smart Plant Bed — Project Note

## 1. Project Goal

Build a commercial MVP smart plant-bed system using:

- Laravel backend
- MySQL database
- ESP32 device
- Web dashboard / PWA later

### Main product goals

- Monitor temperature, humidity, and soil moisture.
- Allow manual watering from the web dashboard.
- Allow auto-watering based on soil moisture.
- Allow schedule-based watering.
- Support device claiming/onboarding by short claim code or QR code.
- Support offline-capable device behavior later.

### Product direction

**Cloud-managed, device-executed, offline-capable.**

Laravel should manage users, device ownership, rules, schedules, commands, logs, and dashboard state. The ESP32 should execute real device behavior and survive temporary internet loss using cached config where possible.

---

## 2. Current Architecture

### Laravel handles

- Web dashboard
- User accounts / auth
- Device ownership
- Device claiming flow
- Sensor reading storage
- Watering rules
- Watering schedules
- Device commands
- Watering logs
- Config API
- Command polling API
- Command acknowledgement API
- Auto-watering decision logic
- Schedule-based command generation

### ESP32 handles

- Sensor reading
- Relay / valve control
- Command polling
- Local config storage
- Local manual button
- Local schedule fallback
- Local auto-watering fallback
- Wi-Fi provisioning later

---

## 3. Current Database / Domain Status

### Main tables

- `users`
- `devices`
- `sensor_readings`
- `watering_rules`
- `watering_schedules`
- `device_commands`
- `watering_logs`

### Important `devices` fields already added

- `uuid`
- `api_key`
- `claim_code`
- `user_id`
- `claimed_at`
- `provisioning_token`
- `provisioning_expires_at`
- `status`
- `last_seen_at`
- `timezone`

### Schedule design

- One row = one scheduled day/time rule.
- `day_of_week` uses `1..7`.
- `1 = Monday`, `7 = Sunday`.

### Watering rule design

Current rule fields include:

- `auto_mode_enabled`
- `soil_moisture_threshold`
- `max_watering_duration_seconds`
- `cooldown_minutes`
- `local_manual_duration_seconds`

---

## 4. Important Backend Features Already Working

### APIs working

- `POST /api/device/readings`
- `GET /api/device/commands`
- `POST /api/device/commands/{command}/ack`
- `GET /api/device/config`

### Logic already working

- Manual watering command creation
- Auto-watering command creation from low soil moisture
- Schedule-based command creation
- Device timezone support in config
- Device config returns timezone, rules, and schedules
- Command queue / duplicate active command protection
- Watering log creation

### Scheduler

Laravel schedule command exists:

```bash
php artisan watering:check-schedules
```

Testing support was added with:

```bash
php artisan watering:check-schedules --day=1 --time=09:30
```

---

## 5. Device Claiming / Onboarding Direction

### V1 customer-facing claim idea

Customers should **not** type or see the long UUID during normal setup.

Use:

- Internal device ID: `uuid`
- Internal device auth: `api_key` / `X-DEVICE-KEY`
- Customer-facing setup code: `claim_code`

Example:

```text
UUID       = internal only
Claim code = PB72K9
```

### Claim methods

Support both:

- Manual short code entry
- QR claim URL

### QR direction

QR should point to a Laravel URL like:

```text
/claim/PB72K9
```

### Current claim status

`DeviceClaimController` has been started and routes exist.

Intended routes:

```text
GET  /devices/add
POST /devices/claim
GET  /claim/{code}
POST /claim/{code}
GET  /devices/{device}/setup
```

### Current test device state

There is a test device with:

```text
claim_code = PB72K9
status     = unclaimed
user_id    = null
```

Use this test device for claim-flow testing.

---

## 6. Auth Status

### Current auth stack

- Laravel Fortify installed
- Fortify routes exist
- Register/login routes available
- Email verification enabled
- Password reset enabled
- Two-factor auth disabled for now

### Current auth issue area

Auth backend is installed, but auth views/pages are still being finished and tested.

Known behavior:

- Guest-only auth pages redirect if already logged in.
- `/reset-password` requires a token URL, so direct `GET /reset-password` without token is expected to fail.
- Mail is currently using the `log` driver, so emails go to `storage/logs/laravel.log`.

### Important current fix needed

Device pages must be protected by auth middleware so `/devices` is not public after logout.

---

## 7. Important Product Decisions Already Made

### Reset behavior

Device should have a physical reset button.

For V1:

- Reset is mostly ESP32-side.
- Clear Wi-Fi / cached config.
- Keep UUID.
- Keep API key.
- Usually keep server ownership intact.
- Return device to setup/AP mode.
- No major Laravel-side reset flow is needed yet.

### Local manual button behavior

- Press once = water for configured duration.
- Press again while watering = stop.
- No cooldown for the local manual button.

### Auto watering

- Should respect cooldown.
- Should not run when soil moisture reading is unavailable.

### Schedule watering

- Should eventually work offline using saved local config.

### Future auth direction

V1 uses regular Laravel auth now.

Later it should still be possible to move to:

- WorkOS
- Google/Apple social login
- More professional auth stack

Keep auth logic separate from domain/device logic.

---

## 8. Main Files to Inspect First

### Laravel core/domain models

- `app/Models/Device.php`
- `app/Models/User.php`
- `app/Models/WateringRule.php`
- `app/Models/WateringSchedule.php`
- `app/Models/DeviceCommand.php`
- `app/Models/WateringLog.php`

### Controllers

- `app/Http/Controllers/DeviceController.php`
- `app/Http/Controllers/DeviceClaimController.php`
- `app/Http/Controllers/Api/DeviceReadingController.php`
- `app/Http/Controllers/Api/DeviceCommandController.php`
- `app/Http/Controllers/Api/DeviceConfigController.php`

### Scheduler / command

- `app/Console/Commands/CheckWateringSchedules.php`

### Auth

- `app/Providers/FortifyServiceProvider.php`
- `config/fortify.php`

### Routes

- `routes/web.php`
- `routes/api.php`
- `routes/console.php`

### Blade views

- `resources/views/devices/index.blade.php`
- `resources/views/devices/show.blade.php`
- `resources/views/devices/add.blade.php`
- `resources/views/devices/claim.blade.php`
- `resources/views/devices/setup.blade.php`
- Auth views under `resources/views/auth/...`

---

## 9. Present Status Summary

### Done

- Core schema for device/watering domain
- Device APIs
- Manual watering
- Auto watering
- Schedule watering logic
- Config API
- Timezone support
- Claim fields added to devices
- Fortify installed
- GitHub private repo created

### In progress

- Login/register/forgot-password/verify-email pages
- Claim/onboarding page testing
- Protecting device pages with auth middleware
- Logout / post-login / verification flow cleanup

### Not done yet

- Polished auth UI
- Google/Apple login
- Phone auth
- Full ESP32 provisioning flow
- Device Wi-Fi setup endpoint/page interaction
- Release/unclaim device flow
- Polished onboarding UX

---

## 10. Current Main Issue To Continue From

Continue from the Laravel side first.

### Immediate priorities

1. Protect `/devices` and related pages with auth middleware.
2. Finish auth Blade pages so Fortify pages are usable.
3. Verify login / register / logout flow.
4. Test device claim flow using claim code `PB72K9`.
5. Confirm successful claim updates:
    - `user_id`
    - `claimed_at`
    - `status = claimed_pending_wifi`
    - `provisioning_token`
    - `provisioning_expires_at`
6. Show setup instructions page after claim.

---

## 11. Desired Coding Style

- Keep the solution simple.
- Keep it V1-friendly.
- Keep it beginner-friendly.
- Avoid team/org/multi-tenant complexity for now.
- Do not overengineer.
- Prefer practical Laravel structure.
- Check existing files before replacing them.
- When editing small important files, provide full file code.

---

## 12. Repo / Source Note

GitHub private repo exists:

```text
bdkazal/smart-plant-bed
```

Use uploaded files / pasted files in the project chat as the source of truth first.

---

## 13. Best Next Step In A New Chat

Start by inspecting:

- `routes/web.php`
- `FortifyServiceProvider.php`
- `DeviceClaimController.php`
- Current auth Blade files

Then fix and finish:

- Auth pages
- Auth protection on device pages
- Claim-flow testing

Suggested first message for the next chat:

```text
Continue from the auth + claim flow stage. Check uploaded files first.
```

---

# Device Presence / Heartbeat Design

## Goal

Make device online/offline status more professional without websocket complexity.

Because the ESP32 already talks to Laravel over HTTP, the cleanest approach is:

1. Device sends heartbeat regularly.
2. Laravel updates `last_seen_at` on every device-originated call.
3. Dashboard polls Laravel every 5 seconds.
4. Laravel computes online/offline from freshness.

## Recommended presence rules

### Device heartbeat

Have the ESP32 contact the server on a fixed interval even when idle.

This can be:

- A lightweight `/api/device/heartbeat` endpoint, or
- Reusing existing endpoints such as readings, config fetch, or command polling, as long as one happens regularly.

### Update `last_seen_at` on every device-originated call

Not only readings. Also update it on:

- Command poll
- Command ack
- Config fetch
- Heartbeat
- State sync

### UI polling

Dashboard should poll the server every 5 seconds.

### Server freshness window

Recommended current rule:

```text
Online  = last_seen_at within 20 seconds
Offline = last_seen_at older than 20 seconds
```

This is better than using a long 60-second window for a control dashboard.

## Customer-facing labels

Keep only these two concepts visible to customers:

### Device Status

- Online
- Offline

Computed from recent heartbeat / last contact.

### Watering State

- Idle
- Waiting
- Watering
- Stopping

Computed from command state and/or device-reported state.

Connectivity state and operational state should stay separate.

## What professionals usually avoid

Avoid relying on:

- Pinging the device LAN IP from backend
- Browser-only connectivity guesses
- Very long `last_seen_at` windows
- One merged status that mixes connectivity and watering operation

Better pattern: **device-to-cloud heartbeat + server-side freshness**.

## Recommended implementation now

Do this before websockets:

- ESP32 sends heartbeat every 10–15 seconds.
- Laravel updates `last_seen_at` on every device request.
- Dashboard polls every 5 seconds.
- Online threshold = 20 seconds.

Even before heartbeat firmware exists, online badge improves if:

- Command poll updates `last_seen_at`.
- Command ack updates `last_seen_at`.

---

# Device State Sync / Reconnect Reconciliation — V1 Design

## Goal

Allow a device to reconnect and tell Laravel its current real state, so the server/dashboard can recover from stale assumptions after disconnects.

This is for cases like:

- Command was sent.
- Device disconnected.
- Server timed out the command.
- Device reconnects later.
- Server must learn the real current state again.

## Main rule

This flow updates **current device state**, not old closed command history.

It should help Laravel know:

- Is the device currently watering?
- Is the valve currently open or closed?
- What are the latest sensor values?
- What was the last command the device completed?

It should not blindly reopen or rewrite old closed commands.

## Endpoint proposal

```text
POST /api/device/state
```

### Auth

Use existing device auth:

- `device_uuid`
- `X-DEVICE-KEY`

Same style as current device endpoints.

## Payload design

### Common fields for all device types

```json
{
    "device_uuid": "uuid-here",
    "device_type": "plant_bed_controller",
    "reported_at": "2026-04-24T12:00:00Z",
    "firmware_version": "v1.0.0",
    "operation_state": "idle"
}
```

### Plant-bed V1 fields

```json
{
    "device_uuid": "uuid-here",
    "device_type": "plant_bed_controller",
    "reported_at": "2026-04-24T12:00:00Z",
    "firmware_version": "v1.0.0",
    "operation_state": "watering",
    "valve_state": "open",
    "watering_state": "watering",
    "last_completed_command_id": 42,
    "temperature": 29.1,
    "humidity": 68,
    "soil_moisture": 31
}
```

## Validation rules

### Common validation

- `device_uuid`: required, uuid
- `device_type`: required, string
- `reported_at`: nullable date
- `firmware_version`: nullable string
- `operation_state`: required

### Plant-bed V1 validation

- `valve_state`: nullable, `open|closed`
- `watering_state`: nullable, `idle|watering`
- `last_completed_command_id`: nullable integer
- `temperature`: nullable numeric
- `humidity`: nullable integer, `0..100`
- `soil_moisture`: nullable integer, `0..100`

## Laravel behavior

Always do these on valid state sync:

1. Authenticate the device.
2. Update `last_seen_at = now()`.
3. Store or update current known device state.
4. Optionally store latest sensor values if included.
5. Return current server-side acknowledgement/result.

## What Laravel should update

### Presence

- Refresh `last_seen_at`.

### Current operational truth

For plant-bed V1:

- Current valve open/closed
- Current watering idle/watering
- Firmware version if provided

### Latest sensor state

If sensor values are included:

- Store as a normal sensor reading, or
- Update a current-state snapshot.

For V1, storing a normal reading is simplest if the values are real readings.

### Reconciliation hints

If `last_completed_command_id` is provided, Laravel can use it to understand what the device believes it finished.

## What Laravel should not do automatically

### Do not reopen closed commands

If a command is already:

- `expired`
- `failed`
- `executed`

Do not automatically turn it into another status just because the device reconnected.

### Do not rewrite history casually

This flow is about current truth, not mutating old history without care.

## Reconciliation behavior for V1

### Case A — device says idle/closed

If device reports:

```text
watering_state = idle
valve_state    = closed
```

Laravel should:

- Trust current device state for current UI.
- Show watering as idle.
- Keep old closed logs closed.

### Case B — device sends `last_completed_command_id = X`

If command `X` is still open:

- Laravel may safely close it as executed if rules allow and command belongs to this device.

If command `X` is already closed:

- Do not reopen/rewrite it.
- Accept the state sync as current truth.

### Case C — device says it is watering now

Laravel should:

- Update current device state to watering.
- Let UI show watering.
- Avoid mutating unrelated old closed commands automatically.

## Suggested V1 response

```json
{
    "message": "Device state synced successfully.",
    "device_id": 1,
    "server_time": "2026-04-24 18:00:00",
    "accepted": true
}
```

Later this can include desired server state or a config refresh hint.

## Data model direction

### Best simple V1 option

Add current-state fields directly to the `devices` table:

- `last_reported_operation_state`
- `last_reported_valve_state`
- `last_reported_watering_state`
- `firmware_version`
- `last_reported_at`

This is easiest for V1.

### Alternative

Create a separate `device_states` table.

That is better for platform scale, but it is more work now.

### Recommendation

For V1, use the `devices` table because it is simple and enough for one main device family.

## Relationship to current UI

Dashboard can use:

### Device Status

- Online/offline from `last_seen_at`.

### Watering State

Prefer:

1. Device-reported current state if recently synced.
2. Command-derived state as fallback.

This is stronger than command-only assumptions.

## Relationship to current ack flow

Current ack flow remains.

Use existing command lifecycle statuses:

- `pending`
- `acknowledged`
- `executed`
- `failed`

State sync adds reconnect recovery.

Separation:

- Ack flow handles command lifecycle.
- State sync handles current truth after reconnect.

## V1 practical decisions

1. Use one endpoint: `/api/device/state`.
2. Keep closed command history closed.
3. State sync updates current truth, not old history.
4. Store current reported state on the `devices` table.
5. For plant-bed V1, support:
    - `valve_state`
    - `watering_state`
    - optional sensor values
    - optional `last_completed_command_id`

## Best next implementation order

1. Add state fields to `devices` table.
2. Add `DeviceStateController`.
3. Add `/api/device/state` route.
4. Update dashboard status logic to prefer device-reported state when available.
5. Test reconnect scenarios.

---

# Smart Plant Bed Device API Contract — V1

This is the practical contract between the ESP32 device and the Laravel backend.

It is written for implementation, not theory.

## 1. Purpose

The device must be able to:

- Identify itself securely.
- Fetch config.
- Upload readings.
- Poll for commands.
- Acknowledge command progress.
- Send heartbeat.
- Sync current device state after reconnect or local execution.

Current MVP device type:

```text
plant_bed_controller
```

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
{
    "device_uuid": "<device-uuid>"
}
```

### Rule

The device must never use normal user login/session auth.

---

## 3. Common Device Rules

Recommended current behavior:

- Command poll every 5–10 seconds.
- Heartbeat every 10–15 seconds.
- Readings on normal sensor interval.
- State sync:
    - after reconnect
    - after local completion if needed
    - when actual current state changes meaningfully

### Online/offline

Laravel treats a device as online if it contacted the server recently.

Current practical rule:

```text
online window = about 20 seconds
```

So the device should keep contacting Laravel regularly.

---

## 4. Endpoint: Get Device Config

```http
GET /api/device/config?device_uuid=<uuid>
X-DEVICE-KEY: <device_api_key>
```

### Purpose

Device fetches current config/settings from Laravel.

### Expected config areas

Current or future config may include:

- Device metadata
- Timezone
- Automation mode
- Soil moisture threshold
- Max watering duration
- Cooldown
- Local manual duration
- Schedule summary

### Device behavior

Device should fetch config:

- At boot
- After reconnect
- Periodically if needed
- After claim/setup completion

---

## 5. Endpoint: Upload Sensor Readings

```http
POST /api/device/readings
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

- Validates auth.
- Stores reading.
- Updates `last_seen_at`.
- May trigger auto watering if:
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

- Send real sensor values.
- Not assume auto command exists unless the server later returns one through command polling.
- Handle null/unavailable sensor values carefully.

---

## 6. Endpoint: Poll Pending Commands

```http
GET /api/device/commands?device_uuid=<uuid>
X-DEVICE-KEY: <device_api_key>
```

### Purpose

Ask Laravel whether a pending command exists.

### Laravel behavior

Laravel currently:

- Validates auth.
- Updates `last_seen_at`.
- Cleans stale commands.
- Returns oldest pending command if one exists.

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

1. Read `command_type`.
2. Process the command.
3. Send ack/status update.

When no command exists:

- Do nothing.
- Continue polling later.

---

## 7. Endpoint: Acknowledge Command Progress

```http
POST /api/device/commands/{command_id}/ack
X-DEVICE-KEY: <device_api_key>
Content-Type: application/json
```

### Request body examples

```json
{
    "device_uuid": "YOUR-DEVICE-UUID",
    "status": "acknowledged"
}
```

```json
{
    "device_uuid": "YOUR-DEVICE-UUID",
    "status": "executed"
}
```

```json
{
    "device_uuid": "YOUR-DEVICE-UUID",
    "status": "failed",
    "message": "Valve jam or pump error"
}
```

### Allowed request statuses

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

### Device behavior for `valve_on`

Recommended flow:

1. Device receives pending command.
2. Device starts the action.
3. Send `acknowledged`.
4. When action fully completes, send `executed`.
5. If action fails, send `failed`.

### Device behavior for `valve_off`

Recommended flow:

1. Device receives stop command.
2. Attempt stop.
3. Send `acknowledged`.
4. When stop is complete, send `executed`.
5. If stop fails, send `failed`.

---

## 8. Endpoint: Heartbeat

```http
POST /api/device/heartbeat
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

- Validates auth.
- Updates `last_seen_at`.

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

```text
every 10–15 seconds
```

---

## 9. Endpoint: Device State Sync / Reconnect Reconciliation

```http
POST /api/device/state
X-DEVICE-KEY: <device_api_key>
Content-Type: application/json
```

### Purpose

Tell Laravel the device’s current real runtime state.

Use this when:

- Device reconnects after disconnect.
- Device wants to correct stale server assumptions.
- Device has locally completed an action.
- Device wants to report current watering/valve truth.

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

### Current plant-bed fields

- `device_type`: currently must match actual device type.
- `operation_state`: generic operating state.
- `valve_state`: `open` or `closed`.
- `watering_state`: `idle` or `watering`.
- Sensor values are optional but useful.
- `last_completed_command_id` is optional.

### Laravel behavior

Laravel currently validates auth and device type, then updates:

- `last_seen_at`
- `last_reported_at`
- `last_reported_operation_state`
- `last_reported_valve_state`
- `last_reported_watering_state`

It can also:

- Store a reading if sensor values are included.
- Reconcile an open command if `last_completed_command_id` is valid.

### Important rule

This endpoint is for current state sync. It should not blindly rewrite old history or reopen already-closed commands.

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

- Reconnecting after being offline.
- Local state changed and server may be stale.
- Local completion happened but normal ack path was interrupted.

---

## 10. Current Laravel State Rules

### Presence

Laravel updates `last_seen_at` when device:

- Uploads reading
- Polls commands
- Acks command
- Sends heartbeat
- Sends state sync

### Online/offline meaning

Current practical UI rule:

- Online if device contacted server recently.
- Offline if not.

### Manual control

Manual dashboard control is only intended when device is online.

### Auto watering

Triggered by readings and server-side rules.

### Schedule watering

Triggered by Laravel scheduler when schedule mode is active.

---

## 11. Device Runtime Loop — Recommended V1

### Suggested loop

Fast loop every 5–10 seconds:

- Poll `/api/device/commands`.

Every 10–15 seconds:

- Send `/api/device/heartbeat`.

On sensor interval:

- Send `/api/device/readings`.

On reconnect or uncertain state:

- Send `/api/device/state`.

On command execution steps:

- Send `/api/device/commands/{id}/ack`.

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

- Open valve.
- Keep watering for duration.
- Stop after duration.
- Report lifecycle properly.

### `valve_off`

Payload:

```json
{}
```

Meaning:

- Stop watering immediately.
- Close valve.
- Report lifecycle properly.

---

## 13. Failure Handling Rules

### If command poll fails

- Retry later.
- Do not assume there is no command forever.

### If ack fails due to connection loss

- Keep local knowledge.
- After reconnect, use `/api/device/state`.
- Optionally include `last_completed_command_id`.

### If device restarts during watering

On boot/reconnect:

- Determine actual valve/watering state.
- Send `/api/device/state`.
- Let Laravel recover current truth.

### If server returns closed-command conflict on ack

- Do not keep retrying the same ack forever.
- Rely on current state sync if needed.

---

## 14. Current Contract Limits

These are current limitations, not bugs:

- State sync currently still expects a full state payload, not a tiny partial payload.
- `device_type` checking is strict.
- Current V1 state sync is strongest for `plant_bed_controller`.
- Future device types will need type-specific state fields.

---

## 15. Recommended Next Implementation Work

### Laravel side

Mostly good enough now for MVP progress.

### Device side next

Implement:

- Boot flow
- Config fetch
- Heartbeat loop
- Command poll loop
- Command ack flow
- State sync on reconnect

### Documentation next

This contract should go into either:

- `README.md`, or
- `docs/device-api-contract.md`

For the Laravel project root, keep this file as:

```text
ProjectNote.md
```

---

## 16. Final Summary

The current Laravel-device contract now supports:

- Secure device authentication
- Readings
- Command polling
- Command lifecycle updates
- Heartbeat presence
- Reconnect state sync
- Open-command reconciliation

That is enough to begin real ESP32 runtime design and implementation against a stable backend contract.
