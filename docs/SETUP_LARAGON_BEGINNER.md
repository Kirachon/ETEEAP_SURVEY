# Laragon Setup Guide (Beginner-Friendly, No Docker)

This guide shows how to run the **ETEEAP Survey Application** on **Windows using Laragon** (no Docker).

Laragon is a simple local dev environment that bundles:
- Apache or Nginx
- PHP
- MySQL/MariaDB

---

## 1) Install Laragon

1) Download and install Laragon (Windows).
2) Open Laragon.
3) Click **Start All**.

You should see:
- Apache/Nginx running
- MySQL running

---

## 2) Put the Project in the Correct Folder

Laragon’s default web root is:
- `C:\laragon\www`

Copy or clone the project into:
- `C:\laragon\www\ETEEAP_SURVEY`

Important: This app must be served from the **public** folder:
- `C:\laragon\www\ETEEAP_SURVEY\public`

Do **not** serve from the project root or users could access `/src` directly.

---

## 3) Create a Local Site (Virtual Host) that Points to `/public`

### Option A (Recommended): Use Laragon “Quick app” / “www” + manual vhost edit

Laragon auto-creates a friendly URL like:
- `http://eteeap_survey.test`

But you must make sure its **DocumentRoot points to `/public`**.

1) In Laragon, click **Menu → Apache → sites-enabled**
2) Open the vhost file for your project (it will contain your site name).
3) Set the document root to:
   - `C:/laragon/www/ETEEAP_SURVEY/public`

Example vhost (Apache):

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

4) Restart Laragon (or restart Apache).

### Option B: Use `http://localhost/ETEEAP_SURVEY/public`

This works, but clean URLs like `/install` may fail if rewrite isn’t set correctly.
Virtual Host (Option A) is strongly recommended.

---

## 4) Run the Web Installer Wizard

Once the site is pointing to `/public`, open:
- `http://eteeap_survey.test/install`

The installer will:
- Write config to `storage/config.php`
- Apply `database/schema.sql`
- Run all migrations in `database/migrations/*.sql`
- Create/update the admin user (password you choose)
- Lock itself by creating `storage/install.lock`

### Fix: “Storage not writable”

If the installer says storage is not writable:
1) Right-click `C:\laragon\www\ETEEAP_SURVEY\storage`
2) Properties → Security → Edit
3) Allow **Modify** for your Windows user

Reload `/install`.

---

## 5) Database Notes (Laragon MySQL/MariaDB)

Laragon includes MySQL or MariaDB depending on your Laragon bundle.

### 5.1 Find your DB credentials

In Laragon:
- **Menu → MySQL → Console**

Most Laragon installs use:
- user: `root`
- password: *(blank)* (but not always)

### 5.2 Create DB + user (two choices)

**Choice A (simplest for beginners)**  
Let the installer use an existing database/user you create manually.

Create a database in phpMyAdmin (or MySQL console) named:
- `eteeap_survey`

Then in `/install`, set:
- DB Host: `127.0.0.1`
- DB Port: `3306`
- DB Name: `eteeap_survey`
- DB User: `root`
- DB Password: *(your Laragon root password, or blank if none)*

**Choice B (installer creates DB/user)**  
In `/install` enable:
- “Create database + user”

Then provide MySQL admin credentials (usually `root` + password).

---

## 6) Admin Login

After installation:
- Admin login: `http://eteeap_survey.test/admin/login`

The admin password is whatever you set during installation.

---

## 7) Troubleshooting

### 7.1 `/install` or `/survey/consent` returns 404
Cause: your site is not pointing to `public/`, or rewrite is not working.

Fix:
- Ensure DocumentRoot is `...\ETEEAP_SURVEY\public`
- In Apache vhost, `AllowOverride All`
- Restart Apache/Laragon

### 7.2 “Database connection failed”
Cause:
- Wrong DB host/port/user/password
- MySQL isn’t running

Fix:
- Confirm MySQL is started in Laragon
- Use DB Host `127.0.0.1` and Port `3306` (typical)

### 7.3 Installer locked
The installer disables itself after success.

To re-run:
- delete `storage/install.lock`
- open `/install` again

### 7.4 You can browse `/src` in the browser (security issue)
Cause: you served the project root instead of `public/`.

Fix:
- Set DocumentRoot to `...\ETEEAP_SURVEY\public`

---

## 8) Optional: Rebuild CSS (only if you changed styles)

From the project root:

```bash
npm ci
npm run build:css
```

This regenerates:
- `public/assets/app.css`

