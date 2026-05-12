# Device API

The current backend/device contract uses REST-style API endpoints.

## Authentication

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

## Endpoints

```http
GET  /api/device/config
POST /api/device/readings
GET  /api/device/commands
POST /api/device/commands/{command}/ack
POST /api/device/heartbeat
POST /api/device/state
```

## `/api/device/config`

Endpoint:

```http
GET /api/device/config?device_uuid=<uuid>
```

Meaning:

```text
The device fetches its latest cloud configuration and syncs server time.
```

The config response includes UTC and local server time fields:

```json
{
  "message": "Device config fetched successfully.",
  "server_time_utc": "2026-05-12T16:46:13+00:00",
  "server_time_local": "2026-05-12 22:46:13",
  "server_time": "2026-05-12 22:46:13",
  "config": {
    "device_uuid": "...",
    "device_type": "smart_fountain",
    "timezone": "Asia/Dhaka",
    "timezone_offset_minutes": 360
  }
}
```

Time field meanings:

```text
server_time_utc          = canonical server time for RTC/NTP-style sync
server_time_local        = server time converted to the device timezone
server_time              = backward-compatible local-time alias
timezone                 = Laravel device timezone
timezone_offset_minutes  = current offset from UTC for that timezone
```

Firmware rules:

```text
Store RTC time as UTC.
Use timezone/timezone_offset_minutes for local display and local schedule interpretation.
Refresh config periodically because timezone offsets can change in DST regions.
```

## `/api/device/state`

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

Responses should be device/request-specific. Smart Fountain should not receive unnecessary Plant Bed legacy fields.

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

## Command ACK meaning

For timed action devices:

```text
executed = the timed action completed
```

For persistent state devices:

```text
executed = the requested state was applied
```

Firmware should generally follow:

```text
1. Poll pending command
2. ACK acknowledged
3. Apply command locally
4. ACK executed or failed
5. POST /api/device/state with actual latest state
```
