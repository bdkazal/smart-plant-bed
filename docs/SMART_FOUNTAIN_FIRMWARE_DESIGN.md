# Smart Fountain Firmware Design

This document defines the ESP32 firmware design for the **Biztola Smart Fountain**.

The firmware must be designed as a reusable **Biztola IoT Platform Core** plus a product-specific **Smart Fountain Module**.

This is important because the repository is no longer only a Smart Plant Bed project. Smart Fountain is one product inside the shared Biztola IoT Platform.

```text
Biztola ESP32 Firmware
├── Platform Core
│   ├── Wi-Fi connection
│   ├── device identity
│   ├── API client
│   ├── config fetch/cache
│   ├── UTC time sync
│   ├── local schedule engine
│   ├── state sync
│   ├── command polling
│   ├── command ACK lifecycle
│   └── retry/offline handling
│
└── Product Module
    └── Smart Fountain
        ├── pump output
        ├── COB light output
        ├── RGB light output
        ├── water-level sensor
        ├── local low-water safety
        ├── scene/output application
        └── offline daily timeline fallback
```

---

## Product decision: offline timeline is required

Smart Fountain daily timeline must work offline after the device has successfully fetched and cached its config.

Reason:

```text
Some customers may have unreliable internet or router/server downtime.
A decorative/functional fountain should continue its Day/Evening/Night behavior locally.
Local pump safety must also continue without internet.
```

So the correct V1 product behavior is:

```text
Laravel online    = Laravel is source of truth and creates scene_apply commands.
Laravel offline   = ESP32 uses cached daily timeline and cached scenes locally.
Wi-Fi offline     = ESP32 uses cached daily timeline and cached scenes locally.
Device rebooted   = ESP32 restores UTC time from RTC if available, then can continue cached timeline.
No valid time     = ESP32 must not run schedule fallback.
No cached config  = ESP32 must not invent scenes or schedules.
```

Offline timeline fallback is not a replacement for Laravel. It is a fallback using the last known valid Laravel config.

---

## Current Laravel readiness

The Laravel side is ready enough for firmware design and skeleton work.

Confirmed API flow from Postman:

```text
GET  /api/device/config
POST /api/device/state
GET  /api/device/commands
POST /api/device/commands/{id}/ack  acknowledged
POST /api/device/commands/{id}/ack  executed
POST /api/device/state              final actual output state
```

Important backend behavior:

```text
POST /api/device/state replaces output state instead of merging.
```

That means firmware should send clean, actual output state and should not depend on stale safety/debug fields remaining in Laravel.

Current Laravel schedule behavior:

```text
Laravel creates scheduled scene_apply commands while the device is online.
```

Firmware must add local fallback behavior:

```text
When Laravel/API is not reachable, ESP32 applies cached daily timeline scenes locally.
```

Laravel may need to expose the daily timeline and scene payloads in `/api/device/config` if they are not already present in the current config response.

---

## Product identity

```text
device_type: smart_fountain
behavior_type: persistent_state
```

Smart Fountain is a persistent-state device.

For this product:

```text
executed = requested state was applied
```

It does not mean the pump/light finished running.

Example:

```text
Pump ON at 60% keeps running until another command changes it, a timeline scene changes it, or local safety forces it OFF.
```

---

## Platform Core responsibilities

Platform Core must be reusable by future Biztola devices.

It should not know fountain-specific rules like pump, RGB, COB, or water-low safety.

### 1. Wi-Fi connection

Responsibilities:

```text
connect to configured Wi-Fi
track online/offline state
reconnect when disconnected
avoid blocking product safety logic while reconnecting
```

Rules:

```text
Wi-Fi failure must not freeze the device.
Local hardware safety must continue while offline.
Local schedule fallback must continue while offline when valid cached config and valid time exist.
No cloud commands can be received while offline.
```

### 2. Device identity

MVP identity values:

```text
device_uuid = stored in firmware config
X-DEVICE-KEY = stored in firmware config
firmware_version = sent in state payload
```

Storage can be simple for the first skeleton.

Recommended later improvement:

```text
move identity/config to NVS/Preferences instead of hardcoding
support factory provisioning / QR claim flow
```

### 3. API client

The API client owns HTTP details:

```text
base_url
headers
GET request helper
POST JSON request helper
HTTP status handling
JSON parse helper
network timeout handling
```

Required headers:

```http
Accept: application/json
Content-Type: application/json
X-DEVICE-KEY: <device_api_key>
```

### 4. Config fetch and cache

Endpoint:

```http
GET /api/device/config?device_uuid=<uuid>
```

Platform Core should parse and cache:

```text
server_time_utc
timezone
timezone_offset_minutes
device_type
behavior_type
supported commands
outputs from config
safety flags
scenes for Smart Fountain
daily_timeline schedule for Smart Fountain
```

Firmware rule:

```text
Config fetch should happen on boot and periodically after that.
A successful config fetch should be saved to persistent storage.
The cached config should survive ESP32 reboot.
```

V1 intervals:

```text
boot config fetch: required when online
periodic config refresh: every 5-15 minutes
manual refresh: allowed later
```

Recommended persistent storage:

```text
ESP32 Preferences/NVS for small config
LittleFS/SPIFFS later if config becomes large
```

### 5. UTC time sync

Canonical cloud time:

```text
server_time_utc
```

Firmware should store/sync time as UTC.

Rules:

```text
RTC stores UTC, not local wall-clock time.
timezone_offset_minutes is only for local display and local schedule interpretation.
Do not hardcode Asia/Dhaka in firmware.
```

Time source priority:

```text
1. NTP time when internet access is available
2. Laravel server_time_utc from /api/device/config when Laravel is reachable
3. RTC UTC backup when offline or after reboot
4. no valid time: do not run local schedule fallback
```

For reliable offline daily timeline after power loss, RTC is strongly recommended.

RTC status:

```text
RTC module = optional/TBD in hardware list
Product recommendation = include RTC for production units if offline schedule reliability matters
```

Without RTC:

```text
Offline schedule can continue only while ESP32 remains powered and already has valid time.
After power loss + no internet + no RTC, schedule fallback cannot safely run.
```

### 6. Local schedule engine

Platform Core should provide a reusable local schedule engine.

Responsibilities:

```text
hold cached schedules/timeline
convert UTC time to local time using timezone_offset_minutes
match current local time against schedule start times
prevent repeated firing of the same schedule in the same day
notify product module when a local schedule should apply
```

For Smart Fountain V1:

```text
schedule type = daily_timeline
schedule action = apply scene locally
periods = Day / Evening / Night
```

The local schedule engine should be generic enough for future products, but the Smart Fountain Module decides how to apply a scene.

### 7. State sync

Endpoint:

```http
POST /api/device/state
```

Platform Core should provide a generic state-sync method, but the product module should build the product-specific state payload.

Recommended Smart Fountain interval:

```text
state sync every 5 seconds during development
state sync every 10-30 seconds later, depending on product need
```

Firmware should send state even when no command exists, because state sync also updates `last_seen_at` and keeps the dashboard online.

When reconnecting after offline timeline fallback:

```text
POST /api/device/state with the actual current output state.
Do not try to ACK a local offline schedule as a cloud command because no cloud command exists.
Use source such as offline_timeline in the output state.
```

### 8. Command polling

Endpoint:

```http
GET /api/device/commands?device_uuid=<uuid>
```

Recommended interval:

```text
poll every 2-5 seconds during development
```

Platform Core responsibilities:

```text
poll command endpoint
ignore null command
validate command has id and command_type
prevent processing the same command repeatedly in one loop
pass supported commands to the product module
```

Smart Fountain supported commands:

```text
output_set
scene_apply
```

### 9. Command ACK lifecycle

Endpoint:

```http
POST /api/device/commands/{command}/ack
```

Required cloud command lifecycle:

```text
1. command received from poll endpoint
2. ACK acknowledged
3. apply hardware locally
4. ACK executed or failed
5. POST /api/device/state with actual latest state
```

Rules:

```text
ACK acknowledged means firmware accepted the command for processing.
ACK executed means firmware applied the requested state.
ACK failed means firmware could not apply the command.
Final /api/device/state is the trusted hardware truth.
```

Local offline schedule lifecycle is different:

```text
1. cached timeline time matches locally
2. apply cached scene locally
3. update local actual output state
4. when online, POST /api/device/state
```

No ACK is sent for offline local schedule actions because there is no Laravel command ID.

### 10. Retry/offline handling

Platform Core should track:

```text
wifi_connected
api_reachable
last_successful_api_at
last_config_fetch_at
last_state_sync_at
last_command_poll_at
last_offline_timeline_apply_at
```

Rules:

```text
If Wi-Fi/API is unavailable, skip cloud requests temporarily.
Use backoff after repeated failures.
Keep product loop and safety loop running.
Run cached timeline fallback when eligible.
Do not replay old failed/expired cloud commands.
Do not invent cloud command ACKs offline.
```

Offline Smart Fountain behavior:

```text
keep current local output state unless timeline or safety changes it
keep low-water pump protection active
apply cached daily timeline scenes at local start times
no cloud command polling while offline
state sync resumes when API becomes reachable
```

---

## Smart Fountain Module responsibilities

The Smart Fountain Module owns product-specific hardware and behavior.

It should expose a clean interface to Platform Core:

```text
begin()
loop()
buildStatePayload()
handleCommand(command)
applyOutput(outputKey, state, source)
applyScene(outputs, source)
applyTimelineScene(period, scene)
readSensors()
enforceSafety()
```

---

## Hardware placeholders

Current hardware choices are intentionally TBD.

```text
pump pin             = TBD
COB PWM pin          = TBD
RGB type/pins        = TBD
water sensor ADC pin = TBD
RTC module           = optional/TBD, recommended for reliable offline timeline after power loss
display              = optional/TBD
```

Do not block firmware architecture on exact pins.

Use a hardware mapping file or constants section like:

```cpp
constexpr int PIN_PUMP = -1;        // TBD
constexpr int PIN_COB_PWM = -1;     // TBD
constexpr int PIN_WATER_ADC = -1;   // TBD
```

For skeleton code, hardware writes can be stubbed or logged until exact modules are selected.

---

## Outputs

Default Smart Fountain outputs:

```text
pump
cob_light
rgb_light
```

### Pump output

State schema:

```json
{
  "enabled": true,
  "speed_percent": 60
}
```

Rules:

```text
enabled is boolean
speed_percent is clamped to 0-100
if enabled=false, speed_percent should become 0
if water_low=true, pump must be forced OFF
```

MVP hardware implementation options:

```text
simple relay/MOSFET ON/OFF first
PWM speed control later if supported by pump driver
```

If hardware cannot support speed control yet:

```text
speed_percent > 0 means ON
report requested speed_percent only if the firmware can honestly apply it
otherwise report actual simple state, for example enabled=true, speed_percent=100
```

### COB light output

State schema:

```json
{
  "enabled": true,
  "brightness_percent": 40
}
```

Rules:

```text
brightness_percent is clamped to 0-100
if enabled=false, brightness_percent should become 0
COB light may still work when water_low=true
```

### RGB light output

State schema:

```json
{
  "enabled": true,
  "brightness_percent": 35,
  "color": "#FFB066",
  "effect": "warm_glow"
}
```

Supported effects from Laravel contract:

```text
solid
breathing
slow_rainbow
warm_glow
water_shimmer
night_mode
```

V1 skeleton may implement only:

```text
solid
warm_glow as solid fallback
unsupported effects as solid fallback
```

Later firmware can add real animations.

Rules:

```text
RGB may still work when water_low=true.
Invalid color should fall back to #000000 or previous valid color.
brightness_percent is clamped to 0-100.
```

---

## Readings

Smart Fountain readings:

```text
water_low
water_level_percent
water_level_raw
```

### water_level_raw

Raw ADC value from the water-level sensor.

```text
unit = adc
```

### water_level_percent

Calculated percentage from raw sensor reading.

```text
unit = percent
range = 0-100
```

Calibration is TBD.

Skeleton can use placeholder mapping:

```text
raw dry value = TBD
raw wet value = TBD
```

### water_low

Boolean safety reading.

```text
unit = boolean
value = 1 means low water / unsafe for pump
value = 0 means water level OK
```

Water-low detection should be debounced to avoid pump flickering.

Recommended V1 logic:

```text
sample water sensor repeatedly
calculate rolling average
apply low threshold and recovery threshold
require several consecutive low samples before water_low=true
require several consecutive safe samples before water_low=false
```

Use placeholders until real sensor calibration:

```text
WATER_LOW_THRESHOLD_RAW = TBD
WATER_RECOVER_THRESHOLD_RAW = TBD
```

---

## Local low-water safety

This is a hard firmware rule.

If `water_low=true`:

```text
1. turn pump OFF immediately
2. force local pump state to enabled=false, speed_percent=0
3. ignore pump ON commands
4. allow COB/RGB light commands
5. allow timeline scenes to apply lights/RGB, but force pump OFF
6. report pump OFF in the next /api/device/state payload
```

If a pump ON command arrives while water is low:

```text
ACK acknowledged
apply safety override locally
ACK executed if the safe resulting state was applied
POST /api/device/state with pump OFF and source=water_safety
```

If an offline timeline scene includes pump ON while water is low:

```text
apply scene lights/RGB
force pump OFF locally
mark local source as water_safety or offline_timeline_safety
sync state when online
```

Reason:

```text
The backend may already rewrite unsafe pump commands, but hardware safety must not depend on Laravel or internet access.
```

---

## Daily timeline offline fallback

Smart Fountain has three customer-facing daily timeline blocks:

```text
Day
Evening
Night
```

Each block has:

```text
period name
start local time
scene id/name
scene output payload
```

Firmware needs the last known valid timeline and scenes from Laravel config.

Recommended config shape for firmware:

```json
{
  "config": {
    "device_type": "smart_fountain",
    "timezone": "Asia/Dhaka",
    "timezone_offset_minutes": 360,
    "daily_timeline": {
      "enabled": true,
      "ranges": [
        {
          "period": "day",
          "start_time": "06:00",
          "scene_id": 1,
          "scene_name": "Day Fountain",
          "outputs": {
            "pump": { "enabled": true, "speed_percent": 60 },
            "cob_light": { "enabled": true, "brightness_percent": 40 },
            "rgb_light": { "enabled": true, "brightness_percent": 35, "color": "#FFB066", "effect": "warm_glow" }
          }
        },
        {
          "period": "evening",
          "start_time": "18:00",
          "scene_id": 2,
          "scene_name": "Night Glow",
          "outputs": {}
        },
        {
          "period": "night",
          "start_time": "23:00",
          "scene_id": 4,
          "scene_name": "All Off",
          "outputs": {}
        }
      ]
    }
  }
}
```

If Laravel does not currently return this shape, add it before final firmware integration.

Offline timeline eligibility:

```text
cached config exists
cached daily_timeline.enabled = true
cached ranges are valid
valid UTC time exists from NTP, Laravel, or RTC
Laravel/API is not currently reachable, or command polling is failing
```

Trigger rule:

```text
Apply a period scene once when local HH:MM matches the period start_time.
Prevent repeated application during the same minute/day.
```

Recommended trigger key:

```text
YYYY-MM-DD + period + start_time
```

Manual command vs offline timeline priority:

```text
When online, cloud commands win because Laravel is source of truth.
When offline, cached timeline may apply scenes at scheduled start times.
A manual command applied before going offline remains active until the next cached timeline start time.
```

Reconnect behavior:

```text
When API returns, immediately POST current actual state.
Then fetch latest config.
Then resume normal command polling.
Do not replay offline timeline events as command ACKs.
```

---

## Command handling design

### output_set

Firmware flow:

```text
1. validate output key
2. ACK acknowledged
3. if output=pump and water_low=true, force pump OFF
4. otherwise apply requested output state
5. update local actual output state
6. ACK executed or failed
7. POST final /api/device/state
```

### scene_apply

Firmware flow:

```text
1. validate outputs object
2. ACK acknowledged
3. apply each known output
4. if water_low=true, force pump OFF but still apply lights
5. update local actual output states
6. ACK executed if all required outputs reached safe actual state
7. ACK failed only if hardware application fails seriously
8. POST final /api/device/state with all actual outputs
```

Scene rule:

```text
Scenes are full-device presets.
If an output is omitted, do not assume the old server state is correct.
For V1, apply provided outputs and report actual final full state.
```

---

## State payload design

Recommended normal state payload:

```json
{
  "device_uuid": "<DEVICE_UUID>",
  "device_type": "smart_fountain",
  "firmware_version": "fountain-dev-0.1",
  "operation_state": "running",
  "outputs": {
    "pump": {
      "enabled": false,
      "speed_percent": 0,
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
    },
    "water_level_raw": {
      "value": 2840,
      "unit": "adc"
    }
  }
}
```

Recommended offline timeline state source:

```json
{
  "pump": {
    "enabled": true,
    "speed_percent": 60,
    "source": "offline_timeline"
  }
}
```

Recommended low-water state source:

```json
{
  "pump": {
    "enabled": false,
    "speed_percent": 0,
    "source": "water_safety"
  }
}
```

Because Laravel now replaces output state instead of merging stale fields, firmware should keep payloads clean.

Avoid unnecessary debug fields in output state unless they are intentionally part of the current state.

---

## Main loop design

The firmware loop should avoid long blocking delays.

Recommended loop order:

```text
1. handle Wi-Fi reconnect/non-blocking network maintenance
2. read sensors
3. enforce local safety
4. update UTC/local time
5. run offline daily timeline fallback if eligible
6. run output animations/effects
7. fetch config if due and online
8. sync state if due and online
9. poll command if due and online
10. process one cloud command at a time
```

Important:

```text
Safety must run even when API requests fail.
Offline timeline must run even when API requests fail, if valid cached config and valid time exist.
RGB effects must not block command polling/state sync.
HTTP timeouts should be short enough to keep local behavior responsive.
```

---

## Suggested file/module structure

Exact Arduino/PlatformIO structure can be adjusted later, but use this direction:

```text
firmware/smart-fountain/
├── platformio.ini                 # or Arduino project config
├── src/
│   ├── main.cpp
│   ├── config/
│   │   └── DeviceSecrets.example.h
│   ├── platform/
│   │   ├── BiztolaDeviceIdentity.h
│   │   ├── BiztolaApiClient.h
│   │   ├── BiztolaApiClient.cpp
│   │   ├── BiztolaConfig.h
│   │   ├── BiztolaConfigCache.h
│   │   ├── BiztolaTimeSync.h
│   │   ├── BiztolaLocalSchedule.h
│   │   └── BiztolaCommand.h
│   └── products/
│       └── smart_fountain/
│           ├── SmartFountainModule.h
│           ├── SmartFountainModule.cpp
│           ├── FountainOutputs.h
│           ├── FountainOutputs.cpp
│           ├── FountainTimeline.h
│           ├── FountainTimeline.cpp
│           ├── WaterLevelSensor.h
│           └── WaterLevelSensor.cpp
└── README.md
```

For the first skeleton, fewer files are acceptable. But keep the separation clear:

```text
Platform code talks to Laravel and manages generic time/cache/scheduling.
Product code talks to fountain hardware and applies fountain scenes safely.
```

---

## First firmware skeleton target

The first ESP32 skeleton should implement:

```text
[ ] compile successfully
[ ] connect to Wi-Fi
[ ] fetch /api/device/config
[ ] parse server_time_utc and config.device_type
[ ] cache latest valid config locally
[ ] parse/cache daily_timeline if present
[ ] restore cached config after reboot
[ ] sync/restore valid UTC time
[ ] keep a local output state object
[ ] read placeholder water sensor value
[ ] enforce water_low pump safety locally
[ ] POST /api/device/state
[ ] poll /api/device/commands
[ ] handle output_set
[ ] handle scene_apply
[ ] ACK acknowledged
[ ] ACK executed after local apply
[ ] ACK failed for unsupported/invalid command
[ ] POST final actual state after every handled command
[ ] apply cached daily timeline locally while offline
[ ] POST current actual state after reconnect
```

Hardware may be stubbed initially:

```text
pump write = log/apply local state only
COB write = log/apply local state only
RGB write = log/apply local state only
water sensor = analogRead if pin known, otherwise placeholder value
RTC = optional stub until module is chosen
```

---

## Non-goals for first skeleton

Do not build these yet unless required:

```text
customer Wi-Fi provisioning UI
QR claim flow
full RGB animation engine
display UI
OTA update
encrypted secure element storage
advanced retry queue
multi-product firmware binary
```

Offline Smart Fountain daily timeline fallback is not a non-goal anymore. It is part of the V1 firmware direction.

---

## Testing plan

### 1. Online boot test

Expected:

```text
Wi-Fi connected
config fetched
device_type = smart_fountain
server_time_utc parsed
config cached
initial state posted
Laravel dashboard shows device online
```

### 2. Output command test

Expected flow:

```text
create output_set from dashboard
firmware polls command
ACK acknowledged
apply output locally
ACK executed
POST final actual state
Laravel dashboard shows final hardware truth
```

### 3. Scene command test

Expected flow:

```text
apply scene from Laravel
firmware polls scene_apply
ACK acknowledged
apply pump/cob_light/rgb_light
ACK executed
POST final full state
Laravel dashboard matches actual outputs
```

### 4. Low-water safety test

Expected:

```text
simulate water_low=true
firmware forces pump OFF
pump ON command is ignored/forced safe locally
lights still work
state sync reports water_low=1 and pump OFF
```

### 5. Offline timeline test without reboot

Expected:

```text
device online first and config cached
stop Laravel or disconnect Wi-Fi
valid device time remains available
at next Day/Evening/Night start time, firmware applies cached scene locally
water_low safety still overrides pump
when online returns, firmware posts actual current state
```

### 6. Offline timeline test after reboot

Expected with RTC:

```text
device online first and config cached
RTC has valid UTC time
stop Laravel / disconnect Wi-Fi
power-cycle ESP32
firmware restores cached config
firmware restores UTC from RTC
at next timeline start time, firmware applies cached scene locally
```

Expected without RTC:

```text
if device reboots offline and has no valid time, firmware does not run timeline fallback
safety still runs
outputs may remain default/safe until time is restored
```

---

## Design rules to protect future expansion

```text
Do not hardcode Smart Fountain behavior into Platform Core.
Do not make Plant Bed behavior depend on Smart Fountain commands.
Do not treat all devices as timed watering devices.
Do not treat all devices as persistent-state devices.
Do not use soil_moisture for Smart Fountain water-level meaning.
Do not mark cloud commands executed before hardware/local state is applied.
Do not trust ACK executed as final dashboard truth; always send /api/device/state.
Do not send ACK for local offline timeline actions because no cloud command exists.
Do not depend on Laravel for pump safety.
Do not run offline timeline without valid cached config and valid time.
```

---

## Next step

Before firmware skeleton starts, confirm or add the Laravel config shape for:

```text
config.daily_timeline.enabled
config.daily_timeline.ranges[].period
config.daily_timeline.ranges[].start_time
config.daily_timeline.ranges[].scene_id
config.daily_timeline.ranges[].scene_name
config.daily_timeline.ranges[].outputs
```

Then start the ESP32 Smart Fountain firmware skeleton with:

```text
Platform Core first
config cache + UTC time + local schedule engine
Smart Fountain Module second
hardware pins left as TBD placeholders
safe local pump behavior from the beginning
offline daily timeline fallback included from the beginning
```
