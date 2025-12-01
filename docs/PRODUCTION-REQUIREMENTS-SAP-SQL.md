# Production Server Requirements - SAP SQL Server Integration

## Date: 2025-11-24

## Overview

This document outlines all requirements for deploying the SAP SQL Server direct integration features to production. The implementation includes direct SQL queries for GRPO, Incoming, Migi, and Powitheta data synchronization.

---

## 1. PHP Requirements

### PHP Version

-   **Minimum**: PHP 7.3 or PHP 8.0+
-   **Recommended**: PHP 8.1 or higher for better performance

### Required PHP Extensions

#### 1.1 SQL Server Extensions (CRITICAL)

**For Windows Server with XAMPP:**

1. **Download Microsoft Drivers for PHP for SQL Server:**

    - Visit: https://docs.microsoft.com/en-us/sql/connect/php/download-drivers-php-sql-server
    - Download the appropriate version for your PHP version (PHP 7.3, 7.4, 8.0, or 8.1)
    - Choose **Thread Safe (TS)** version for XAMPP
    - Extract the ZIP file

2. **Identify Your PHP Version:**

    ```cmd
    cd C:\xampp\php
    php -v
    ```

    Note: PHP version (e.g., 7.4.33) and architecture (x86 or x64)

3. **Copy Extension Files:**

    - From extracted folder, copy these files to `C:\xampp\php\ext\`:
        - `php_sqlsrv_XX_ts.dll` (where XX is your PHP version, e.g., `php_sqlsrv_74_ts.dll`)
        - `php_pdo_sqlsrv_XX_ts.dll` (e.g., `php_pdo_sqlsrv_74_ts.dll`)
    - Choose the correct architecture folder (x86 or x64) from the extracted files

4. **Enable Extensions in php.ini:**

    - Open `C:\xampp\php\php.ini` in a text editor
    - Find the `[ExtensionList]` section or add at the end:

    ```ini
    extension=php_sqlsrv_74_ts.dll
    extension=php_pdo_sqlsrv_74_ts.dll
    ```

    - Replace `74` with your PHP version number (e.g., `73`, `74`, `80`, `81`)

5. **Restart Apache:**
    - Stop and start Apache from XAMPP Control Panel

**For Linux (Ubuntu/Debian):**

```bash
sudo apt-get install php-sqlsrv php-pdo-sqlsrv
```

**For Linux (CentOS/RHEL):**

```bash
sudo yum install php-sqlsrv php-pdo-sqlsrv
```

**Verify Installation:**

```cmd
# Windows (Command Prompt)
cd C:\xampp\php
php -m | findstr sqlsrv

# Linux
php -m | grep sqlsrv
php -m | grep pdo_sqlsrv
```

#### 1.2 Other Required Extensions (Standard Laravel)

-   `pdo_mysql` - For MySQL database connection
-   `mbstring` - String handling
-   `xml` - XML processing
-   `openssl` - Encryption
-   `json` - JSON processing
-   `curl` - HTTP requests
-   `zip` - File compression
-   `gd` or `imagick` - Image processing (if needed)

**Verify All Extensions:**

```bash
php -m
```

---

## 2. Microsoft ODBC Driver for SQL Server

The PHP sqlsrv extension requires the Microsoft ODBC Driver for SQL Server to be installed on the server.

### Installation

#### Windows Server 2016 (XAMPP) - RECOMMENDED

1. **Download ODBC Driver:**

    - Visit: https://docs.microsoft.com/en-us/sql/connect/odbc/download-odbc-driver-for-sql-server
    - Download **ODBC Driver 17 for SQL Server** or **ODBC Driver 18 for SQL Server**
    - Choose the Windows x64 installer

2. **Install ODBC Driver:**

    - Run the downloaded `.msi` installer
    - Accept the license agreement
    - Complete the installation

3. **Verify Installation:**
    - Open **ODBC Data Source Administrator (64-bit)**:
        - Press `Win + R`, type `odbcad32.exe`, press Enter
    - Go to **Drivers** tab
    - Look for "ODBC Driver 17 for SQL Server" or "ODBC Driver 18 for SQL Server"

**Alternative: Command Line Verification**

```cmd
# Open Command Prompt as Administrator
cd C:\Windows\System32
odbcinst -q -d
# Should show "ODBC Driver 17 for SQL Server" or "ODBC Driver 18 for SQL Server"
```

#### Linux (Ubuntu/Debian)

```bash
# Add Microsoft repository
curl https://packages.microsoft.com/keys/microsoft.asc | sudo apt-key add -
curl https://packages.microsoft.com/config/ubuntu/20.04/prod.list | sudo tee /etc/apt/sources.list.d/mssql-release.list

# Install ODBC Driver
sudo apt-get update
sudo ACCEPT_EULA=Y apt-get install -y msodbcsql18
# For older systems, use msodbcsql17

# Install unixODBC development headers
sudo apt-get install -y unixodbc-dev
```

#### Linux (CentOS/RHEL)

```bash
# Add Microsoft repository
sudo curl -o /etc/yum.repos.d/mssql-release.repo https://packages.microsoft.com/config/rhel/8/prod.repo

# Install ODBC Driver
sudo ACCEPT_EULA=Y yum install -y msodbcsql18
# For older systems, use msodbcsql17

# Install unixODBC development headers
sudo yum install -y unixODBC-devel
```

---

## 3. Network Requirements

### 3.1 Connectivity

-   **Outbound TCP connection** to SAP SQL Server on port **1433** (default) or configured port
-   **Firewall rules** must allow outbound connections to SAP SQL Server
-   **Network latency**: Should be < 100ms for optimal performance

### 3.2 DNS Resolution

-   SAP SQL Server hostname must be resolvable from production server
-   **Windows**: Test with: `ping arkasrv2` or `nslookup arkasrv2`
-   **Linux**: Test with: `ping arkasrv2` or `nslookup arkasrv2`

### 3.3 Port Access

**Windows (Command Prompt):**

```cmd
# Test connectivity (if telnet is enabled)
telnet arkasrv2 1433

# OR use PowerShell
Test-NetConnection -ComputerName arkasrv2 -Port 1433
```

**Linux:**

```bash
# Test connectivity
telnet arkasrv2 1433
# OR
nc -zv arkasrv2 1433
```

---

## 4. Environment Configuration

### 4.1 Required Environment Variables

Add to `.env` file on production server:

```env
# SAP SQL Server Direct Access
SAP_SQL_HOST=your_sql_host
SAP_SQL_PORT=1433
SAP_SQL_DATABASE=your_sql_database
SAP_SQL_USERNAME=your_sql_username
SAP_SQL_PASSWORD=your_sql_password
```

**Alternative (Fallback):**
If not specified, system will use existing SAP configuration:

```env
SAP_SERVER_URL=your_sql_host
SAP_DB_NAME=your_sql_database
SAP_USER=your_sql_username
SAP_PASSWORD=your_sql_password
```

### 4.2 Security Best Practices

1. **Use Read-Only SQL User** (Recommended)

    - Create SQL Server user with `SELECT` permissions only
    - No `INSERT`, `UPDATE`, `DELETE`, or `ALTER` permissions

2. **Secure Credentials**

    - Never commit `.env` file to version control
    - Use environment-specific `.env` files
    - Consider using secret management systems (AWS Secrets Manager, Azure Key Vault, etc.)

3. **Encrypted Connections** (Production)
    - Update `config/database.php` to use encrypted connection:
    ```php
    'sap_sql' => [
        // ... other config
        'options' => [
            'TrustServerCertificate' => false, // Set to false in production
            'Encrypt' => true,
        ],
    ],
    ```

---

## 5. Database Permissions

### 5.1 Required SQL Server Permissions

The SQL Server user needs **SELECT** permissions on these tables:

**For GRPO:**

-   `OPDN`, `PDN1`, `OPOR`, `OITM`, `@MIS_CCDPT`, `OWHS`, `OPRJ`, `ORDR`, `RDR1`, `OSCL`, `ODLN`, `OWTR`, `OIGE`

**For Incoming:**

-   `OPDN`, `PDN1`, `OIGN`, `IGN1`, `ORPD`, `RPD1`, `OOCR`

**For Migi:**

-   `ODLN`, `DLN1`, `OIGE`, `IGE1`, `OOCR`

**For Powitheta:**

-   `OPOR`, `POR1`, `@MIS_CCDPT`, `OPRQ`

### 5.2 SQL Server User Setup Example

```sql
-- Create read-only user
CREATE LOGIN sap_readonly WITH PASSWORD = 'StrongPassword123!';

-- Use the SAP database
USE SBO_AAP_NEW;

-- Create user in database
CREATE USER sap_readonly FOR LOGIN sap_readonly;

-- Grant SELECT permissions on required tables
GRANT SELECT ON dbo.OPDN TO sap_readonly;
GRANT SELECT ON dbo.PDN1 TO sap_readonly;
GRANT SELECT ON dbo.OPOR TO sap_readonly;
-- ... repeat for all required tables
```

---

## 6. Performance Considerations

### 6.1 PHP Configuration

Update `php.ini` for large data syncs:

```ini
; Increase execution time for large syncs
max_execution_time = 600  ; 10 minutes

; Increase memory limit
memory_limit = 512M  ; Adjust based on data volume

; Increase post max size if needed
post_max_size = 100M
upload_max_filesize = 100M
```

### 6.2 Database Connection Pooling

Consider implementing connection pooling for high-volume scenarios:

-   Use persistent connections (if supported)
-   Monitor connection count
-   Set appropriate timeout values

### 6.3 Query Optimization

-   Large date ranges may return thousands of records
-   Consider implementing pagination or chunking for very large datasets
-   Monitor query execution time

---

## 7. Testing Requirements

### 7.1 Pre-Deployment Testing

1. **Test SQL Connection:**

    ```bash
    php artisan tinker
    ```

    ```php
    DB::connection('sap_sql')->select('SELECT TOP 1 * FROM OPDN');
    ```

2. **Test Sync Accuracy:**

    ```bash
    php artisan sap:verify-sync incoming --start=2025-01-01 --end=2025-11-24
    php artisan sap:verify-sync migi --start=2025-01-01 --end=2025-11-24
    php artisan sap:verify-sync powitheta --start=2024-12-01 --end=2025-11-24
    php artisan sap:verify-sync grpo --start=2025-01-01 --end=2025-11-24
    ```

3. **Test UI Functionality:**
    - Navigate to each module (GRPO, Incoming, Migi, Powitheta)
    - Test "Sync from SAP" button
    - Verify progress bar displays correctly
    - Verify data appears in tables after sync

### 7.2 Production Validation Checklist

-   [ ] PHP sqlsrv extension installed and enabled
-   [ ] Microsoft ODBC Driver installed
-   [ ] Network connectivity to SAP SQL Server verified
-   [ ] Environment variables configured in `.env`
-   [ ] SQL Server user has required permissions
-   [ ] Test connection successful
-   [ ] Test sync accuracy verified
-   [ ] UI sync functionality tested
-   [ ] Error handling tested
-   [ ] Logging configured (if needed)

---

## 8. Troubleshooting

### 8.1 Common Issues

**Issue: "Class 'PDO' not found" or "sqlsrv extension not loaded"**

-   **Windows Solution**:
    -   Verify DLL files are in `C:\xampp\php\ext\`
    -   Check `php.ini` has correct extension lines
    -   Ensure you're using Thread Safe (TS) versions for XAMPP
    -   Restart Apache from XAMPP Control Panel
-   **Linux Solution**: Install and enable sqlsrv/pdo_sqlsrv extension
-   **Verify**:
    -   Windows: `php -m | findstr sqlsrv`
    -   Linux: `php -m | grep sqlsrv`

**Issue: "SQLSTATE[HY000] [2002] Connection refused"**

-   **Solution**: Check network connectivity and firewall rules
-   **Windows Test**: `Test-NetConnection -ComputerName arkasrv2 -Port 1433`
-   **Linux Test**: `telnet arkasrv2 1433`

**Issue: "SQLSTATE[28000] [1045] Access denied"**

-   **Solution**: Verify SQL Server credentials in `.env`
-   **Check**: Username, password, and database name

**Issue: "SQLSTATE[HY000] [20002] TCP Provider: Error code 0x2AF9"**

-   **Windows Solution**:
    -   Install Microsoft ODBC Driver 17 or 18 for SQL Server
    -   Verify in ODBC Data Source Administrator (64-bit)
-   **Linux Solution**: Install Microsoft ODBC Driver for SQL Server
-   **Verify**:
    -   Windows: Open ODBC Data Source Administrator, check Drivers tab
    -   Linux: `odbcinst -q -d`

**Issue: "Unable to load dynamic library 'php_sqlsrv_XX_ts.dll'"**

-   **Solution**:
    -   Ensure DLL architecture matches PHP (x86 or x64)
    -   Check PHP version matches DLL version (e.g., 74 for PHP 7.4)
    -   Verify DLL file exists in `C:\xampp\php\ext\`
    -   Check file permissions (should be readable)

**Issue: "The specified DSN contains an architecture mismatch"**

-   **Solution**:
    -   Use 64-bit ODBC Driver if PHP is 64-bit
    -   Use 32-bit ODBC Driver if PHP is 32-bit
    -   Check PHP architecture: `php -i | findstr "Architecture"`

**Issue: Slow query performance**

-   **Solution**:
    -   Check network latency
    -   Optimize date range (use smaller ranges)
    -   Check SQL Server performance
    -   Consider indexing on date columns

### 8.2 Logging

Enable query logging for debugging:

```php
// In config/database.php
'sap_sql' => [
    // ... other config
    'logging' => env('SAP_SQL_LOGGING', false),
],
```

---

## 9. Security Checklist for Production

-   [ ] Use read-only SQL Server user
-   [ ] Enable encrypted connections (`Encrypt => true`)
-   [ ] Disable `TrustServerCertificate` in production
-   [ ] Store credentials securely (never in code)
-   [ ] Use strong passwords for SQL Server user
-   [ ] Restrict SQL Server user to specific database
-   [ ] Monitor SQL Server access logs
-   [ ] Regularly rotate SQL Server passwords
-   [ ] Use firewall rules to restrict access
-   [ ] Enable SSL/TLS for SQL Server connections

---

## 10. Deployment Steps

### For Windows Server 2016 with XAMPP:

1. **Install Microsoft ODBC Driver:**

    - Download ODBC Driver 17 or 18 from Microsoft website
    - Run the installer and complete installation
    - Verify in ODBC Data Source Administrator

2. **Install PHP SQL Server Extensions:**

    - Download Microsoft Drivers for PHP for SQL Server
    - Extract and copy DLL files to `C:\xampp\php\ext\`
    - Edit `C:\xampp\php\php.ini` and add:
        ```ini
        extension=php_sqlsrv_74_ts.dll
        extension=php_pdo_sqlsrv_74_ts.dll
        ```
    - Replace `74` with your PHP version
    - Restart Apache from XAMPP Control Panel

3. **Verify Extensions:**

    ```cmd
    cd C:\xampp\php
    php -m | findstr sqlsrv
    ```

4. **Configure Environment:**

    - Edit `.env` file in your project root
    - Add SAP SQL Server credentials:
        ```env
        SAP_SQL_HOST=arkasrv2
        SAP_SQL_PORT=1433
        SAP_SQL_DATABASE=SBO_AAP_NEW
        SAP_SQL_USERNAME=your_sql_username
        SAP_SQL_PASSWORD=your_sql_password
        ```

5. **Test Connection:**

    ```cmd
    cd C:\xampp\htdocs\your-project
    php artisan tinker
    ```

    Then in tinker:

    ```php
    DB::connection('sap_sql')->select('SELECT TOP 1 * FROM OPDN');
    ```

6. **Verify Sync Accuracy:**

    ```cmd
    php artisan sap:verify-sync all
    ```

7. **Deploy Code:**

    - Deploy updated code files to `C:\xampp\htdocs\your-project`
    - Run migrations if any: `php artisan migrate`
    - Clear cache: `php artisan config:clear`

8. **Test UI:**
    - Access your application in browser
    - Test sync functionality
    - Verify progress bars work
    - Verify data appears correctly

### For Linux Servers:

1. **Install PHP Extensions:**

    ```bash
    sudo apt-get install php-sqlsrv php-pdo-sqlsrv
    ```

2. **Install ODBC Driver:**

    ```bash
    sudo ACCEPT_EULA=Y apt-get install -y msodbcsql18
    ```

3. **Follow steps 3-8 above (same for both platforms)**

---

## 11. Monitoring Recommendations

1. **Monitor Sync Operations:**

    - Track sync duration
    - Monitor record counts
    - Log errors

2. **Monitor Database Connections:**

    - Track connection count
    - Monitor connection errors
    - Set up alerts for connection failures

3. **Monitor Performance:**
    - Track query execution time
    - Monitor memory usage
    - Monitor CPU usage during syncs

---

## 12. Support Information

### Related Files

-   `config/database.php` - Database connection configuration
-   `app/Services/SapService.php` - SQL query execution methods
-   `app/Http/Controllers/*Controller.php` - Sync controller methods
-   `database/*.sql` - Source SQL queries

### Documentation

-   `docs/SAP-SQL-DIRECT-ACCESS.md` - Implementation details
-   `docs/PRODUCTION-REQUIREMENTS-SAP-SQL.md` - This document

---

## Summary

**Minimum Requirements:**

1. PHP 7.3+ with sqlsrv/pdo_sqlsrv extension
2. Microsoft ODBC Driver for SQL Server
3. Network access to SAP SQL Server (port 1433)
4. SQL Server user with SELECT permissions
5. Environment variables configured

**Windows Server 2016 (XAMPP) Specific:**

-   Install ODBC Driver 17 or 18 for SQL Server (Windows x64)
-   Download and install Microsoft Drivers for PHP for SQL Server (Thread Safe version)
-   Copy DLL files to `C:\xampp\php\ext\`
-   Enable extensions in `C:\xampp\php\php.ini`
-   Restart Apache from XAMPP Control Panel

**Recommended:**

-   PHP 8.1+
-   Encrypted connections
-   Read-only SQL Server user
-   Monitoring and logging
-   Regular security audits

## Quick Reference for Windows Server 2016 + XAMPP

### Step-by-Step Installation:

1. **Check PHP Version:**

    ```cmd
    cd C:\xampp\php
    php -v
    ```

2. **Download Required Files:**

    - ODBC Driver: https://docs.microsoft.com/en-us/sql/connect/odbc/download-odbc-driver-for-sql-server
    - PHP Drivers: https://docs.microsoft.com/en-us/sql/connect/php/download-drivers-php-sql-server

3. **Install ODBC Driver:**

    - Run the downloaded `.msi` installer
    - Accept license agreement

4. **Install PHP Extensions:**

    - Extract PHP drivers ZIP
    - Copy DLLs from appropriate folder (x64 or x86, TS version) to `C:\xampp\php\ext\`
    - Edit `php.ini` and add extension lines
    - Restart Apache

5. **Configure:**

    - Add credentials to `.env` file
    - Test connection

6. **Verify:**
    ```cmd
    php -m | findstr sqlsrv
    php artisan tinker
    ```
