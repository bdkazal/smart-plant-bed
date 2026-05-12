# Smart Fountain API Contract

This document is the firmware-facing contract for the Smart Fountain device.

The Smart Fountain is a **persistent state device**. It keeps outputs in their current state until another command changes them.

## Device type

```text
smart_fountain
```

## Authentication

Every device request must include:

```http
X-DEVICE-KEY: <device_api_key>
Accept: application/json
Content-Type: application/json
```

The device identifies itself with:

```json
{
  "device_uuid": "c2e6eb95-dc09-4344-a996-bc43b3c24da5"
}
```

If the key is missing or wrong, Laravel returns `401`.

If the posted `device_type` does not match the stored device type, Laravel returns `409`.

## Firmware loop

Recommended loop:

```text
1. Fetch config at boot.
2. Sync time from server_time_utc when config is reachable.
3. Send state/readings every few seconds.
4. Poll pending commands every few seconds.
5. When command exists:
   - ACK acknowledged.
   - Apply command locally.
   - ACK executed or failed.
   - Send latest actual state/readings.
```

Development intervals:

```text
State sync:      every 5 seconds
Command polling: every 2-5 seconds
Config fetch:    boot + occasional manual refresh
```

## Endpoint summary

```http
GET  /api/device/config
POST /api/device/state
GET  /api/device/commands
POST /api/device/commands/{command}/ack
POST /api/device/heartbeat
```

For Smart Fountain, prefer `/api/device/state` over `/api/device/readings`.

`/api/device/readings` is mainly for the legacy Smart Plant Bed reading shape.

---

## 1. Fetch config

```http
GET /api/device/config?device_uuid=<uuid>
```

Headers:

```http
X-DEVICE-KEY: <device_api_key>
Accept: application/json
```

Example response shape:

```json
{
  "message": "Device config fetched successfully.",
  "server_time_utc": "2026-05-12T16:46:13+00:00",
  "server_time_local": "2026-05-12 22:46:13",
  "server_time": "2026-05-12 22:46:13",
  "config": {
    "device_uuid": "c2e6eb95-dc09-4344-a996-bc43b3c24da5",
    "device_name": "Biztola Smart Fountain",
    "device_type": "smart_fountain",
    "timezone": "Asia/Dhaka",
    "timezone_offset_minutes": 360,
    "behavior_type": "persistent_state",
    "capabilities": {
      "pump_output": {
        "config": {},
        "state": {}
      },
      "dimmable_light": {
        "config": {},
        "state": {}
      },
      "rgb_light": {
        "config": {},
        "state": {}
      },
      "water_level_sensor": {
        "config": {},
        "state": {}
      }
    },
    "outputs": {
      "pump": {
        "type": "pump",
        "name": "Pump",
        "config": {},
        "state": {
          "enabled": false,
          "speed_percent": 0
        },
        "last_changed_source": null,
        "last_changed_at": null
      },
      "cob_light": {
        "type": "dimmable_light",
        "name": "COB Light",
        "config": {},
        "state": {
          "enabled": false,
          "brightness_percent": 0
        },
        "last_changed_source": null,
        "last_changed_at": null
      },
      "rgb_light": {
        "type": "rgb_light",
        "name": "RGB Light",
        "config": {},
        "state": {
          "enabled": false,
          "brightness_percent": 0,
          "color": "#FFB066",
          "effect": "warm_glow"
        },
        "last_changed_source": null,
        "last_changed_at": null
      }
    },
    "safety": {
      "pump_requires_water_level_ok": true,
      "water_low_should_force_pump_off": true
    },
    "commands": {
      "supported": ["output_set", "scene_apply"],
      "execution_meaning": "executed means requested state was applied, not that the output finished running"
    }
  }
}
```

Firmware should use this response to learn available outputs, the latest server-known output state, and the current server/device time.

Time rules:

```text
server_time_utc is the canonical time for RTC/NTP-style sync.
server_time_local is for logs/display only.
server_time is a backward-compatible local-time alias.
timezone and timezone_offset_minutes are used for local display/schedule interpretation.
RTC should store UTC time, not local wall-clock time.
```

---

## 2. Send state/readings

```http
POST /api/device/state
```

This endpoint reports the **actual hardware state**.

Minimum payload:

```json
{
  "device_uuid": "c2e6eb95-dc09-4344-a996-bc43b3c24da5",
  "device_type": "smart_fountain",
  "operation_state": "running"
}
```

Recommended full payload:

```json
{
  "device_uuid": "c2e6eb95-dc09-4344-a996-bc43b3c24da5",
  "device_type": "smart_fountain",
  "firmware_version": "fountain-dev-0.1",
  "operation_state": "running",
  "outputs": {
    "pump": {
      "enabled": false,
      "speed_percent": 0,
      "source": "device_state"
    },
    "cob_light": {
      "enabled": true,
      "brightness_percent": 40,
      "source": "device_state"
    },
    "rgb_light": {
      "enabled": true,
      "brightness_percent": 35,
      "color": "#FFB066",
      "effect": "warm_glow",
      "source": "device_state"
    }
  },
  "readings": {
    "water_low": {
      "value": 0,
      "unit": "boolean"
    },
    "water_level_percent": {
      "value": 40,
      "unit": "percent"
    },
    "water_level_raw": {
      "value": 2840,
      "unit": "adc"
    }
  }
}
```

Example response:

```json
{
  "message": "Device state synced successfully.",
  "device_id": 2,
  "device_type": "smart_fountain",
  "state": {
    "operation_state": "running",
    "last_reported_at": "2026-05-11 22:00:00",
    "outputs": []
  },
  "accepted_completed_command_id": null,
  "platform_outputs_updated": 3,
  "device_readings_stored": 3
}
```

`operation_state` recommended values:

```text
booting
running
error
maintenance
```

`outputs` should represent actual hardware output state.

`readings` should represent actual sensor readings.

---

## 3. Poll command

```http
GET /api/device/commands?device_uuid=<uuid>
```

No command:

```json
{
  "message": "No pending commands.",
  "command": null
}
```

`output_set` command:

```json
{
  "message": "Pending command found.",
  "command": {
    "id": 284,
    "command_type": "output_set",
    "payload": {
      "output": "pump",
      "state": {
        "enabled": true,
        "speed_percent": 60
      },
      "source": "dashboard"
    },
    "status": "pending",
    "issued_at": "2026-05-11 22:05:00"
  }
}
```

`scene_apply` command:

```json
{
  "message": "Pending command found.",
  "command": {
    "id": 285,
    "command_type": "scene_apply",
    "payload": {
      "scene_id": 1,
      "scene_name": "Day Fountain",
      "source": "scene:1",
      "outputs": {
        "pump": {
          "enabled": true,
          "speed_percent": 60
        },
        "cob_light": {
          "enabled": true,
          "brightness_percent": 40
        },
        "rgb_light": {
          "enabled": true,
          "brightness_percent": 35,
          "color": "#FFB066",
          "effect": "warm_glow"
        }
      }
    },
    "status": "pending",
    "issued_at": "2026-05-11 22:06:00"
  }
}
```

Schedule-created scene commands use the same `scene_apply` command type, with extra metadata:

```json
{
  "source": "schedule:5:evening",
  "schedule_range_id": 5,
  "schedule_name": "Evening",
  "schedule_period": "evening",
  "schedule_phase": "start"
}
```

---

## 4. Acknowledge command

```http
POST /api/device/commands/{command}/ack
```

First ACK after receiving command:

```json
{
  "device_uuid": "c2e6eb95-dc09-4344-a996-bc43b3c24da5",
  "status": "acknowledged"
}
```

After applying command successfully:

```json
{
  "device_uuid": "c2e6eb95-dc09-4344-a996-bc43b3c24da5",
  "status": "executed"
}
```

If the command fails:

```json
{
  "device_uuid": "c2e6eb95-dc09-4344-a996-bc43b3c24da5",
  "status": "failed",
  "message": "Pump driver did not respond."
}
```

Example response:

```json
{
  "message": "Command status updated successfully.",
  "command_id": 285,
  "command_type": "scene_apply",
  "status": "executed",
  "execution_meaning": "For persistent state commands, executed means the requested state was applied."
}
```

After `executed`, firmware should send `/api/device/state` with actual output states.

---

## 5. Optional heartbeat

```http
POST /api/device/heartbeat
```

For Smart Fountain, `/api/device/state` is usually better because it also updates outputs/readings.

---

## Output schemas

### Pump

```json
{
  "enabled": true,
  "speed_percent": 60
}
```

Rules:

```text
enabled = boolean
speed_percent = 0-100
```

### COB light

```json
{
  "enabled": true,
  "brightness_percent": 40
}
```

Rules:

```text
enabled = boolean
brightness_percent = 0-100
```

### RGB light

```json
{
  "enabled": true,
  "brightness_percent": 35,
  "color": "#FFB066",
  "effect": "warm_glow"
}
```

Supported effects:

```text
solid
breathing
slow_rainbow
warm_glow
water_shimmer
night_mode
```

---

## Water safety rules

Smart Fountain must protect the pump locally.

If `water_low` is true:

```text
1. Firmware must turn pump OFF immediately.
2. Firmware must ignore pump ON commands.
3. Firmware may keep COB/RGB light commands working.
4. Firmware should report pump OFF in the next /api/device/state payload.
```

Laravel also protects pump commands, but firmware safety must not depend only on internet/server availability.

Recommended low-water state payload:

```json
{
  "device_uuid": "c2e6eb95-dc09-4344-a996-bc43b3c24da5",
  "device_type": "smart_fountain",
  "operation_state": "running",
  "outputs": {
    "pump": {
      "enabled": false,
      "speed_percent": 0,
      "source": "water_safety"
    }
  },
  "readings": {
    "water_low": {
      "value": 1,
      "unit": "boolean"
    },
    "water_level_percent": {
      "value": 10,
      "unit": "percent"
    }
  }
}
```

---

## Offline behavior

Laravel treats the device as online when `last_seen_at` is fresh.

Current online window:

```text
20 seconds
```

When Smart Fountain is offline:

```text
- Home output commands are disabled.
- Scene Apply buttons are disabled.
- Backend rejects output_set command creation.
- Backend rejects scene_apply command creation.
- Scheduled timeline commands are skipped.
```

Editing cloud settings is still allowed while offline:

```text
- Device Settings
- Scene create/edit/delete
- Daily Timeline schedule edit
```

---

## Error responses

Missing key:

```json
{
  "message": "Missing device API key."
}
```

Invalid credentials:

```json
{
  "message": "Invalid device credentials."
}
```

Device type mismatch:

```json
{
  "message": "Device type mismatch."
}
```

Command belongs to another device:

```json
{
  "message": "This command does not belong to the authenticated device."
}
```

Command already closed:

```json
{
  "message": "Command is already closed and cannot be updated.",
  "command_id": 285,
  "status": "executed"
}
```

---

## Firmware checklist

```text
[ ] Store device_uuid and X-DEVICE-KEY securely enough for MVP.
[ ] Fetch config on boot.
[ ] Sync UTC time from server_time_utc when available.
[ ] Send /api/device/state regularly.
[ ] Poll /api/device/commands regularly.
[ ] Handle output_set.
[ ] Handle scene_apply.
[ ] ACK pending command as acknowledged before applying.
[ ] ACK executed after applying.
[ ] ACK failed with message if hardware action fails.
[ ] Enforce water_low pump safety locally.
[ ] Continue sending state even when no commands exist.
[ ] Treat unknown output keys as ignored, not fatal.
[ ] Treat unknown command types as failed or ignored safely.
```
