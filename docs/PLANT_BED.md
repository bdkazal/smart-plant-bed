# Smart Plant Bed

The Smart Plant Bed is the first working product in the Laravel app.

It is a timed action device.

## Product goal

Build a simple, reliable smart watering system that allows a customer to:

- monitor plant-bed conditions remotely
- water manually from a dashboard
- automate watering by schedule
- automate watering by soil moisture threshold

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

## Offline behavior

`devices.status = active` does not mean the physical device is online.

The dashboard should calculate online/offline from `last_seen_at`.

When the device is offline:

```text
live sensor cards show N/A
manual watering is unavailable
old readings remain available in History
```

## History behavior

Plant Bed history shows recent customer-facing activity:

```text
Sensor Readings
Watering Activity
Device Actions
```

Raw JSON/debug details should stay collapsed under Technical details.
