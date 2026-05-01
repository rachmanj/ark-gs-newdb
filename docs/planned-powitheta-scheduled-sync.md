# Scheduled POWITHETA staging refresh + SAP sync

**Status**: **Implemented** (2026-03-25). Scheduling details updated **2026-04-30** (fixed wall times, staging offset, monthly history job). Production still requires **OS-level** `php artisan schedule:run` every minute (see [architecture.md](architecture.md) deployment section and [decisions.md](decisions.md)).

**Depends on**: [planned-powitheta-po-upsert.md](planned-powitheta-po-upsert.md) — implemented: upsert `performConvertToPo()` + unique `doc_num` + staging-only truncate for manual and scheduled paths.

---

## Current behavior

1. **Staging only**: Scheduled job truncates **`powithetas`** only, then SAP import + **`performConvertToPo()`** (upsert rules match manual flow).

2. **Command**: `php artisan powitheta:refresh-from-sap` with **`--scheduled`** for Laravel-scheduled runs; writes **`powitheta_sync_histories`**, sets cache flag **`powitheta_scheduled_sync_in_progress`** during execution.

3. **Schedule** (`app/Console/Kernel.php`, `config('app.timezone')` / **`APP_TIMEZONE`**):
    - **`powitheta:refresh-from-sap --scheduled`**: **06:05** and **12:05** daily, **`withoutOverlapping(20)`**, only when **`powitheta_schedule.json`** has **`enabled`** true.
    - **`staging-modules:sync-from-sap --scheduled`**: **06:10** and **12:10** daily (five minutes after each POWITHETA run), **`withoutOverlapping(25)`**, only when **`enabled`** and **`staging_modules_enabled`** are true.
    - **`history:generate-monthly`**: **23:45 on the last calendar day** of each month **`APP_TIMEZONE`** — implemented as **`dailyAt('23:45')`** plus **`when`** `now()->day === now()->daysInMonth`; **`withoutOverlapping(60)`**. Not gated on POWITHETA **`enabled`** (captures dashboards into **`histories`** for monthly reporting). `php artisan schedule:list` may still show cron **`45 23 * * *`** because Laravel lists the base expression; **`when`** filters actual runs.

   **Wall-clock POWITHETA + staging-module times are fixed in code** (`Kernel`). They are **not** read from **`PowithetaScheduleSettings::normalizedSyncTimes()`** at runtime. JSON **`sync_times`** remains in **`defaultConfig()`** and the superadmin form (**`06:05` / `12:05`**) for clarity and consistency; changing those fields alone does **not** reschedule Artisan until **`Kernel`** is updated.

4. **Configuration**: `storage/app/powitheta_schedule.json` (gitignored; created by defaults or **Admin → POWITHETA sync schedule**): **`enabled`**, **`staging_modules_enabled`**, **`sync_times`** (defaults **`06:05`**, **`12:05`**), **`sap_date_mode`**, optional custom SAP date range for scheduled runs (`getScheduledSapDatePayload()` merged into scheduled `Request`).

5. **Timezone**: `config('app.timezone')` from **`APP_TIMEZONE`** (default **`Asia/Makassar`** for WITA). **`dailyAt`** / history **`when`** gate use this timezone.

6. **UX**: Public **`GET /api/powitheta-sync-status`** + ticker partial; superadmin page lists **recent sync history**.

---

## Architecture

```mermaid
flowchart LR
  cron[Cron_or_TaskScheduler]
  artisan[php_artisan_schedule_run]
  kernel[Console_Kernel_schedule]
  cmd["powitheta:refresh_scheduled"]
  truncStaging[Truncate_powithetas_only]
  sync[SAP_import_upsert]
  cron --> artisan --> kernel --> cmd
  cmd --> truncStaging --> sync
```

Separate scheduled commands (**staging-modules**, **`history:generate-monthly`**) register in the same `Kernel::schedule`; see [architecture.md](architecture.md).

---

## Key files

| Area | Path |
|------|------|
| Commands | `app/Console/Commands/PowithetaRefreshFromSapCommand.php`, `StagingModulesSyncFromSapCommand.php`, `GenerateMonthlyHistoriesCommand.php` |
| Monthly history capture | `app/Services/MonthlyHistoryCaptureService.php`, `HistoryController::generate_monthly` |
| Schedule | `app/Console/Kernel.php` |
| Settings | `app/Services/PowithetaScheduleSettings.php`, `storage/app/powitheta_schedule.json` |
| History model | `app/Models/PowithetaSyncHistory.php` |
| Admin UI | `app/Http/Controllers/PowithetaScheduleController.php`, `resources/views/admin/powitheta-schedule.blade.php` |
| Sync + upsert | `app/Http/Controllers/PowithetaController.php` (`sync_from_sap`, `performConvertToPo`) |
| SAP dates | `app/Services/SapService.php` (`resolvePowithetaSapDateRange`) |
| Status API | `routes/web.php` → `GET /api/powitheta-sync-status` |
| Ticker | `resources/views/templates/partials/powitheta-sync-ticker.blade.php` (included from main layout + login) |
| App timezone | `config/app.php` (`APP_TIMEZONE`) |

---

## Production deploy (after `git pull`)

1. `.env`: `APP_TIMEZONE=Asia/Makassar` (or chosen zone), valid `APP_KEY`, DB, SAP credentials.
2. `composer install`, `php artisan migrate --force`, `php artisan config:cache` as usual.
3. **One-time**: configure cron or Task Scheduler so **`php artisan schedule:run`** runs **every minute** from the app root.
4. Verify: `php artisan schedule:list` shows POWITHETA at **06:05** and **12:05**, staging-modules at **06:10** and **12:10**, **`history:generate-monthly`** with cron **`45 23 * * *`** (effective **month-end 23:45** via **`when`**), all with **`+08:00`** when using Makassar.

**Windows Server + XAMPP**: step-by-step guide — [deploy-production-windows-xampp.md](deploy-production-windows-xampp.md).

---

## Alignment with PO upsert plan

| Topic | Behavior |
|--------|----------|
| Before SAP import | Truncate **`powithetas`** only |
| After import | **`performConvertToPo()`** upsert (new PO full create; existing PO header fields only) |
| Manual “Sync from SAP” | Modal dates; scheduled runs use JSON SAP date settings |

---

## References

- [docs/planned-powitheta-po-upsert.md](planned-powitheta-po-upsert.md)
- [docs/architecture.md](architecture.md) — POWITHETA scheduled sync + Mermaid
- `MEMORY.md` [020], [022] — timezone + schedule parity + monthly budget alignment

---

*Updated 2026-03-25: status set to implemented; operational and timezone guidance added. Updated 2026-04-30: fixed Kernel times, staging +5 min offset, monthly `history`, JSON vs Kernel note, verify expectations. Updated further: **`history:generate-monthly`** — **month-end 23:45** (`dailyAt` + **`when(day === daysInMonth)`).*
