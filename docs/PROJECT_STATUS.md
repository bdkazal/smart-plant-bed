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

The ESP32/device-side runtime logic should be designed after the Laravel/API contract is stable.

## Priority order

1. reliable core behavior
2. clear device/server contract
3. simple customer UX
4. clean future expansion path
