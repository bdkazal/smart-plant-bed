# Dashboard UX

The web dashboard should feel like a customer-facing mobile/PWA product, not a developer/debug admin screen.

## Current Plant Bed customer pages

The Plant Bed pages use a modern mobile-style layout:

```text
Home
Automation
Schedules
History
```

## Plant Bed Home behavior

The Home page is for monitoring and direct manual action.

It should show:

- live/offline status
- soil moisture gauge
- temperature
- humidity
- manual watering control
- current watering state
- last synced time

It should not expose internal debug details.

When the device is offline, live sensor cards should show:

```text
N/A
```

instead of old readings. Old readings remain available in History.

## Automation page behavior

The Automation page owns watering mode and safety limits.

Current watering modes:

```text
auto
schedule
```

Manual watering is not a persistent mode. It is a direct user action from the Home page.

## Schedule page behavior

Plant Bed schedules are timed watering actions:

```text
Monday 06:00 → Water for 30 seconds
```

Schedule duration should respect the Automation max watering duration setting.

## History page behavior

History is a customer-friendly Recent Activity page.

It has filter links:

```text
All
Actions
Readings
Errors
```

Plant Bed:

```text
Readings = real sensor readings from sensor_readings
Actions = watering logs + device commands
Errors = failed/expired/cancelled logs and commands
```

Smart Fountain:

```text
Readings = generic device_readings
Actions = scene/output/device commands
Errors = failed/expired/cancelled commands + water_low alerts
```

Raw JSON and internal payloads should stay hidden inside expandable Technical details.

Normal customer UI should not show huge database totals or full pagination.

## Customer wording rule

Avoid internal terms such as:

```text
desired_state
reported_state
payload
metadata
command_type
```

Use user-facing labels such as:

```text
Waiting for device
Applying
Applied
Failed
Expired
Live
Offline
Recent Activity
Technical details
```
