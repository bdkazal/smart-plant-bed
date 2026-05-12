# Smart Fountain Firmware Design

This document defines the first ESP32 firmware design for the **Biztola Smart Fountain**.

The goal is not to build one-off fountain firmware. The firmware should be organized as a reusable **Biztola IoT Platform Core** plus a product-specific **Smart Fountain Module**.

```text
Biztola ESP32 Firmware
├── Platform Core
│   ├── Wi-Fi connection
│   ├── device identity
│   ├── API client
│   ├── config fetch
│   ├── UTC time sync
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
        └── scene/output application
```

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
Pump ON at 60% keeps running until another command changes it or local safety forces it OFF.
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

### 4. Config fetch

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
```

Smart Fountain Module may use the config output states as initial desired/default output state.

Firmware rule:

```text
Config fetch should happen on boot and periodically after that.
```

V1 intervals:

```text
boot config fetch: required when online
periodic config refresh: every 5-15 minutes
manual refresh: allowed later
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
1. NTP time when available
2. Laravel server_time_utc from /api/device/config
3. RTC UTC backup when offline or after reboot
4. no valid time: do not run local schedule fallback
```

For Smart Fountain V1, Laravel currently creates scheduled timeline commands when the device is online. So the first fountain firmware skeleton does not need local schedule execution yet.

Still, Platform Core should keep the UTC time design reusable because Plant Bed already needs offline schedule behavior and future products may need it too.

### 6. State sync

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

### 7. Command polling

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

### 8. Command ACK lifecycle

Endpoint:

```http
POST /api/device/commands/{command}/ack
```

Required lifecycle:

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

Unknown command types:

```text
ACK failed with a short message.
Do not crash.
Do not silently mark unknown commands executed.
```

Unknown output keys:

```text
ignore safely or fail only that command, depending on command type.
For V1, fail command if the target output is unknown.
```

### 9. Retry/offline handling

Platform Core should track:

```text
wifi_connected
api_reachable
last_successful_api_at
last_config_fetch_at
last_state_sync_at
last_command_poll_at
```

Rules:

```text
If Wi-Fi/API is unavailable, skip cloud requests temporarily.
Use backoff after repeated failures.
Keep product loop and safety loop running.
Do not replay old failed/expired commands.
Do not invent cloud commands offline.
```

Offline Smart Fountain behavior:

```text
keep current local output state, unless safety changes it
keep low-water pump protection active
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
readSensors()
enforceSafety()
```

---

## Hardware placeholders

Current hardware choices are intentionally TBD.

```text
pump pin            = TBD
COB PWM pin         = TBD
RGB type/pins       = TBD
water sensor ADC pin = TBD
RTC module          = optional/TBD
display             = optional/TBD
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
5. report pump OFF in the next /api/device/state payload
```

If a pump ON command arrives while water is low:

```text
ACK acknowledged
apply safety override locally
ACK executed if the safe resulting state was applied
POST /api/device/state with pump OFF and source=water_safety
```

Reason:

```text
The backend may already rewrite unsafe pump commands, but hardware safety must not depend on Laravel or internet access.
```

Recommended pump state in low-water state sync:

```json
{
  "pump": {
    "enabled": false,
    "speed_percent": 0,
    "source": "water_safety"
  }
}
```

---

## Command handling design

### output_set

Example command payload:

```json
{
  "output": "pump",
  "state": {
    "enabled": true,
    "speed_percent": 60
  },
  "source": "dashboard"
}
```

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

Example command payload:

```json
{
  "scene_id": 1,
  "scene_name": "Day Fountain",
  "source": "scene:1",
  "outputs": {
    "pump": {
      "enabled": true,
      "speed_percent": 60
    },
    "cob_light": {
      "enabled": true,
      "brightness_percent": 40
    },
    "rgb_light": {
      "enabled": true,
      "brightness_percent": 35,
      "color": "#FFB066",
      "effect": "warm_glow"
    }
  }
}
```

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

Recommended low-water state payload:

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
      "source": "water_safety"
    }
  },
  "readings": {
    "water_low": {
      "value": 1,
      "unit": "boolean"
    },
    "water_level_percent": {
      "value": 10,
      "unit": "percent"
    },
    "water_level_raw": {
      "value": 1234,
      "unit": "adc"
    }
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
4. run output animations/effects
5. fetch config if due and online
6. sync state if due and online
7. poll command if due and online
8. process one command at a time
```

Important:

```text
Safety must run even when API requests fail.
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
│   │   ├── BiztolaTimeSync.h
│   │   ├── BiztolaScheduler.h        # future/shared optional
│   │   └── BiztolaCommand.h
│   └── products/
│       └── smart_fountain/
│           ├── SmartFountainModule.h
│           ├── SmartFountainModule.cpp
│           ├── FountainOutputs.h
│           ├── FountainOutputs.cpp
│           ├── WaterLevelSensor.h
│           └── WaterLevelSensor.cpp
└── README.md
```

For the first skeleton, fewer files are acceptable. But keep the separation clear:

```text
Platform code talks to Laravel.
Product code talks to fountain hardware.
```

---

## First firmware skeleton target

The first ESP32 skeleton should implement:

```text
[ ] compile successfully
[ ] connect to Wi-Fi
[ ] fetch /api/device/config
[ ] parse server_time_utc and config.device_type
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
```

Hardware may be stubbed initially:

```text
pump write = log/apply local state only
COB write = log/apply local state only
RGB write = log/apply local state only
water sensor = analogRead if pin known, otherwise placeholder value
```

---

## Non-goals for first skeleton

Do not build these yet unless required:

```text
customer Wi-Fi provisioning UI
QR claim flow
local offline fountain schedule execution
full RGB animation engine
display UI
OTA update
encrypted secure element storage
advanced retry queue
multi-product firmware binary
```

Keep the first firmware small and testable.

---

## Testing plan

### 1. Online boot test

Expected:

```text
Wi-Fi connected
config fetched
device_type = smart_fountain
server_time_utc parsed
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

### 5. Offline test

Expected:

```text
stop Laravel or disconnect Wi-Fi
firmware keeps running local loop
low-water safety still works
no cloud command polling while offline
state sync resumes after reconnect
```

---

## Design rules to protect future expansion

```text
Do not hardcode Smart Fountain behavior into Platform Core.
Do not make Plant Bed behavior depend on Smart Fountain commands.
Do not treat all devices as timed watering devices.
Do not treat all devices as persistent-state devices.
Do not use soil_moisture for Smart Fountain water-level meaning.
Do not mark commands executed before hardware/local state is applied.
Do not trust ACK executed as final dashboard truth; always send /api/device/state.
Do not depend on Laravel for pump safety.
```

---

## Next step

After this document is accepted, start the ESP32 Smart Fountain firmware skeleton with:

```text
Platform Core first
Smart Fountain Module second
hardware pins left as TBD placeholders
safe local pump behavior from the beginning
```
