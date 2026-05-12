# Smart Fountain

The Smart Fountain uses the persistent state platform model.

## Device type

```text
smart_fountain
```

## Default capabilities

```text
pump_output
dimmable_light
rgb_light
water_level_sensor
```

## Default outputs

```text
pump
cob_light
rgb_light
```

## Default scenes

```text
Day Fountain
Night Glow
Display Mode
All Off
```

## Persistent state behavior

Smart Fountain is not a timed watering device.

For Smart Fountain:

```text
executed = the requested state was applied
```

It does not mean the output finished running.

Example:

```text
Pump ON at 60% continues running until another command changes it.
```

## Commands

Smart Fountain uses generic platform commands:

```text
output_set
scene_apply
```

## Dashboard command flow

Dashboard command is a request, not proof of real hardware state.

Expected flow:

```text
Dashboard sends command
→ Laravel creates pending output_set / scene_apply
→ Device polls command
→ Device acknowledges command
→ Device applies command locally
→ Device ACKs executed or failed
→ Device POSTs /api/device/state with actual hardware state
→ Dashboard auto-refresh shows the confirmed actual state
```

Backend ACK behavior:

```text
ACK executed may update the server-known requested/applied state quickly.
POST /api/device/state is the final trusted hardware truth.
```

Recommended real-device flow:

```text
1. Poll pending command
2. ACK acknowledged
3. Apply output locally
4. ACK executed or failed
5. POST /api/device/state with actual outputs/readings
```

## Scene behavior

Smart Fountain scenes are full-device presets. A scene controls all three default outputs:

```text
pump
cob_light
rgb_light
```

If an output should be off in a scene, store it as disabled/0%, not omitted.

Example All Off scene:

```json
{
  "pump": {
    "enabled": false,
    "speed_percent": 0
  },
  "cob_light": {
    "enabled": false,
    "brightness_percent": 0
  },
  "rgb_light": {
    "enabled": false,
    "brightness_percent": 0,
    "color": "#000000",
    "effect": "solid"
  }
}
```

Applying a scene creates one command:

```text
command_type = scene_apply
```

## Low water pump protection

Smart Fountain uses water-level safety to protect the pump.

When latest `water_low` is true:

```text
pump must stay OFF
lights/RGB may still work
```

Laravel has a Smart Fountain safety service:

```text
app/Services/SmartFountainSafetyService.php
```

The service checks the latest `water_low` device reading and can force pump state to:

```json
{
  "enabled": false,
  "speed_percent": 0,
  "safety_override": "water_low"
}
```

Current backend protection:

```text
output_set pump ON while water_low=true → command payload is changed to safe pump OFF
scene_apply while water_low=true       → scene payload keeps lights/RGB but forces pump OFF
schedule scene_apply while water_low=true uses the same DeviceCommand protection
```

Important firmware rule:

```text
ESP32 must also protect the pump locally.
If water_low is true, firmware should immediately turn pump OFF and ignore pump ON commands.
Laravel protection is helpful, but hardware safety must not depend only on internet/server availability.
```

## Daily Timeline schedule

Smart Fountain uses a simple V1 daily timeline schedule instead of an advanced automation page.

The customer-facing model is three continuous timeline blocks:

```text
Day
Evening
Night
```

The three blocks cover the full 24 hours without overlap or gaps:

```text
Day ends when Evening starts.
Evening ends when Night starts.
Night ends when Day starts.
```

Each block applies one scene at its start time using a `scene_apply` command.

Example:

```text
06:00 → Day Fountain
18:00 → Night Glow
23:00 → All Off
```

Manual scene apply still works from the Scenes page. It changes the current output state immediately through a `scene_apply` command. It does not permanently edit the daily timeline. The next scheduled timeline block will apply its configured scene at its start time.

Manual testing command:

```bash
php artisan smart-fountain:check-schedules --day=6 --time=06:00
```

## Water level readings

The Smart Fountain may physically use a capacitive soil moisture sensor, but in Laravel its product role is:

```text
water_level_sensor
```

Do not store this as `soil_moisture` for the fountain, because the measurement meaning is water-level / dry-run protection, not soil moisture.

Recommended Smart Fountain readings:

```text
water_level_percent
water_level_raw
water_low
```

If `water_low` is true, the pump should remain off to protect the motor.

## History behavior

Smart Fountain history uses the shared history page:

```http
GET /devices/{device}/history
```

It shows recent customer-facing activity:

```text
Device Readings
Device Actions
```

Raw JSON/debug details should stay collapsed under Technical details.
