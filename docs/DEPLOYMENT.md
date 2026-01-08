# Deployment Guide

> Production deployment instructions for the ETEEAP Survey Application

---

## 📋 Table of Contents

- [Pre-Deployment Checklist](#pre-deployment-checklist)
- [Production Environment Setup](#production-environment-setup)
- [Docker Deployment](#docker-deployment)
- [Traditional Server Deployment](#traditional-server-deployment)
- [SSL/HTTPS Configuration](#sslhttps-configuration)
- [Database Backup \& Recovery](#database-backup--recovery)
- [Monitoring \& Maintenance](#monitoring--maintenance)
- [Troubleshooting](#troubleshooting)

---

## ✅ Pre-Deployment Checklist

Before deploying to production, ensure the following:

### Security
- [ ] Change default admin password in `database/seed.sql`
- [ ] Update database credentials (strong passwords)
- [ ] Set `APP_ENV=production` in configuration
- [ ] Disable `display_errors` in PHP
- [ ] Enable HTTPS/SSL certificate
- [ ] Configure firewall rules
- [ ] Review CORS settings
- [ ] Implement rate limiting (optional)

### Performance
- [ ] Enable PHP OpCache
- [ ] Configure database connection pool
- [ ] Set up CDN for static assets (optional)
- [ ] Enable Gzip compression
- [ ] Optimize images and assets

### Monitoring
- [ ] Set up error logging
- [ ] Configure database backups
- [ ] Enable server monitoring
- [ ] Set up email alerts (optional)

---

## 🖥️ Production Environment Setup

### Recommended Specifications

**Minimum Requirements**:
- **CPU**: 2 cores
- **RAM**: 2 GB
- **Storage**: 20 GB SSD
- **Bandwidth**: 100 Mbps

**Recommended for 1000+ users**:
- **CPU**: 4 cores
- **RAM**: 4 GB
- **Storage**: 50 GB SSD
- **Database**: Separate server

### Operating System
- Ubuntu Server 22.04 LTS (recommended)
- Debian 11+
- CentOS 8+
- Amazon Linux 2

---

## 🐳 Docker Deployment

### Step 1: Prepare Server

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Docker and Docker Compose
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo apt install docker-compose -y

# Add user to docker group
sudo usermod -aG docker $USER
newgrp docker
```

### Step 2: Clone Repository

```bash
cd /opt
sudo git clone https://github.com/Kirachon/ETEEAP_SURVEY.git
cd ETEEAP_SURVEY
```

### Step 3: Configure Environment

Create `.env.production`:

```env
# Database Configuration
DB_HOST=db
DB_NAME=eteeap_survey
DB_USER=eteeap_user
DB_PASSWORD=CHANGE_THIS_STRONG_PASSWORD
DB_ROOT_PASSWORD=CHANGE_THIS_ROOT_PASSWORD

# Application Configuration
APP_NAME=ETEEAP Survey
APP_URL=https://survey.example.com
APP_ENV=production

# Session Configuration
SESSION_LIFETIME=3600
SESSION_SECURE=true
SESSION_HTTPONLY=true
```

### Step 4: Update docker-compose.yml

Create `docker-compose.prod.yml`:

```yaml
version: '3.8'

services:
  web:
    build: .
    container_name: eteeap_web
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./:/var/www/html
      - ./docker/php/php-prod.ini:/usr/local/etc/php/php.ini
      - ./ssl:/etc/ssl/certs
    environment:
      - APP_ENV=production
    depends_on:
      - db
    restart: unless-stopped
    networks:
      - eteeap_network

  db:
    image: mysql:8.0
    container_name: eteeap_db
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
      MYSQL_DATABASE: ${DB_NAME}
      MYSQL_USER: ${DB_USER}
      MYSQL_PASSWORD: ${DB_PASSWORD}
    volumes:
      - db_data:/var/lib/mysql
      - ./database/schema.sql:/docker-entrypoint-initdb.d/01-schema.sql
      - ./database/seed.sql:/docker-entrypoint-initdb.d/02-seed.sql
    restart: unless-stopped
    networks:
      - eteeap_network

volumes:
  db_data:
    driver: local

networks:
  eteeap_network:
    driver: bridge
```

### Step 5: Production PHP Configuration

Create `docker/php/php-prod.ini`:

```ini
; Production PHP Configuration
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log

; Performance
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2

; Security
expose_php = Off
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1

; File Uploads
upload_max_filesize = 10M
post_max_size = 12M
max_execution_time = 60
```

### Step 6: Deploy

```bash
# Load environment variables
export $(cat .env.production | xargs)

# Start services
docker-compose -f docker-compose.prod.yml up -d

# Check status
docker-compose -f docker-compose.prod.yml ps

# View logs
docker-compose -f docker-compose.prod.yml logs -f
```

### Step 7: Initialize Admin User

```bash
# Access database
docker-compose exec db mysql -u root -p${DB_ROOT_PASSWORD} eteeap_survey

# Update admin password
UPDATE admin_users SET password_hash = '$2y$10$NEW_HASH_HERE' WHERE email = 'admin@example.com';
```

---

## 🖥️ Traditional Server Deployment

### Step 1: Install LAMP Stack

**Ubuntu 22.04**:
```bash
sudo apt update
sudo apt install -y apache2 php8.1 php8.1-{mysql,mbstring,gd,curl,zip,xml}
sudo apt install -y mysql-server

# Enable Apache modules
sudo a2enmod rewrite ssl headers
sudo systemctl restart apache2
```

### Step 2: Secure MySQL

```bash
sudo mysql_secure_installation

# Follow prompts:
# - Set root password
# - Remove anonymous users
# - Disallow root login remotely
# - Remove test database
```

### Step 3: Create Database and User

```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE eteeap_survey CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'eteeap_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON eteeap_survey.* TO 'eteeap_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 4: Deploy Application

```bash
# Clone to web directory
cd /var/www
sudo git clone https://github.com/Kirachon/ETEEAP_SURVEY.git eteeap
cd eteeap

# Set permissions
sudo chown -R www-data:www-data .
sudo chmod -R 755 .

# Import database
mysql -u eteeap_user -p eteeap_survey < database/schema.sql
mysql -u eteeap_user -p eteeap_survey < database/seed.sql
```

### Step 5: Configure Apache VirtualHost

Create `/etc/apache2/sites-available/eteeap.conf`:

```apache
<VirtualHost *:80>
    ServerName survey.example.com
    ServerAdmin admin@example.com
    
    DocumentRoot /var/www/eteeap/public
    
    <Directory /var/www/eteeap/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        
        # Security headers
        Header always set X-Content-Type-Options "nosniff"
        Header always set X-Frame-Options "SAMEORIGIN"
        Header always set X-XSS-Protection "1; mode=block"
    </Directory>
    
    # Logging
    ErrorLog ${APACHE_LOG_DIR}/eteeap_error.log
    CustomLog ${APACHE_LOG_DIR}/eteeap_access.log combined
    
    # Compression
    <IfModule mod_deflate.c>
        AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
    </IfModule>
</VirtualHost>
```

Enable site:
```bash
sudo a2ensite eteeap
sudo systemctl reload apache2
```

### Step 6: Configure PHP Production Settings

Edit `/etc/php/8.1/apache2/php.ini`:

```ini
display_errors = Off
log_errors = On
error_log = /var/log/php/error.log

opcache.enable=1
opcache.memory_consumption=128
opcache.revalidate_freq=2

session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1

upload_max_filesize = 10M
post_max_size = 12M
```

Restart Apache:
```bash
sudo systemctl restart apache2
```

---

## 🔒 SSL/HTTPS Configuration

### Using Let's Encrypt (Recommended)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache -y

# Obtain and install certificate
sudo certbot --apache -d survey.example.com

# Test auto-renewal
sudo certbot renew --dry-run

# Auto-renewal is configured via cron
```

### Manual SSL Certificate

If you have purchased an SSL certificate:

```bash
# Copy certificate files
sudo cp your_domain.crt /etc/ssl/certs/
sudo cp your_domain.key /etc/ssl/private/
sudo cp ca_bundle.crt /etc/ssl/certs/

# Update Apache config
sudo nano /etc/apache2/sites-available/eteeap-ssl.conf
```

```apache
<VirtualHost *:443>
    ServerName survey.example.com
    
    DocumentRoot /var/www/eteeap/public
    
    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/your_domain.crt
    SSLCertificateKeyFile /etc/ssl/private/your_domain.key
    SSLCertificateChainFile /etc/ssl/certs/ca_bundle.crt
    
    # ... rest of config same as HTTP
</VirtualHost>
```

Enable SSL site:
```bash
sudo a2ensite eteeap-ssl
sudo systemctl reload apache2
```

---

## 💾 Database Backup & Recovery

### Automated Daily Backups

Create backup script `/opt/scripts/backup-eteeap.sh`:

```bash
#!/bin/bash

# Configuration
BACKUP_DIR="/backup/eteeap"
DB_NAME="eteeap_survey"
DB_USER="eteeap_user"
DB_PASS="YOUR_PASSWORD"
RETENTION_DAYS=30

# Create backup directory
mkdir -p $BACKUP_DIR

# Generate filename with date
DATE=$(date +%Y%m%d_%H%M%S)
FILENAME="eteeap_${DATE}.sql"

# Perform backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > ${BACKUP_DIR}/${FILENAME}

# Compress backup
gzip ${BACKUP_DIR}/${FILENAME}

# Delete old backups
find $BACKUP_DIR -name "eteeap_*.sql.gz" -mtime +$RETENTION_DAYS -delete

echo "Backup completed: ${FILENAME}.gz"
```

Make executable and add to cron:
```bash
chmod +x /opt/scripts/backup-eteeap.sh

# Add to crontab (daily at 2 AM)
crontab -e
```

Add line:
```
0 2 * * * /opt/scripts/backup-eteeap.sh >> /var/log/eteeap-backup.log 2>&1
```

### Manual Backup

```bash
# Create backup
mysqldump -u eteeap_user -p eteeap_survey > eteeap_backup_$(date +%Y%m%d).sql

# Compress
gzip eteeap_backup_*.sql
```

### Recovery from Backup

```bash
# Decompress if needed
gunzip eteeap_backup_20260108.sql.gz

# Restore database
mysql -u eteeap_user -p eteeap_survey < eteeap_backup_20260108.sql
```

---

## 📊 Monitoring & Maintenance

### Log Monitoring

**Apache Logs**:
```bash
# Error log
sudo tail -f /var/log/apache2/eteeap_error.log

# Access log
sudo tail -f /var/log/apache2/eteeap_access.log
```

**PHP Logs**:
```bash
# PHP error log
sudo tail -f /var/log/php/error.log
```

**MySQL Logs**:
```bash
# MySQL error log
sudo tail -f /var/log/mysql/error.log
```

### Database Optimization

Run monthly optimization:
```bash
# Optimize all tables
mysqlcheck -u eteeap_user -p --optimize eteeap_survey

# Analyze tables
mysqlcheck -u eteeap_user -p --analyze eteeap_survey
```

### Disk Space Monitoring

```bash
# Check disk usage
df -h

# Monitor log sizes
du -sh /var/log/*

# Clean old logs
sudo find /var/log -name "*.log" -mtime +30 -delete
```

### Server Health Checks

Create monitoring script `/opt/scripts/health-check.sh`:

```bash
#!/bin/bash

# Check Apache
if ! systemctl is-active --quiet apache2; then
    echo "Apache is down!" | mail -s "ETEEAP Alert" admin@example.com
fi

# Check MySQL
if ! systemctl is-active --quiet mysql; then
    echo "MySQL is down!" | mail -s "ETEEAP Alert" admin@example.com
fi

# Check disk space
DISK_USAGE=$(df -h / | awk 'NR==2 {print $5}' | sed 's/%//')
if [ $DISK_USAGE -gt 80 ]; then
    echo "Disk usage is at ${DISK_USAGE}%!" | mail -s "ETEEAP Alert" admin@example.com
fi
```

Run every 5 minutes:
```bash
*/5 * * * * /opt/scripts/health-check.sh
```

---

## 🔧 Troubleshooting

### Application Won't Start

**Check Apache**:
```bash
sudo systemctl status apache2
sudo apache2ctl configtest
```

**Check MySQL**:
```bash
sudo systemctl status mysql
```

**Check Permissions**:
```bash
sudo chown -R www-data:www-data /var/www/eteeap
sudo chmod -R 755 /var/www/eteeap
```

### Database Connection Errors

1. Verify credentials in `src/config/database.php`
2. Check MySQL is running: `sudo systemctl status mysql`
3. Test connection:
   ```bash
   mysql -u eteeap_user -p -h localhost eteeap_survey
   ```

### 500 Internal Server Error

1. Check error logs: `sudo tail -f /var/log/apache2/eteeap_error.log`
2. Verify `.htaccess` exists in `public/` folder
3. Check `mod_rewrite` is enabled: `sudo a2enmod rewrite`
4. Check PHP errors: `sudo tail -f /var/log/php/error.log`

### Slow Performance

1. **Enable OpCache**: Check `/etc/php/8.1/apache2/php.ini`
2. **Optimize Database**:
   ```bash
   mysqlcheck -u eteeap_user -p --optimize eteeap_survey
   ```
3. **Check slow queries**:
   ```sql
   SHOW FULL PROCESSLIST;
   ```
4. **Monitor server resources**:
   ```bash
   htop
   ```

---

## 🔄 Updating the Application

### Pull Latest Changes

```bash
cd /var/www/eteeap
sudo git pull origin master

# Run migrations if any
mysql -u eteeap_user -p eteeap_survey < database/migrations/YYYYMMDD_migration.sql

# Clear cache (if implemented)
sudo rm -rf cache/*

# Restart Apache
sudo systemctl restart apache2
```

### Zero-Downtime Updates

For critical systems:

1. **Set up staging server** for testing
2. **Test updates** on staging first
3. **Schedule maintenance window**
4. **Create backup** before updating
5. **Monitor logs** after deployment

---

## 📞 Support

For deployment assistance:
- Check logs first
- Review this documentation
- Contact system administrator

---

**Last Updated**: January 2026  
**Version**: 1.0.0
