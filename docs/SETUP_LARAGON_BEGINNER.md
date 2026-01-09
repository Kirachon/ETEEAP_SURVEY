# Laragon Setup Guide (Beginner-Friendly, No Docker)

This guide shows how to install and run the **ETEEAP Survey Application** on **Windows** using **Laragon** (no Docker). It is written for beginners and covers the full process from start to finish.

## What you will achieve
- You can open the survey at `http://eteeap_survey.test/`.
- You can run the installer at `http://eteeap_survey.test/install`.
- You can log in to admin at `http://eteeap_survey.test/admin/login`.

## Important concept (must read)
This app **must be served from the `public/` folder**.

- Correct: `C:\laragon\www\ETEEAP_SURVEY\public`
- Wrong (insecure / breaks routes): `C:\laragon\www\ETEEAP_SURVEY`

If you serve the project root, people may be able to browse `/src` and the installer/routes may not work correctly.

---

## 1) Install Laragon
1) Download Laragon (Windows) and install it.
2) Open Laragon.
3) Click **Start All**.

You should see both services running:
- Apache (or Nginx)
- MySQL (or MariaDB)

---

## 2) Put the project in Laragon’s web folder
Laragon’s default web root is:
- `C:\laragon\www`

Copy your project folder into:
- `C:\laragon\www\ETEEAP_SURVEY`

You should now have:
- `C:\laragon\www\ETEEAP_SURVEY\public`
- `C:\laragon\www\ETEEAP_SURVEY\src`
- `C:\laragon\www\ETEEAP_SURVEY\storage`

---

## 3) Create a local site that points to `/public` (Recommended)
This gives you clean URLs like `/install` and `/survey/consent`.

### 3A) Create the site in Laragon
1) In Laragon, click **Menu -> www**
2) Open the folder `ETEEAP_SURVEY` to confirm Laragon sees it.
3) In Laragon, click **Menu -> Tools -> Quick app** (or **Menu -> www -> your project** depending on your Laragon version).
4) Choose a site name (example): `eteeap_survey`

Laragon usually creates:
- `http://eteeap_survey.test`

### 3B) Ensure the DocumentRoot is `.../public`
You must configure the virtual host to point to `ETEEAP_SURVEY/public`.

#### If you are using Apache (most common)
1) Laragon **Menu -> Apache -> sites-enabled**
2) Open the vhost file for your site.
3) Set `DocumentRoot` to the `public` folder and allow `.htaccess`:

```apache
<VirtualHost *:80>
  ServerName eteeap_survey.test
  DocumentRoot "C:/laragon/www/ETEEAP_SURVEY/public"

  <Directory "C:/laragon/www/ETEEAP_SURVEY/public">
    AllowOverride All
    Require all granted
  </Directory>
</VirtualHost>
```

4) Restart Apache: **Laragon -> Stop -> Start** (or **Menu -> Apache -> Restart**).

#### If you are using Nginx
Nginx does not use `.htaccess`. You must use an Nginx config that rewrites all requests to `public/index.php`.

If you are on Nginx and routes like `/install` return 404, switch to Apache for the simplest setup:
- **Laragon -> Menu -> Preferences -> Services & Ports -> Web Server -> Apache**
- Restart Laragon

### If you do NOT want to create a virtual host
You can still run the app, but you must include `/public` in the URL:
- `http://localhost/ETEEAP_SURVEY/public/install`

---

## 4) Verify the site is pointing to `public/`
Open these URLs:
- `http://eteeap_survey.test/` (should redirect to the survey consent page)
- `http://eteeap_survey.test/survey/consent` (should show the consent page)
- `http://eteeap_survey.test/install` (should show the installer)

Security check:
- `http://eteeap_survey.test/src` should **NOT** be browseable. If it loads anything, your DocumentRoot is wrong.

---

## 5) Fix permissions for the installer (storage must be writable)
The installer needs to write files into:
- `C:\laragon\www\ETEEAP_SURVEY\storage`

If `/install` says **Storage not writable**:
1) Right-click `C:\laragon\www\ETEEAP_SURVEY\storage`
2) Properties → Security → Edit
3) Select your Windows user (or `Users`)
4) Allow **Modify**
5) Click Apply → OK
6) Reload `http://eteeap_survey.test/install`

Optional (advanced) command approach (run in PowerShell as your user):
```powershell
icacls "C:\laragon\www\ETEEAP_SURVEY\storage" /grant "$env:USERNAME:(OI)(CI)M" /T
```

Notes:
- If `$env:USERNAME` does not work, replace it with your actual Windows username (example: `"John"`), or grant the whole `Users` group:
  ```powershell
  icacls "C:\laragon\www\ETEEAP_SURVEY\storage" /grant "Users:(OI)(CI)M" /T
  ```
- `(OI)` = applies to files inside the folder
- `(CI)` = applies to subfolders
- `M` = Modify permission
- If this command fails, use the GUI method above.

---

## 6) Run the web installer wizard
Open:
- `http://eteeap_survey.test/install`

The installer will:
- Create `storage/config.php` (your local settings)
- Import `database/schema.sql`
- Run SQL migrations from `database/migrations/*.sql`
- Create/update an admin user (password you set)
- Create `storage/install.lock` to disable the installer after success

### Minimum fields you must fill correctly
Database:
- Host: `127.0.0.1`
- Port: `3306`
- Database: `eteeap_survey` (recommended)
- Username: `root` (or your MySQL user)
- Password: (blank or your root password — depends on your Laragon/MySQL setup)

Admin:
- Email: (your choice)
- Password: **at least 8 characters**

If you do not see the password field, scroll down in the installer page. If the “Install Now” button covers fields, your app folder may be outdated—download the latest release and replace your files (keep `storage/config.php` if you want to keep settings).

---

## 7) Create the database in MySQL (if needed)
If the installer says the database does not exist, create it first.

### Option A: Using Laragon MySQL Console (recommended)
1) Laragon -> **Menu -> MySQL -> Console**
2) Try:
   - `mysql -u root`
   - If that fails, try: `mysql -u root -p` (it will prompt for a password)
3) In the MySQL prompt, run:

```sql
CREATE DATABASE IF NOT EXISTS eteeap_survey CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Then reload `/install` and use `eteeap_survey` as the DB name.

If you get an “Access denied” error when creating the database, you are not logged in with a user that has permission to create databases. Use phpMyAdmin (Option B) or log in as a DB admin user.

### Option B: Using phpMyAdmin
1) Laragon -> **Menu -> Tools -> phpMyAdmin**
2) Create a database named `eteeap_survey` with collation `utf8mb4_unicode_ci`.

---

## 8) After install: where to log in / what to test
Admin login:
- `http://eteeap_survey.test/admin/login`

Survey:
- `http://eteeap_survey.test/` (consent page then steps)

Quick smoke test:
1) Complete the survey and submit.
2) Log in as admin and confirm the submission appears.
3) Try CSV export and charts (if available in the admin dashboard).

---

## 9) Troubleshooting (common beginner issues)

### 9.1 “Not Found” on `/install` or `/survey/consent`
Cause: DocumentRoot is wrong or rewrite is not enabled.

Fix checklist (Apache):
- DocumentRoot points to `...\ETEEAP_SURVEY\public`
- `<Directory ".../public"> AllowOverride All </Directory>` is present
- Apache restarted

### 9.2 You opened `.../src/views/install/` in the browser
That folder is only template files. The installer runs via the app’s router.

Use:
- `http://eteeap_survey.test/install`

### 9.3 “Access denied for user 'root'@'localhost' (using password: NO)”
This means your MySQL root user requires a password.

What to do:
1) In Laragon, open **Menu -> MySQL -> Console**
2) Try `mysql -u root -p` and enter the root password.
3) If you do not know the password:
   - Check if Laragon has a menu option like **Menu -> MySQL -> Reset root password** (some versions do).
   - Otherwise, use the database user you already know (if you have one), or consult the MySQL/MariaDB reset steps for your specific stack (Laragon vs XAMPP vs standalone).

If you are on XAMPP specifically, follow XAMPP/MariaDB root reset instructions (do not use `--skip-networking` on Windows if you still need to connect via TCP).

### 9.4 Installer locked
After success, the installer locks itself.

To re-run:
- Delete `C:\laragon\www\ETEEAP_SURVEY\storage\install.lock`
- Open `/install` again

### 9.5 Storage not writable
See section **5** and ensure `storage/` has Modify permissions.

---

## 10) Optional: access from another computer on the same network (LAN)
If other devices open your PC’s IP but get redirected to `localhost`, set the app URL to match your LAN address.

During installation, set the **App URL** field to:
- `http://<your-PC-LAN-IP>/`

If you already installed, you can update this in `storage/config.php` (look for `APP_URL`) and then refresh the page.

Also ensure Windows Firewall allows Apache.

---

## 11) Optional: build CSS (only if you changed styles)
Only needed for developers editing Tailwind styles.

From the project root:
```bash
npm ci
npm run build:css
```

This regenerates:
- `public/assets/app.css`
