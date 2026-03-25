# Production deploy: POWITHETA scheduled sync (Windows Server + XAMPP)

Step-by-step for deploying the **Laravel scheduler + POWITHETA automatic SAP sync** on **Windows Server** using **XAMPP** (Apache + PHP + MySQL typical layout).

Adjust drive letters and folder names to match your server.

---

## What you are enabling

1. **Application code** from `git pull` (scheduler, command, admin UI, migrations).
2. **`.env`** with `APP_TIMEZONE` and correct DB/SAP settings.
3. **Database migrations** for `powitheta_sync_histories` (if not already applied).
4. **Windows Task Scheduler** running **`php artisan schedule:run` every minute** so Laravel can fire **06:00** and **18:00 WITA** (with `APP_TIMEZONE=Asia/Makassar`).

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
   | `APP_TIMEZONE` | `Asia/Makassar` for **WITA** (06:00 / 18:00 wall-clock for scheduled sync). |
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

You should see **`powitheta:refresh-from-sap --scheduled`** twice, with **Next Due** at **06:00** and **18:00** and offset **`+08:00`** when using `Asia/Makassar`.

Optional: run once (does nothing unless the current minute matches a scheduled time):

```bat
C:\xampp\php\php.exe artisan schedule:run
```

---

## 7. Windows Task Scheduler — run `schedule:run` every minute

This is **one-time** per server (until the path or PHP binary changes).

### 7.1 Create the task

1. Open **Task Scheduler** (`taskschd.msc`).
2. **Create Task…** (not a simple “Basic Task” if you need full control).
3. **General** tab:
   - **Name**: e.g. `ARK-GS Laravel Scheduler`
   - Select **Run whether user is logged on or not**.
   - Check **Run with highest privileges** only if your policy requires it (often not needed).
   - **Configure for**: your Windows Server version.

### 7.2 Trigger (every minute)

1. Tab **Triggers** → **New…**
2. **Begin the task**: **On a schedule**
3. **Settings**: **Daily** — set start date/time to **today** and a time **in the past** or **now** (e.g. 00:00:00).
4. **Advanced settings**:
   - Check **Repeat task every**: `1 minute`
   - **For a duration of**: **Indefinitely** (or **1 day** and add a second trigger if your version behaves oddly — most use **Indefinitely**).
5. **Enabled**: checked → OK.

### 7.3 Action (call PHP + Artisan)

1. Tab **Actions** → **New…**
2. **Action**: **Start a program**
3. **Program/script**: full path to PHP, e.g.  
   `C:\xampp\php\php.exe`
4. **Add arguments**:  
   `artisan schedule:run`
5. **Start in (optional)** — **required** for Laravel:  
   `D:\project\ark-gs`  
   (your real application root, same folder as `artisan`)

Click OK.

### 7.4 Conditions / Settings

- **Conditions**: Uncheck **Start only if on AC power** (for servers).
- **Settings**: Prefer **Allow task to be run on demand**; **If task fails, restart every**… optional.

### 7.5 Credentials

Save the task. Windows will prompt for the **password** of the user that will run the job. Use the **service account** that has rights to the project folder and to reach **MySQL** and **SAP SQL Server**.

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
| Wrong time (not WITA) | `APP_TIMEZONE` in `.env`; then `php artisan config:clear` and `config:cache`. |
| Permission errors in logs | User running the task has write access to `storage\` and `bootstrap\cache\`. |
| SAP / DB errors | Same as manual “Sync from SAP”; verify `SAP_SQL_*` and firewall from app server to SQL Server. |

---

## Related documentation

- [planned-powitheta-scheduled-sync.md](planned-powitheta-scheduled-sync.md)
- [architecture.md](architecture.md) — deployment section
- [decisions.md](decisions.md) — POWITHETA timezone + scheduler decision

---

*Last updated: 2026-03-25*
