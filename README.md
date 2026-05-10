# Biztola IoT Platform / Smart Plant Bed MVP

This Laravel project is evolving from a single **Smart Plant Bed** backend into the shared **Biztola IoT Platform**.

The goal is one Laravel app where a user can own and manage many Biztola IoT products, including:

- Smart Plant Bed
- Smart Fountain
- Smart Planter
- Soil Monitor modules/probes
- Fan & Light Controller
- Smart Bathroom Controller
- future Biztola devices

The original Smart Plant Bed watering system remains the first working product, but new development should follow the platform model: devices have types, capabilities, outputs, readings, commands, scenes, schedules, and product-specific behavior.

---

## Documentation Map

The long project notes are split into focused files under `docs/`.

| File | Purpose |
| --- | --- |
| [`docs/PROJECT_STATUS.md`](docs/PROJECT_STATUS.md) | Current project status, completed Laravel features, active product focus, and priority order. |
| [`docs/PLATFORM_RULES.md`](docs/PLATFORM_RULES.md) | Critical platform rules: device status vs online status, command lifecycle, timed action vs persistent state devices, and Plant Bed isolation. |
| [`docs/DASHBOARD_UX.md`](docs/DASHBOARD_UX.md) | Customer-facing UI rules for Home, Automation, Schedules, History, filters, wording, and debug detail hiding. |
| [`docs/PLANT_BED.md`](docs/PLANT_BED.md) | Smart Plant Bed behavior, automation modes, watering states, commands, schedules, offline behavior, and history. |
| [`docs/SMART_FOUNTAIN.md`](docs/SMART_FOUNTAIN.md) | Smart Fountain persistent-state model, outputs, scenes, daily timeline schedule, water-level readings, and history. |
| [`docs/DEVICE_API.md`](docs/DEVICE_API.md) | Device API endpoints, authentication, `/api/device/state`, ACK meaning, and firmware flow. |
| [`docs/DATA_RETENTION.md`](docs/DATA_RETENTION.md) | History data retention, future pruning command, and customer UI record-count rules. |

---

## Current Product Focus

### Smart Plant Bed

Timed watering product.

Current features:

- mobile/PWA-style Home dashboard
- live/offline status from `last_seen_at`
- soil moisture, temperature, humidity display
- manual watering
- automation mode: `auto` or `schedule`
- watering schedules
- recent activity history

### Smart Fountain

Persistent state product.

Current features:

- output control using `output_set`
- full-device scenes using `scene_apply`
- default outputs: `pump`, `cob_light`, `rgb_light`
- daily timeline schedule: Day / Evening / Night
- water-level style readings using `device_readings`
- recent activity history

---

## Quick Platform Rules

Do not confuse lifecycle status with online status:

```text
devices.status = account/device lifecycle
last_seen_at   = live connection freshness
```

Do not mix product behavior:

```text
Plant Bed = timed action device
Smart Fountain = persistent state device
```

Command meaning differs by product type:

```text
Timed action devices: executed = action completed
Persistent state devices: executed = requested state applied
```

Failed/expired commands are closed and must not be replayed automatically when a device reconnects.

---

## API Overview

Device authentication uses:

```http
X-DEVICE-KEY: <device_api_key>
```

Main device endpoints:

```http
GET  /api/device/config
POST /api/device/readings
GET  /api/device/commands
POST /api/device/commands/{command}/ack
POST /api/device/heartbeat
POST /api/device/state
```

See [`docs/DEVICE_API.md`](docs/DEVICE_API.md) for the full contract.

---

## Tech Stack

### Backend

- Laravel
- MySQL
- Blade
- REST-style device API

### Device

- ESP32 / ESP32-C3 depending on product
- sensor modules
- relay/MOSFET/output drivers as needed

### Hosting

- VPS / custom domain

---

## Development Notes

This project is being built as a **commercial-minded MVP**, not as a hobby-only experiment.

Priority order:

1. reliable core behavior
2. clear device/server contract
3. simple customer UX
4. clean future expansion path

When opening a new thread, start with:

- [`docs/PROJECT_STATUS.md`](docs/PROJECT_STATUS.md)
- [`docs/PLATFORM_RULES.md`](docs/PLATFORM_RULES.md)
- the product-specific doc, such as [`docs/PLANT_BED.md`](docs/PLANT_BED.md) or [`docs/SMART_FOUNTAIN.md`](docs/SMART_FOUNTAIN.md)
