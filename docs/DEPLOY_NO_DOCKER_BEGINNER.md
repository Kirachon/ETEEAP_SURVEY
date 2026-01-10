# Non-Docker Deployment Guide (Beginner-Friendly, Linux)

This guide deploys the ETEEAP Survey on a Linux server without Docker.

## 1) Requirements

- Ubuntu 22.04+ (recommended) or similar
- PHP 8.1+ with extensions: `pdo_mysql`, `mbstring`, `bcmath`, `exif`, `gd`, `curl`, `openssl`
- MySQL 8.0+ (MariaDB 10.6+ usually works)
- Apache 2.4+ (recommended for beginners) or Nginx

## 2) Install system packages (Apache + PHP + MySQL)

```bash
sudo apt update
sudo apt install -y apache2 mysql-server \
  php php-mysql php-mbstring php-bcmath php-exif php-gd php-curl php-xml unzip
sudo a2enmod rewrite
sudo systemctl restart apache2
```

## 3) Deploy the project

```bash
sudo mkdir -p /var/www/eteeap
sudo chown -R $USER:$USER /var/www/eteeap
cd /var/www/eteeap
git clone https://github.com/Kirachon/ETEEAP_SURVEY.git .
```

Important: serve from `public/`.

Create an Apache site:

```bash
sudo nano /etc/apache2/sites-available/eteeap.conf
```

```apache
<VirtualHost *:80>
    ServerName survey.example.com
    DocumentRoot /var/www/eteeap/public

    <Directory /var/www/eteeap/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/eteeap_error.log
    CustomLog ${APACHE_LOG_DIR}/eteeap_access.log combined
</VirtualHost>
```

```bash
sudo a2ensite eteeap
sudo systemctl reload apache2
```

## 3b) Permissions (storage must be writable)

The installer and runtime configuration use:
- `storage/`

Make it writable by the web server:

```bash
sudo chown -R www-data:www-data /var/www/eteeap/storage
sudo chmod -R 775 /var/www/eteeap/storage
```

## 4) Create the database and user

```bash
sudo mysql
```

```sql
CREATE DATABASE eteeap_survey CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'eteeap_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON eteeap_survey.* TO 'eteeap_user'@'localhost';
FLUSH PRIVILEGES;
```

## 5) Run the web installer (recommended)

Open:
- `http://survey.example.com/install`

If the installer is locked, remove:
- `storage/install.lock`

If you are in production and `/install` returns 404, temporarily enable it:
- set `INSTALLER_ALLOW=true` in your environment for the install step only

After installation:
- remove/unset `INSTALLER_ALLOW`
- confirm `storage/install.lock` exists

## 6) Verify

- Complete a survey submission
- Log in to `/admin/login`
- Confirm response list and CSV export

## 7) Enable HTTPS (recommended)

Example using Certbot + Apache:

```bash
sudo apt install -y certbot python3-certbot-apache
sudo certbot --apache -d survey.example.com
```

## 8) Common troubleshooting

### 8.1 404 for `/survey/consent` or `/install`

Usually means DocumentRoot is wrong or rewrite is disabled.
Confirm your vhost uses:
- `DocumentRoot /var/www/eteeap/public`
- `<Directory /var/www/eteeap/public> AllowOverride All </Directory>`

### 8.2 Storage not writable

Re-apply the permissions in section **3b**.

### 8.3 Database schema out-of-date

Apply migrations in `database/migrations/` in order.
