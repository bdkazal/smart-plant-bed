# Smart Plant Bed

The Smart Plant Bed is the first working product in the Laravel app.

It is a timed action device.

## Product goal

Build a simple, reliable smart watering system that allows a customer to:

- monitor plant-bed conditions remotely
- water manually from a dashboard
- automate watering by schedule
- automate watering by soil moisture threshold
- keep scheduled watering working during temporary server/network outages

## Current model

The Plant Bed uses two separate concepts.

### Automation mode

```text
auto
schedule
```

### Watering state

```text
idle
waiting
watering
stopping
```

Manual watering is not a persistent mode. Manual watering is a direct user action / trigger source.

## Commands

Current Plant Bed commands:

```text
valve_on
valve_off
```

For Plant Bed:

```text
executed = the timed watering action completed
```

For `valve_on`, executed means the watering run finished and the valve/pump has been turned off after the requested duration.

## Tables used by Plant Bed

Plant Bed still uses:

```text
sensor_readings
watering_logs
watering_rules
watering_schedules
device_commands
```

The older `sensor_readings` table should not be removed until the platform migration is mature.

## Automation behavior

Auto mode uses soil moisture threshold.

Schedule mode uses saved watering schedules.

Automation settings include:

- watering mode
- soil moisture threshold
- max watering duration
- cooldown minutes
- local manual duration
- device timezone

Auto mode requires a valid soil moisture reading.

## Schedule behavior

Plant Bed schedules are timed watering actions:

```text
Monday 06:00 → Water for 30 seconds
```

Schedule form duration should follow the Automation max watering duration setting.

## Offline time and schedule behavior

Plant Bed offline schedule fallback is now confirmed working after both:

```text
Laravel server outage
ESP32/device power loss and reboot
```

The device can run cached schedules locally when Laravel is unavailable.

Time source priority:

```text
1. NTP time when available
2. Laravel server_time_utc from /api/device/config
3. DS1307 RTC UTC backup time
4. No valid time: local schedule fallback disabled
```

The RTC stores UTC time. The configured device timezone and timezone offset from Laravel are used to convert that UTC clock into local schedule/display time.

Example:

```text
RTC UTC:          17:10:06
Local Asia/Dhaka: 23:10:06
```

Offline schedule fallback runs only when:

```text
Laravel is not recently reachable
watering_mode = schedule
cached schedules contain an enabled schedule for current day/time
device time is ready from NTP, Laravel UTC, or RTC
valve/pump is not already watering
```

The schedule match window is the scheduled minute:

```text
Schedule 23:10:00 may trigger between 23:10:00 and 23:10:59
```

See `docs/OFFLINE_TIME_AND_SCHEDULE.md` for the full details.

## Offline behavior

`devices.status = active` does not mean the physical device is online.

The dashboard should calculate online/offline from `last_seen_at`.

When the device is offline:

```text
live sensor cards show N/A
manual watering is unavailable from dashboard
old readings remain available in History
device can continue confirmed local schedule fallback from cached config
```

Offline auto-mode watering from cached soil readings/config is not yet treated as a confirmed behavior in this document. It should be documented separately if/when tested.

## History behavior

Plant Bed history shows recent customer-facing activity:

```text
Sensor Readings
Watering Activity
Device Actions
```

Raw JSON/debug details should stay collapsed under Technical details.
