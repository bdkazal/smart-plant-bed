# Data Retention

The customer-facing History page shows recent useful records only, but the database still stores more records during MVP testing.

## Current rule

Do not manually delete old readings/logs just because the UI hides them.

Older records are useful for:

- debugging device behavior
- testing schedule behavior
- checking command lifecycle problems
- future charts and analytics
- customer support

## Recommended future pruning command

Future direction:

```bash
php artisan devices:prune-history
```

Recommended default policy:

```text
sensor_readings / device_readings: keep latest 500–1000 rows per device
watering_logs: keep latest 200–500 rows per device
device_commands: keep latest 200–500 rows per device
```

This command should run daily through Laravel Scheduler after the retention policy is finalized.

## Customer UI rule

Normal customer pages should not expose huge raw record counts such as:

```text
Sensor Readings: 8238
Watering Actions: 452
```

Use recent activity wording instead:

```text
Recent Sensor Readings
8 shown

Recent Watering Actions
16 shown
```

## Developer/debug rule

Raw payloads, metadata, and JSON details may remain available behind expandable Technical details during MVP development.

They should not be the main customer-facing interface.
