# Project Status

This project is in active MVP backend development.

## Goal

Build one Laravel-based Biztola IoT Platform where a user can own and manage multiple product types.

Current product focus:

- Smart Plant Bed
- Smart Fountain

Future product direction:

- Smart Planter
- Soil Monitor modules/probes
- Fan & Light Controller
- Smart Bathroom Controller
- other Biztola devices

## Current Laravel features

The Laravel side currently includes:

- user authentication
- device claiming / ownership flow
- device dashboard pages
- modern mobile/PWA-style Plant Bed Home, Automation, Schedules, and History pages
- sensor reading upload
- manual watering commands
- scheduled watering for Plant Bed
- auto watering by soil moisture threshold for Plant Bed
- command lifecycle handling
- shared command timeout / expiry handling service
- schedule CRUD for Plant Bed
- timezone-aware schedules for Plant Bed
- basic online / offline device presence using `last_seen_at`
- customer-friendly recent activity history with All / Actions / Readings / Errors filters
- Smart Fountain dashboard using generic `output_set` commands
- Smart Fountain full-scene presets using `scene_apply` commands
- Smart Fountain Daily Timeline schedule using scene ranges
- Smart Fountain platform config API
- Smart Fountain platform state sync API
- Smart Fountain dashboard auto-refresh/status endpoint
- shared history page that adapts by device type

## Platform foundation tables/models

Early IoT platform foundation includes:

- `device_capabilities`
- `device_outputs`
- `device_readings`
- `device_scenes`
- `device_schedule_ranges`
- `devices.last_reported_state`

## Device-side status

The Plant Bed firmware is now a working reference implementation for timed-action devices.

Confirmed device-side behavior:

- Laravel config fetch and cache
- Laravel UTC time sync from `server_time_utc`
- NTP time sync when available
- DS1307 RTC UTC backup time
- offline schedule fallback using cached schedules
- offline schedule continues after Laravel server outage
- offline schedule continues after ESP32 power loss/reboot when RTC time is valid
- OLED main status page
- OLED schedule/time page from display button
- display button can temporarily override dry-soil warning page
- physical manual watering button remains responsive while Laravel is offline

Important time model:

```text
1. NTP time when available
2. Laravel server_time_utc when Laravel is reachable
3. RTC UTC backup time when offline/rebooted
4. No valid time means local schedule fallback must not run
```

See `docs/OFFLINE_TIME_AND_SCHEDULE.md` for the full confirmed behavior and test notes.

## Priority order

1. reliable core behavior
2. clear device/server contract
3. simple customer UX
4. clean future expansion path
