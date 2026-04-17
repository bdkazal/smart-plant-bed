# Smart Plant Bed MVP

## Project Idea

A simple commercial smart plant-bed monitoring and watering system.

The product will use an ESP32-based device with sensors to monitor:

- temperature
- humidity
- soil moisture

The system will also control a solenoid valve for watering.

The device will connect to a Laravel backend API hosted on my VPS/domain, and customers will use a mobile-friendly web app to monitor and control it.

---

## Main Goal

Build a simple, useful, easy-to-operate commercial device and service.

Version 1 should focus only on core useful features and avoid extra complexity.

The system should still be designed in a way that allows future expansion.

---

## MVP Features

### Monitoring

- Show temperature
- Show humidity
- Show soil moisture
- Show last updated time
- Show device online/offline status

### Manual Control

- Manual switch/button to run solenoid valve
- Manual stop option

### Scheduled Watering

- User can define watering schedule
- Set start time
- Set watering duration
- Set days if needed

### Auto Watering

- Auto-run solenoid valve when soil moisture goes below threshold
- User can enable/disable auto mode
- User can set threshold value
- User can set watering duration
- Optional cooldown period between auto watering cycles

---

## Version 1 Scope

This project should NOT try to do too much at the beginning.

Version 1 should include only:

1. sensor display
2. manual valve control
3. schedule-based watering
4. soil-moisture-based auto watering

No extra fancy features in MVP.

---

## Possible Future Features

- multiple devices per user
- charts/history analytics
- notifications/alerts
- water tank monitoring
- fertilizer control
- rain prediction integration
- weather API integration
- QR-based device pairing
- subscription/payment system
- team/farm management
- admin dashboard
- native app or PWA
- MQTT or real-time communication
- offline fallback logic on device

---

## Target Users

Possible target users:

- home gardeners
- rooftop gardeners
- small-scale farmers
- nursery owners
- greenhouse users
- hobby growers

---

## Product Value

This product should help users:

- monitor plant-bed condition remotely
- reduce manual checking
- control watering remotely
- automate watering in a simple way
- save time and improve watering consistency

---

## Tech Stack (Planned)

### Device Side

- ESP32
- soil moisture sensor
- temperature/humidity sensor
- relay or proper driver for solenoid valve

### Backend

- Laravel
- MySQL
- Laravel Sanctum for user authentication
- REST API

### Frontend

- mobile-friendly web app
- first version may use Blade + JavaScript
- later version may use Vue.js

### Hosting

- domain + Hostinger VPS
- production server on VPS
- no Home Assistant or CasaOS for commercial version

---

## Architecture Idea

### Device

The ESP32 device will:

- read sensor values
- send readings to backend
- fetch config/commands from backend
- control solenoid valve

### Backend

Laravel will:

- manage users
- manage devices
- store sensor readings
- store schedule settings
- store auto-watering rules
- handle manual commands
- serve dashboard data

### Frontend

The web app will:

- allow login
- show device dashboard
- show current readings
- allow manual valve control
- allow schedule setup
- allow auto mode setup

---

## Authentication Strategy

### User Authentication

- Laravel Sanctum
- email/password login
- mobile web app uses user token

### Device Authentication

- separate device token or pairing token
- ESP32 should not use normal user login
- each device should have its own secure identity

---

## Pairing Idea

Possible pairing flow:

1. device has serial number / pairing code / QR code
2. customer creates account or logs in
3. customer adds device using pairing code
4. backend links device to that customer
5. device becomes visible in dashboard

---

## Device Communication Idea

### Sensor Upload

ESP32 sends:

- temperature
- humidity
- soil moisture
- current valve status
- timestamp / heartbeat

### Config Fetch

ESP32 fetches:

- manual run command
- schedule settings
- auto-watering settings

### Control Strategy

For MVP, use simple polling:

- send readings every 1 to 5 minutes
- fetch config every 20 to 60 seconds

This is simpler than using MQTT or WebSockets in version 1.

---

## Core Backend Resources

Likely main resources:

- users
- devices
- sensor_readings
- valve_commands
- watering_schedules
- auto_rules
- device_logs

---

## Core User Flows

### User Flow

1. user registers/logs in
2. user pairs device
3. user sees device dashboard
4. user sees latest readings
5. user manually runs valve if needed
6. user sets watering schedule
7. user sets auto watering threshold

### Device Flow

1. device connects to Wi-Fi
2. device authenticates to backend
3. device uploads readings
4. device fetches commands/settings
5. device runs valve when instructed
6. device reports status

---

## Main Dashboard Idea

The dashboard should show:

- device name
- online/offline status
- temperature
- humidity
- soil moisture
- valve status
- last updated time
- manual ON/OFF controls
- schedule summary
- auto mode summary

Keep the UI simple and mobile-friendly.

---

## Hardware Notes

Important hardware concerns:

- proper power design for ESP32 and solenoid valve
- relay/MOSFET/driver circuit
- flyback diode for valve protection
- sensor quality and reliability
- waterproofing and outdoor durability
- stable Wi-Fi connection
- safe enclosure

---

## Business Notes

This should be a simple product first, not a complex platform.

Main commercial promise:
“Monitor your plant bed remotely and automate watering simply.”

The focus should be reliability, ease of setup, and ease of use.

---

## Development Priority Order

### Phase 1

- user auth
- device model
- device registration/pairing

### Phase 2

- sensor reading upload
- reading storage
- device dashboard

### Phase 3

- manual valve ON/OFF control

### Phase 4

- watering schedule

### Phase 5

- auto watering by soil moisture threshold

### Phase 6

- product polish
- better UI
- better pairing flow
- logs and reliability improvements

---

## What I Should NOT Do Early

- do not start with native mobile app
- do not start with Home Assistant integration
- do not start with MQTT unless needed
- do not add too many extra features
- do not overcomplicate the frontend at the beginning

---

## Current Direction

Build a commercial MVP using:

- ESP32
- Laravel API
- MySQL
- mobile-friendly web app
- VPS hosting on my own domain

Keep version 1 simple, useful, and expandable.

IDEA:

1. Safety/Hazzard Notification from App, if ignored for a dangerous amount of time, then from Company Safety Agent.
   For example Solenoid valves open for several hours, or moisture is 0%, fire/smoke alarms etc.

2. Add light intensity sensor

3. Add rain sensor mode is on, then if it rains for a certain period of time, scheduled watering will be off.
