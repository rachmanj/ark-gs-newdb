# Production deploy: POWITHETA scheduled sync (Windows Server + XAMPP)

Step-by-step for deploying the **Laravel scheduler + POWITHETA automatic SAP sync** on **Windows Server** using **XAMPP** (Apache + PHP + MySQL typical layout).

Adjust drive letters and folder names to match your server.

---

## What you are enabling

1. **Application code** from `git pull` (scheduler, command, admin UI, migrations).
2. **`.env`** with `APP_TIMEZONE` and correct DB/SAP settings.
3. **Database migrations** for `powitheta_sync_histories` (if not already applied).
4. **Windows Task Scheduler** running **`php artisan schedule:run` every minute** so Laravel can fire **POWITHETA 06:05 / 12:05**, **staging-modules +5 min**, and **`history:generate-monthly`** on the **1st at 10:05** in **`APP_TIMEZONE`** (typically **`Asia/Makassar`**).

Without step 4, the web app works, but **automatic** syncs never run.

---

## Before you start

- **Git** installed on the server, or another way to deploy the same code revision.
- **Composer** on the server (or run `composer install` from a machine that has Composer and copy `vendor` — not ideal; prefer Composer on server).
- **XAMPP PHP path** known, e.g. `C:\xampp\php\php.exe` (open **Command Prompt** and run `where php` after `cd` to project if you use XAMPP’s PHP in PATH).
- **Service account** (recommended): a Windows user that will run the scheduled task and has **read/write** to the project folder (especially `storage\`, `bootstrap\cache\`).

---

## 1. Deploy the code

1. Open **Command Prompt** or **PowerShell** **as Administrator** (only if your deploy path requires it).

2. Go to the project directory (example):

   ```bat
   cd /d D:\project\ark-gs
   ```

3. Pull the release branch:

   ```bat
   git fetch origin
   git pull origin main
   ```

   Use your real branch name if not `main`.

---

## 2. Install PHP dependencies

```bat
composer install --no-dev --optimize-autoloader
```

If `composer` is not in PATH, use the full path to `composer.phar` or install Composer globally.

---

## 3. Environment file (`.env`)

1. On the server, copy from `.env.example` if you are creating fresh, or **merge** new keys into the existing production `.env`.

2. Set at least:

   | Variable | Example / note |
   |----------|----------------|
   | `APP_ENV` | `production` |
   | `APP_DEBUG` | `false` |
   | `APP_KEY` | Must match existing production key (do not regenerate if app already deployed). |
   | `APP_URL` | Public URL of the site, e.g. `https://erp.example.com` |
   | `APP_TIMEZONE` | `Asia/Makassar` for **WITA** (`dailyAt` / scheduled jobs use this zone). |
   | `DB_*` | Production MySQL host, database, user, password. |
   | SAP SQL variables | `SAP_SQL_HOST`, `SAP_SQL_PORT`, etc., as today. |

3. Never commit `.env`. Confirm `storage\app\powitheta_schedule.json` exists on first run after deploy (Laravel defaults apply if missing; superadmin can save **Admin → POWITHETA sync schedule**).

---

## 4. Laravel optimization and database

Run from the **project root** using **XAMPP’s PHP** if you have multiple PHP installs:

```bat
C:\xampp\php\php.exe artisan config:clear
C:\xampp\php\php.exe artisan migrate --force
C:\xampp\php\php.exe artisan config:cache
C:\xampp\php\php.exe artisan route:cache
```

Notes:

- **`migrate --force`** is required in production.
- If migrations fail, fix DB permissions or backup/restore first; do not leave the app half-migrated.

---

## 5. Storage and cache directories

Ensure these exist and are writable by the process that runs **both** Apache and the scheduled task:

- `storage\logs`
- `storage\framework\cache`
- `storage\framework\sessions`
- `storage\framework\views`
- `storage\app`
- `bootstrap\cache`

On Windows, usually the **same user** as the Apache service or the dedicated app user needs **Modify** on the project folder (or at least `storage` and `bootstrap\cache`).

---

## 6. Verify scheduled jobs (manual, one-time)

Still in the project root:

```bat
C:\xampp\php\php.exe artisan schedule:list
```

You should see **POWITHETA** twice (**06:05**, **12:05**), **staging-modules** twice (**06:10**, **12:10**), and **`history:generate-monthly`** with **Next Due** on the next month’s **1st at 10:05**, with offset **`+08:00`** when using `Asia/Makassar`.

Optional: run once (does nothing unless the current minute matches a scheduled time):

```bat
C:\xampp\php\php.exe artisan schedule:run
```

---

## 7. Windows Task Scheduler — run `schedule:run` every minute

This is **one-time** per server (until the path or PHP binary changes).

### 7.1 GUI limitation (repeat every 1 minute)

On many **Windows Server** builds, the Task Scheduler **Triggers** dialog only allows **Repeat task every** down to **5 minutes**, not 1 minute. Laravel’s docs recommend **`schedule:run` every minute** so jobs like `dailyAt('06:47')` are never missed.

**If scheduled jobs fall on multiples of five minutes** (current app defaults: POWITHETA **06:05** / **12:05**, staging **06:10** / **12:10**, history **monthlyOn 10:05**): a GUI task repeating **every 5 minutes** starting on **`:00`** is usually enough (**7.4**).

**If you might use other minutes** (`06:03`, `09:47`, …) or insist on Laravel’s canonical **every minute**: use **`schtasks`** (**7.2**) or XML **PT1M** (**7.3**). Note: POWITHETA wall times are fixed in **`Kernel`**; **`sync_times`** in Admin does **not** change cron.

**Acceptable GUI-only approach:** repeat every **5 minutes**, trigger start **e.g. `00:00:00` daily**, duration **Indefinitely** — runs at `:00`, `:05`, `:10`, … so **06:05**, **10:05**, **12:10**, etc. are hit. **Do not** start the repeat at e.g. `06:02`, or you can miss **:05** slots.

---

### 7.2 Recommended: `schtasks` / one-minute schedule

Create a **batch file** so the working directory is always correct (adjust paths):

**`C:\Scripts\ark-gs-laravel-scheduler.bat`** (create folder as needed):

```bat
@echo off
cd /d D:\project\ark-gs
C:\xampp\php\php.exe artisan schedule:run >> C:\Scripts\ark-gs-scheduler.log 2>&1
```

Open **Command Prompt as Administrator** and run (replace `YOURDOMAIN\svcuser` and task name; you will be prompted for password):

```bat
schtasks /Create /TN "ARK-GS Laravel Scheduler" /TR "C:\Scripts\ark-gs-laravel-scheduler.bat" /SC MINUTE /MO 1 /RU "YOURDOMAIN\svcuser" /RP
```

- **`/SC MINUTE /MO 1`** = every **1 minute** (works even when the GUI minimum is 5).
- **`/RP`** with no value prompts for the account password once.

To update an existing task later:

```bat
schtasks /Delete /TN "ARK-GS Laravel Scheduler" /F
REM then run Create again with new paths
```

Verify: **Task Scheduler** → find the task → **Last Run Time** / **Last Run Result**; check `C:\Scripts\ark-gs-scheduler.log` if you added logging.

---

### 7.3 Alternative: GUI task + manual XML (advanced)

If you must use the graphical task editor, you can **export** a task from another machine that supports **1 minute** repetition as **XML**, edit **`<Repetition><Interval>PT1M</Interval>`**, then **Import** on the server. This is fragile across versions; prefer **7.2**.

---

### 7.4 GUI-only manual task (if you skip `schtasks`)

1. Open **Task Scheduler** (`taskschd.msc`).
2. **Create Task…**
3. **General** tab:
   - **Name**: e.g. `ARK-GS Laravel Scheduler`
   - **Run whether user is logged on or not**
   - **Configure for**: your Windows Server version
4. **Triggers** → **New…** → **Daily**, start **00:00:00**, **Repeat every 5 minutes** (if 1 minute is unavailable), **for a duration of Indefinitely** — ensure the start time is **on a 5-minute boundary** (`:00`, `:05`, …) so **06:00** and **18:00** are hit.
5. **Actions** → **New…** → **Start a program**  
   - Program: `C:\xampp\php\php.exe`  
   - Arguments: `artisan schedule:run`  
   - **Start in:** `D:\project\ark-gs`
6. **Conditions**: Uncheck **Start the task only if the computer is on AC power** (servers).
7. Save and enter credentials for the **service account** that can read/write the project and reach **MySQL** and **SAP SQL Server**.

---

## 8. Smoke test the task

1. In Task Scheduler, right-click the task → **Run**.
2. Check `storage\logs\laravel.log` for errors.
3. Optionally run from CMD as the **same user**:

   ```bat
   cd /d D:\project\ark-gs
   C:\xampp\php\php.exe artisan schedule:run
   ```

   Expect: `No scheduled commands are ready to run` unless the clock is exactly on a scheduled minute.

---

## 9. XAMPP / Apache

- **Apache** serves the app; it does **not** run the Laravel scheduler. The **Task Scheduler** job is what runs the scheduler.
- Restart Apache after deploy if you change PHP extensions or `php.ini` (usually not required for this feature alone).
- If you use **OPcache**, reload or restart Apache after deploy so new code is picked up.

---

## 10. After future `git pull` deployments

Each release:

1. `git pull`
2. `composer install --no-dev --optimize-autoloader`
3. `php artisan migrate --force`
4. `php artisan config:cache` (and `route:cache` if you use it)

**Do not** recreate the Task Scheduler job unless the **project path** or **php.exe** path changes.

---

## Troubleshooting

| Symptom | Check |
|--------|--------|
| Scheduled sync never runs | Task Scheduler **Last Run Result** (0x0 = success); **History** enabled; correct **Start in** folder; PHP path. |
| **Event 203 / 101 — “Action failed to start” / “Launch Failure”** (e.g. **Error Value: 2147942667** ≈ `0x8007010B`, often *“The directory name is invalid”*) | See **below** — fix **Start in**, **php.exe** path, and account. |
| GUI only allows repeat every 5 minutes | Use **`schtasks /SC MINUTE /MO 1`** with a `.bat` wrapper (section 7.2). |
| Wrong time (not WITA) | `APP_TIMEZONE` in `.env`; then `php artisan config:clear` and `config:cache`. |
| Permission errors in logs | User running the task has write access to `storage\` and `bootstrap\cache\`. |
| SAP / DB errors | Same as manual “Sync from SAP”; verify `SAP_SQL_*` and firewall from app server to SQL Server. |

### Task Scheduler Event 203 — `php.exe` launch failure (2147942667)

This usually means Task Scheduler cannot **start** the action (before Laravel runs). Common causes:

1. **“Start in” (Start in / optional working directory) is wrong or empty**  
   It must be the **folder that contains `artisan`** (your app root), e.g. `D:\project\ark-gs`.  
   If it points to a **non-existent** path, a **mapped drive** that is not available at logon, or a typo, Windows often reports **Launch Failure** / invalid directory.

2. **`C:\xampp\php\php.exe` does not exist on this server**  
   Production may use another drive, another XAMPP install, or only `php.exe` elsewhere. In **cmd** (as the task user):  
   `dir C:\xampp\php\php.exe`  
   If missing, set **Program/script** to the **real** PHP path (e.g. `C:\xampp\php\php.exe` on the machine where the app lives).

3. **Arguments** (`artisan schedule:run`) must be in **Add arguments**, not merged into the path incorrectly.

4. **Run as user** (`Run whether user is logged on or not`) must have **read/execute** on `php.exe` and **read** access to the **Start in** folder. If the task runs as a service account, confirm that account can access `C:\xampp` and the project path (no folder-only permissions blocking).

5. **Quick test** (same user as the task):  
   `cd /d D:\your\path\to\ark-gs`  
   `C:\xampp\php\php.exe artisan schedule:run`  
   If this fails in cmd, fix paths before Task Scheduler.

---

### Optional: use a `.bat` wrapper (avoids “Start in” mistakes)

If the task still fails, run a **single `.bat`** as the **Program** and put everything inside:

```bat
@echo off
cd /d D:\project\ark-gs
C:\xampp\php\php.exe artisan schedule:run
```

Set **Start in** to the folder where the `.bat` lives **or** the folder containing `artisan` (same as above). Point the task’s **Program** to the full path of the `.bat`.

---

## Related documentation

- [planned-powitheta-scheduled-sync.md](planned-powitheta-scheduled-sync.md)
- [architecture.md](architecture.md) — deployment section
- [decisions.md](decisions.md) — POWITHETA timezone + scheduler decision

---

*Last updated: 2026-04-30 (schedule times aligned with `Kernel`; Task Scheduler GUI 5-minute minimum: see sections 7.1–7.2)*
