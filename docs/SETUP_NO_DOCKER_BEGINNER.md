# Non-Docker Setup Guide (Beginner-Friendly)

This guide helps you run the **ETEEAP Survey Application** on your computer **without Docker**.

You will install:
- PHP (backend)
- Apache (web server)
- MySQL (database)

Optional:
- Node.js (only if you want to rebuild the CSS)

---

## 0) Quick Checklist

Before you start, you should know:
- Are you installing on **Windows** or **Linux**?
- What URL will you use?
  - Local: `http://eteeap.local`
  - LAN (other devices): `http://192.168.x.x:8000` (only if you configure it)

If you are a beginner on Windows, the easiest option is:
- **XAMPP** (includes Apache + PHP + MySQL)

---

## 1) Requirements

### Required Software
- **PHP 8.1+**
- **MySQL 8.0+** (MariaDB 10.6+ usually works)
- **Apache 2.4+** with `mod_rewrite` enabled

### Required PHP Extensions
At minimum:
- `pdo_mysql`
- `mbstring`

Commonly needed (often enabled by default):
- `openssl`
- `curl`
- `json`
- `fileinfo`

### Optional (Frontend build only)
Only needed if you plan to rebuild Tailwind CSS:
- Node.js (LTS) + npm

---

## 2) Get the Project

1) Clone the repository:

```bash
git clone https://github.com/Kirachon/ETEEAP_SURVEY.git
cd ETEEAP_SURVEY
```

If you already downloaded a ZIP, extract it and open a terminal in the extracted folder.

---

## 3) Database Setup (MySQL)

You will:
1) Create the database
2) Create a DB user
3) Import schema + seed
4) Run migrations

### 3.1 Create the database + user

Open MySQL (phpMyAdmin / MySQL Workbench / or CLI) and run:

```sql
CREATE DATABASE eteeap_survey
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE USER 'eteeap_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON eteeap_survey.* TO 'eteeap_user'@'localhost';
FLUSH PRIVILEGES;
```

### 3.2 Import schema and seed data

From the project root:

```bash
mysql -u root -p eteeap_survey < database/schema.sql
mysql -u root -p eteeap_survey < database/seed.sql
```

### 3.3 Apply migrations (IMPORTANT)

Migrations contain changes that must also exist in your DB.
Run these in date order:

```bash
mysql -u root -p eteeap_survey < database/migrations/2026-01-08_nationwide_interest_survey.sql
mysql -u root -p eteeap_survey < database/migrations/2026-01-09_unique_identity_name_email.sql
```

If you skip migrations, you may see errors like:
- “There was a problem submitting your survey…”
- “Unknown column …”
- “Data truncated …”

---

## 4) Configure the Application

### 4.1 Configure database connection

Edit:
- `src/config/database.php`

Set:
- `DB_HOST`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`

Example:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'eteeap_survey');
define('DB_USER', 'eteeap_user');
define('DB_PASS', 'strong_password_here');
```

### 4.2 Configure app URL + environment

Edit:
- `src/config/app.php`

Recommended for local:

```php
define('APP_ENV', 'development');
define('APP_DEBUG', true);
define('APP_URL', 'http://eteeap.local');
```

Recommended for production:

```php
define('APP_ENV', 'production');
define('APP_DEBUG', false);
define('APP_URL', 'https://your-domain.example');
```

Note: The app can also derive the base URL from the current request host, but setting `APP_URL` explicitly is still recommended for stable production URLs.

---

## 5) Web Server Setup (Apache)

The app’s public entry point is:
- `public/index.php`

Your Apache DocumentRoot should be the **public** folder:
- `<project>/public`

### 5.1 Windows (XAMPP) - Recommended for beginners

#### Step A: Install XAMPP
1) Install XAMPP.
2) Start **Apache** and **MySQL** from the XAMPP Control Panel.

#### Step B: Place the project in htdocs
Copy the project folder to:
- `C:\xampp\htdocs\ETEEAP_Survey`

The public folder will be:
- `C:\xampp\htdocs\ETEEAP_Survey\public`

#### Step C: Enable rewrite module
Open:
- `C:\xampp\apache\conf\httpd.conf`

Make sure these lines are enabled (not commented):
- `LoadModule rewrite_module modules/mod_rewrite.so`
- `Include conf/extra/httpd-vhosts.conf`

Restart Apache.

#### Step D: Create a Virtual Host (recommended)
Open:
- `C:\xampp\apache\conf\extra\httpd-vhosts.conf`

Add:

```apache
<VirtualHost *:80>
    ServerName eteeap.local
    DocumentRoot "C:/xampp/htdocs/ETEEAP_Survey/public"

    <Directory "C:/xampp/htdocs/ETEEAP_Survey/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Step E: Add hosts entry
Edit as Administrator:
- `C:\Windows\System32\drivers\etc\hosts`

Add:

```
127.0.0.1 eteeap.local
```

Restart Apache.

Now open:
- `http://eteeap.local/`

### 5.2 Linux (Ubuntu/Debian) - Apache

#### Step A: Install Apache + PHP + extensions

```bash
sudo apt update
sudo apt install -y apache2 mysql-server php php-mysql php-mbstring
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### Step B: Create an Apache site config
Example:

```bash
sudo nano /etc/apache2/sites-available/eteeap.conf
```

Paste:

```apache
<VirtualHost *:80>
    ServerName eteeap.local
    DocumentRoot /var/www/eteeap/public

    <Directory /var/www/eteeap/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/eteeap_error.log
    CustomLog ${APACHE_LOG_DIR}/eteeap_access.log combined
</VirtualHost>
```

Enable site:

```bash
sudo a2ensite eteeap
sudo systemctl reload apache2
```

#### Step C: Place project files

```bash
sudo mkdir -p /var/www/eteeap
sudo cp -R ./ /var/www/eteeap
sudo chown -R www-data:www-data /var/www/eteeap
```

---

## 6) Build CSS (Optional)

The repository includes `public/assets/app.css` already.

Only run this if you changed Tailwind styles:

```bash
npm ci
npm run build:css
```

---

## 7) Run and Access the App

### Survey
- `http://eteeap.local/`

### Admin
- `http://eteeap.local/admin/login`

Default admin credentials are stored/seeded via:
- `database/seed.sql`

---

## 8) Change the Admin Password (Beginner Safe)

### Step A: Generate a password hash
On any machine that has PHP installed:

```bash
php -r "echo password_hash('NewStrongPassword!', PASSWORD_DEFAULT), PHP_EOL;"
```

Copy the output (it starts with `$2y$...`).

### Step B: Update the database
Run:

```sql
UPDATE admin_users
SET password_hash = 'PASTE_HASH_HERE'
WHERE username = 'admin';
```

---

## 9) Common Problems and Fixes

### 9.1 Links/Pages show 404 when navigating
Causes:
- Apache rewrite is not enabled
- DocumentRoot is not set to the `public/` folder
- `AllowOverride All` missing

Fix:
- Enable `mod_rewrite`
- Ensure the virtual host points to `<project>/public`

### 9.2 “There was a problem submitting your survey…”
Almost always:
Your database schema is behind the code.

Fix:
- Re-run schema + migrations:
  - `database/schema.sql`
  - `database/migrations/*.sql` (in order)

### 9.3 Redirects go to localhost when accessed via LAN IP
Fix:
- Set `APP_URL` in `src/config/app.php` to your real LAN URL, e.g.:
  - `http://192.168.1.50:8000`

---

## 10) Production Checklist (Recommended)

- Set `APP_ENV=production` and `APP_DEBUG=false` in `src/config/app.php`
- Change admin password (Section 8)
- Use HTTPS (Let’s Encrypt)
- Apply migrations before deploying new code
- Restrict database user permissions (avoid broad grants in production)
- Set up database backups

