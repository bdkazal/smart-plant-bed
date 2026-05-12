# Offline Time and Schedule Behavior

This document records the tested offline-time behavior for the Biztola IoT Platform, especially the Smart Plant Bed / Plant Buddy firmware.

## Confirmed status

The Smart Plant Bed can now keep schedule watering working when:

```text
Laravel server is offline
Wi-Fi/server API requests fail
ESP32 is power-cycled
```

This was tested successfully with:

```text
1. Device online first, so it can fetch config and sync time.
2. Laravel server stopped.
3. Device continued using cached config and local schedule fallback.
4. Device power-cycled while Laravel remained offline.
5. Device restored time from DS1307 RTC and triggered the scheduled watering at the expected local time.
```

## Time source priority

The device-side time source priority is:

```text
1. NTP time when internet access is available
2. Laravel server_time_utc from /api/device/config when Laravel is reachable
3. DS1307 RTC UTC time when offline or after reboot
4. No valid time: local schedule fallback must not run
```

## Laravel time fields

The device config API now exposes both UTC and local server time:

```json
{
  "server_time_utc": "2026-05-12T16:46:13+00:00",
  "server_time_local": "2026-05-12 22:46:13",
  "server_time": "2026-05-12 22:46:13",
  "config": {
    "timezone": "Asia/Dhaka",
    "timezone_offset_minutes": 360
  }
}
```

`server_time` is kept as a backward-compatible local-time alias.

## RTC storage rule

The RTC stores UTC time, not local wall-clock time.

Reason:

```text
UTC is stable across regions.
The Laravel device timezone remains the source of truth for local display and local schedule interpretation.
A device moved to another timezone should not keep an old country-specific RTC clock.
```

Example for Bangladesh:

```text
RTC UTC:          17:10:06
Local Asia/Dhaka: 23:10:06
```

The firmware log should show both forms after RTC restore:

```text
DS1307 RTC ready UTC: 2026-5-12 17:10:06
System time loaded from RTC UTC: 2026-5-12 17:10:06
Time source: RTC backup.
RTC restored local time: 2026-05-12 23:10:06
```

## Schedule fallback rule

Offline schedule fallback runs only when:

```text
Laravel is not recently reachable
watering_mode = schedule
cached config has at least one enabled schedule
device time is ready from NTP, Laravel UTC, or RTC
valve/pump is not already watering
```

A schedule is matched within the scheduled minute. For example:

```text
Schedule: 23:10:00
Can trigger: 23:10:00 through 23:10:59
```

The firmware prevents repeated triggering of the same schedule using a daily schedule trigger key.

## Dashboard/API responsibility

Laravel remains the source of truth for:

```text
device timezone
current timezone offset
schedule records
watering mode
watering durations
soil threshold and cooldown settings
```

The device caches the config and uses it only as a local fallback when Laravel is unavailable.

## Important implementation notes

- Do not hardcode Bangladesh time in firmware.
- Do not store local wall-clock time in RTC.
- Do not run local schedule fallback when no valid time source exists.
- Keep `server_time_utc` in the device config response.
- Keep `timezone_offset_minutes` in the config object for local conversion and display.
- For DST regions, Laravel should refresh the device config so the device receives the current offset.

## Future improvement

For maximum global/DST correctness, Laravel can later send precomputed future schedule run times:

```json
{
  "next_schedule_runs": [
    {
      "schedule_id": 9,
      "run_at_utc": "2026-05-12T18:06:00Z",
      "run_at_local": "2026-05-13 00:06:00",
      "duration_seconds": 30
    }
  ]
}
```

Then device schedule matching can become UTC-epoch comparison instead of local day/time matching.
