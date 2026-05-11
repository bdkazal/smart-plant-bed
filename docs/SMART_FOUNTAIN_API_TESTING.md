# Smart Fountain API Testing Guide

This guide contains copy-paste API requests for testing the Smart Fountain Laravel API with Postman, Insomnia, or curl before ESP32 firmware integration.

Use this together with:

- `docs/SMART_FOUNTAIN_API_CONTRACT.md`
- `docs/DEVICE_API.md`

## Base URL

Local Laravel server:

```text
http://127.0.0.1:8000
```

Production/staging example:

```text
https://your-domain.com
```

## Required headers

Every device API request needs:

```http
Accept: application/json
Content-Type: application/json
X-DEVICE-KEY: <device_api_key>
```

Replace these placeholders in all examples:

```text
<BASE_URL>
<DEVICE_UUID>
<DEVICE_API_KEY>
<COMMAND_ID>
```

Example values from local testing:

```text
BASE_URL=http://127.0.0.1:8000
DEVICE_UUID=c2e6eb95-dc09-4344-a996-bc43b3c24da5
```

Do not commit real production API keys.

---

## 1. Fetch Smart Fountain config

### Postman

```http
GET <BASE_URL>/api/device/config?device_uuid=<DEVICE_UUID>
```

Headers:

```http
Accept: application/json
X-DEVICE-KEY: <DEVICE_API_KEY>
```

### curl

```bash
curl -X GET "<BASE_URL>/api/device/config?device_uuid=<DEVICE_UUID>" \
  -H "Accept: application/json" \
  -H "X-DEVICE-KEY: <DEVICE_API_KEY>"
```

Expected result:

```text
Device config fetched successfully.
```

The response should include:

```text
device_type = smart_fountain
behavior_type = persistent_state
outputs = pump, cob_light, rgb_light
commands.supported = output_set, scene_apply
```

---

## 2. State sync: online with readings only

This makes the device online and stores water readings.

### Postman

```http
POST <BASE_URL>/api/device/state
```

Body:

```json
{
  "device_uuid": "<DEVICE_UUID>",
  "device_type": "smart_fountain",
  "firmware_version": "postman-test-0.1",
  "operation_state": "running",
  "readings": {
    "water_low": {
      "value": 0,
      "unit": "boolean"
    },
    "water_level_percent": {
      "value": 55,
      "unit": "percent"
    }
  }
}
```

### curl

```bash
curl -X POST "<BASE_URL>/api/device/state" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "X-DEVICE-KEY: <DEVICE_API_KEY>" \
  -d '{
    "device_uuid": "<DEVICE_UUID>",
    "device_type": "smart_fountain",
    "firmware_version": "postman-test-0.1",
    "operation_state": "running",
    "readings": {
      "water_low": {
        "value": 0,
        "unit": "boolean"
      },
      "water_level_percent": {
        "value": 55,
        "unit": "percent"
      }
    }
  }'
```

Expected result:

```text
message = Device state synced successfully.
device_readings_stored = 2
```

---

## 3. State sync: final actual output state

Use this after a command is acknowledged/executed.

Example: device actually applied pump ON at 66%.

### Postman

```http
POST <BASE_URL>/api/device/state
```

Body:

```json
{
  "device_uuid": "<DEVICE_UUID>",
  "device_type": "smart_fountain",
  "firmware_version": "postman-test-0.1",
  "operation_state": "running",
  "outputs": {
    "pump": {
      "enabled": true,
      "speed_percent": 66,
      "source": "device_state"
    }
  },
  "readings": {
    "water_low": {
      "value": 0,
      "unit": "boolean"
    },
    "water_level_percent": {
      "value": 55,
      "unit": "percent"
    }
  }
}
```

### curl

```bash
curl -X POST "<BASE_URL>/api/device/state" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "X-DEVICE-KEY: <DEVICE_API_KEY>" \
  -d '{
    "device_uuid": "<DEVICE_UUID>",
    "device_type": "smart_fountain",
    "firmware_version": "postman-test-0.1",
    "operation_state": "running",
    "outputs": {
      "pump": {
        "enabled": true,
        "speed_percent": 66,
        "source": "device_state"
      }
    },
    "readings": {
      "water_low": {
        "value": 0,
        "unit": "boolean"
      },
      "water_level_percent": {
        "value": 55,
        "unit": "percent"
      }
    }
  }'
```

Expected result:

```text
platform_outputs_updated = 1
device_readings_stored = 2
```

The returned pump state should be clean:

```json
{
  "enabled": true,
  "speed_percent": 66
}
```

---

## 4. State sync: full scene final state

Use this after a `scene_apply` command is executed.

### Postman

```http
POST <BASE_URL>/api/device/state
```

Body:

```json
{
  "device_uuid": "<DEVICE_UUID>",
  "device_type": "smart_fountain",
  "firmware_version": "postman-test-0.1",
  "operation_state": "running",
  "outputs": {
    "pump": {
      "enabled": true,
      "speed_percent": 60,
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
      "value": 55,
      "unit": "percent"
    }
  }
}
```

Expected result:

```text
platform_outputs_updated = 3
device_readings_stored = 2
```

Refresh the Smart Fountain dashboard. Pump, COB, and RGB states should match the state payload.

---

## 5. State sync: low-water safety

This simulates the device detecting low water and forcing pump OFF locally.

### Postman

```http
POST <BASE_URL>/api/device/state
```

Body:

```json
{
  "device_uuid": "<DEVICE_UUID>",
  "device_type": "smart_fountain",
  "firmware_version": "postman-test-0.1",
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

### curl

```bash
curl -X POST "<BASE_URL>/api/device/state" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "X-DEVICE-KEY: <DEVICE_API_KEY>" \
  -d '{
    "device_uuid": "<DEVICE_UUID>",
    "device_type": "smart_fountain",
    "firmware_version": "postman-test-0.1",
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
  }'
```

Expected dashboard behavior:

```text
water_low = Low
pump controls locked/disabled
pump state = OFF / 0%
```

Firmware rule:

```text
If water_low is true, firmware must force the pump OFF locally even if the server or dashboard asks for pump ON.
```

---

## 6. Poll pending command

Before polling, create a command from the Smart Fountain dashboard or Scenes page.

### Postman

```http
GET <BASE_URL>/api/device/commands?device_uuid=<DEVICE_UUID>
```

### curl

```bash
curl -X GET "<BASE_URL>/api/device/commands?device_uuid=<DEVICE_UUID>" \
  -H "Accept: application/json" \
  -H "X-DEVICE-KEY: <DEVICE_API_KEY>"
```

No command response:

```json
{
  "message": "No pending commands.",
  "command": null
}
```

Pending command response:

```json
{
  "message": "Pending command found.",
  "command": {
    "id": 297,
    "command_type": "output_set",
    "payload": {
      "state": {
        "enabled": true,
        "speed_percent": 66
      },
      "output": "pump",
      "source": "dashboard"
    },
    "status": "pending",
    "issued_at": "2026-05-11 23:14:22"
  }
}
```

Save the `command.id`. Use it as `<COMMAND_ID>` in the ACK requests.

---

## 7. ACK command as acknowledged

### Postman

```http
POST <BASE_URL>/api/device/commands/<COMMAND_ID>/ack
```

Body:

```json
{
  "device_uuid": "<DEVICE_UUID>",
  "status": "acknowledged"
}
```

### curl

```bash
curl -X POST "<BASE_URL>/api/device/commands/<COMMAND_ID>/ack" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "X-DEVICE-KEY: <DEVICE_API_KEY>" \
  -d '{
    "device_uuid": "<DEVICE_UUID>",
    "status": "acknowledged"
  }'
```

Expected result:

```text
status = acknowledged
```

---

## 8. ACK command as executed

Use this after the device applies the command locally.

### Postman

```http
POST <BASE_URL>/api/device/commands/<COMMAND_ID>/ack
```

Body:

```json
{
  "device_uuid": "<DEVICE_UUID>",
  "status": "executed"
}
```

### curl

```bash
curl -X POST "<BASE_URL>/api/device/commands/<COMMAND_ID>/ack" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "X-DEVICE-KEY: <DEVICE_API_KEY>" \
  -d '{
    "device_uuid": "<DEVICE_UUID>",
    "status": "executed"
  }'
```

Expected result:

```text
status = executed
execution_meaning = For persistent state commands, executed means the requested state was applied.
```

After this, send `/api/device/state` with the final actual output state.

---

## 9. ACK command as failed

Use this when firmware receives the command but cannot apply it.

### Postman

```http
POST <BASE_URL>/api/device/commands/<COMMAND_ID>/ack
```

Body:

```json
{
  "device_uuid": "<DEVICE_UUID>",
  "status": "failed",
  "message": "Pump driver did not respond."
}
```

### curl

```bash
curl -X POST "<BASE_URL>/api/device/commands/<COMMAND_ID>/ack" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "X-DEVICE-KEY: <DEVICE_API_KEY>" \
  -d '{
    "device_uuid": "<DEVICE_UUID>",
    "status": "failed",
    "message": "Pump driver did not respond."
  }'
```

Expected result:

```text
status = failed
```

A failed command is closed and should not be replayed automatically.

---

## 10. Complete manual test sequence

Use this full flow for `output_set`:

```text
1. Send online state/readings.
2. Create a pump command from the dashboard.
3. Poll command.
4. ACK acknowledged.
5. Apply output locally or pretend in Postman.
6. ACK executed.
7. POST final output state.
8. Refresh dashboard and confirm final state.
```

Use this full flow for `scene_apply`:

```text
1. Send online state/readings.
2. Apply a scene from the Scenes page.
3. Poll command.
4. ACK acknowledged.
5. Apply all scene outputs locally or pretend in Postman.
6. ACK executed.
7. POST full scene final state with pump/cob_light/rgb_light.
8. Refresh dashboard and confirm all states.
```

Use this full flow for low-water safety:

```text
1. POST water_low = 1 with pump OFF.
2. Confirm dashboard locks pump control.
3. Try to request pump ON from dashboard.
4. Laravel should keep pump safe or block/disable the action depending on current UI/backend state.
5. Firmware must still keep pump OFF locally.
```

---

## Troubleshooting

### 401 Missing device API key

Check the request has:

```http
X-DEVICE-KEY: <DEVICE_API_KEY>
```

### 401 Invalid device credentials

Check both:

```text
device_uuid
X-DEVICE-KEY
```

### 409 Device type mismatch

For Smart Fountain, request body must include:

```json
"device_type": "smart_fountain"
```

### No pending commands

Make sure you created a command from the web UI first and that the device is online.

### Command already closed

You already sent `executed`, `failed`, or it expired. Create a new command from the dashboard and test with the new ID.

### Dashboard not updating

After `/api/device/state`, refresh the Smart Fountain dashboard or wait for the frontend polling interval.

### Pump still locked

Send a fresh safe water reading:

```json
"water_low": {
  "value": 0,
  "unit": "boolean"
}
```

Then refresh the dashboard.
