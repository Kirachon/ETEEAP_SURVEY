# ETEEAP Survey Application

> **Nationwide Interest Survey on the ETEEAP–BS Social Work Program for DSWD Personnel**

A premium, mobile-first web application for collecting survey responses from DSWD (Department of Social Welfare and Development) personnel regarding their interest in the ETEEAP (Expanded Tertiary Education Equivalency and Accreditation Program) Bachelor of Science in Social Work program.

---

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [System Requirements](#system-requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [API Documentation](#api-documentation)
- [Database Schema](#database-schema)
- [Security](#security)
- [Deployment](#deployment)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)
- [License](#license)

---

## 🎯 Overview

The ETEEAP Survey Application is designed to assess the interest and eligibility of DSWD personnel for the BS Social Work program through ETEEAP. The application features:

- **8-step multi-step form** with progress tracking
- **Premium bento-grid dashboard** with real-time analytics
- **Mobile-first responsive design** (works on all devices)
- **CSRF protection** and secure data handling
- **Export functionality** for data analysis
- **Chart.js visualizations** for insights

---

## ✨ Features

### Survey Flow
- ✅ Data privacy consent (RA 10173 compliant)
- ✅ Progressive multi-step form (8 sections)
- ✅ Real-time validation
- ✅ Mobile-optimized UI with premium aesthetics
- ✅ Session persistence for resume-later functionality
- ✅ Thank you page with completion confirmation

### Admin Dashboard
- 📊 **Executive-Grade Analytics**: Premium, board-ready visualization with Chart.js
- 🎨 **Adaptive Grid Layout**: Smart layout engine that switches between 5/7 split and full-width grids based on content type
- 📈 **Interactive Charts**: Gradient-infused, animated visualizations for national interest and demographics
- 🔍 **Advanced Filtering**: Deep dive capability into specific regions, demographics, and interest levels
- 📥 **Export to CSV**: Enterprise-grade data export for offline analysis
- 👁️ **Detail View**: High-fidelity individual response inspection
- 📱 **Responsive & Mobile-First**: Fully functional administration from any device


### Security Features
- 🔒 CSRF token protection on all forms
- 🔐 Password hashing (bcrypt)
- 🛡️ SQL injection protection (prepared statements)
- 🚫 Input sanitization and validation
- 📋 Session management with secure cookies

---

## 🛠️ Technology Stack

| Layer | Technology | Version |
|-------|------------|---------|
| **Backend** | PHP | 8.1+ |
| **Database** | MySQL | 8.0+ |
| **Styling** | Tailwind CSS | 3.x (compiled to `public/assets/app.css`) |
| **Charts** | Chart.js | 4.x (vendored in `public/assets/vendor/`) |
| **Server** | Apache | 2.4+ |
| **Containerization** | Docker | 20.10+ |

---

## 📦 System Requirements

### Minimum Requirements
- PHP 8.1 or higher
- MySQL 8.0 or higher
- Apache 2.4 with `mod_rewrite` enabled
- 512 MB RAM
- 100 MB disk space

### PHP Extensions Required
- `pdo_mysql` - Database connectivity
- `mbstring` - Multi-byte string handling
- `exif` - Image metadata
- `pcntl` - Process control
- `bcmath` - Arbitrary precision math
- `gd` - Image processing

---

## 🚀 Installation

### Option 1: Docker (Recommended)

1. **Clone the repository**
   ```bash
   git clone https://github.com/Kirachon/ETEEAP_SURVEY.git
   cd ETEEAP_SURVEY
   ```

2. **Start containers**
   ```bash
   docker compose up -d
   docker compose ps
   ```

   Services and ports (defaults from `docker-compose.yml`):
   - App (PHP + Apache): `http://localhost:8000`
   - MySQL: `localhost:3307` (host port → container 3306)
   - phpMyAdmin: `http://localhost:8080`

3. **Access the application**
   - Survey: `http://localhost:8000/`
   - Admin: `http://localhost:8000/admin/login`

   Default admin credentials are in `database/seed.sql`.

### Option 2: Manual Installation

1. **Install prerequisites**
   ```bash
   # Ubuntu/Debian
   sudo apt update
   sudo apt install apache2 php8.1 php8.1-mysql php8.1-mbstring php8.1-gd mysql-server
   
   # Enable Apache modules
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

2. **Clone and configure**
   ```bash
   cd /var/www/html
   git clone https://github.com/Kirachon/ETEEAP_SURVEY.git eteeap
   cd eteeap
   chmod -R 755 .
   chown -R www-data:www-data .
   ```

3. **Configure Apache**
   Create `/etc/apache2/sites-available/eteeap.conf`:
   ```apache
   <VirtualHost *:80>
       ServerName survey.example.com
       DocumentRoot /var/www/html/eteeap/public
       
       <Directory /var/www/html/eteeap/public>
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

4. **Set up database**
   ```bash
   mysql -u root -p
   ```
   ```sql
   CREATE DATABASE eteeap_survey CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'eteeap_user'@'localhost' IDENTIFIED BY 'strong_password_here';
   GRANT ALL PRIVILEGES ON eteeap_survey.* TO 'eteeap_user'@'localhost';
   FLUSH PRIVILEGES;
   EXIT;
   ```
   
   ```bash
   mysql -u root -p eteeap_survey < database/schema.sql
   mysql -u root -p eteeap_survey < database/seed.sql
   ```

5. **Configure application**
   Edit `src/config/database.php` with your credentials.

---

## ⚙️ Configuration

### Database Configuration
Edit `src/config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'eteeap_survey');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_CHARSET', 'utf8mb4');
```

### Application Settings
Edit `src/config/app.php`:

```php
define('APP_NAME', 'ETEEAP Survey');
define('APP_URL', 'http://localhost:8080');
define('APP_ENV', 'production'); // development | production
define('SESSION_LIFETIME', 3600); // 1 hour in seconds
```

---

## 💡 Usage

### For Survey Respondents

1. Navigate to the survey URL (e.g., `http://survey.example.com`)
2. Read and accept the data privacy consent
3. Complete all 8 sections of the survey:
   - Consent
   - Basic Information
   - Office & Employment Data
   - Work Experience
   - Social Work Competencies
   - Educational Background
   - DSWD Academy Courses
   - ETEEAP Interest
4. Submit and receive confirmation

### For Administrators

1. Navigate to `http://survey.example.com/admin/login`
2. Log in with admin credentials
3. **Dashboard**: View real-time metrics and charts
4. **Responses**: Browse all survey submissions
5. **Export**: Download CSV for analysis
6. **Detail View**: Click any response to see full details

---

## 📖 API Documentation

See [API.md](docs/API.md) for complete API documentation.

### Quick Reference

**Dashboard Statistics**
```http
GET /api/stats/summary
GET /api/stats/demographics
GET /api/stats/interest
GET /api/stats/timeline
```

**Public (no auth)**
```http
GET /api/positions
GET /api/courses
POST /api/survey/submit
```

---

## 🗄️ Database Schema

The application uses MySQL with the following main tables:

- `survey_responses` - Main survey data
- `response_program_assignments` - Program checkboxes
- `response_sw_tasks` - Social work tasks
- `response_expertise_areas` - Areas of expertise
- `response_dswd_courses` - DSWD courses taken
- `response_motivations` - ETEEAP motivations
- `response_barriers` - Barriers to ETEEAP
- `admin_users` - Admin authentication

See `database/schema.sql` for complete schema.

---

## 🔐 Security

### Best Practices Implemented

✅ **CSRF Protection**: Tokens on all POST requests  
✅ **SQL Injection Prevention**: Prepared statements (PDO)  
✅ **XSS Protection**: Output escaping with `htmlspecialchars()`  
✅ **Password Security**: bcrypt hashing  
✅ **Session Security**: `session_regenerate_id()` after login  
✅ **Input Validation**: Server-side validation on all inputs  

### Security Checklist for Production

- [ ] Change default admin password
- [ ] Use HTTPS (Let's Encrypt)
- [ ] Set `APP_ENV=production` in config
- [ ] Enable `display_errors=Off` in `php.ini`
- [ ] Restrict database user permissions
- [ ] Regular security updates for PHP/MySQL
- [ ] Implement rate limiting for login attempts

---

## 🌐 Deployment

Beginner manuals:
- `docs/README.md`

### Docker Deployment (Production)

1. **Update docker-compose.yml** for production settings
2. **Set environment variables**
   ```bash
   export DB_ROOT_PASSWORD="secure_password"
   export APP_ENV="production"
   ```
3. **Deploy**
   ```bash
   docker-compose -f docker-compose.prod.yml up -d
   ```

### Traditional Server Deployment

1. **Configure firewall**
   ```bash
   sudo ufw allow 80/tcp
   sudo ufw allow 443/tcp
   sudo ufw enable
   ```

2. **Set up SSL with Let's Encrypt**
   ```bash
   sudo apt install certbot python3-certbot-apache
   sudo certbot --apache -d survey.example.com
   ```

3. **Configure backups**
   ```bash
   # Daily database backup
   0 2 * * * mysqldump -u root -p eteeap_survey > /backup/eteeap_$(date +\%Y\%m\%d).sql
   ```

---

## 🐛 Troubleshooting

### Common Issues

**Problem**: "Fatal error: Uncaught PDOException"  
**Solution**: Check database credentials in `src/config/database.php`

**Problem**: Charts not displaying  
**Solution**: Ensure Chart.js CDN is accessible. Check browser console for errors.

**Problem**: 404 errors on navigation  
**Solution**: Enable Apache `mod_rewrite`:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

**Problem**: Session data not persisting  
**Solution**: Check PHP session directory permissions:
```bash
sudo chmod 1733 /var/lib/php/sessions
```

---

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## 📄 License

This project is proprietary software developed for the Department of Social Welfare and Development (DSWD).

---

## 📞 Contact

For support or inquiries:
- **Project Repository**: https://github.com/Kirachon/ETEEAP_SURVEY
- **Documentation**: See `/docs` folder

---

## 🙏 Acknowledgments

- DSWD for project sponsorship
- Tailwind CSS for premium styling framework
- Chart.js for visualization library
- Docker community for containerization support

---

**Last Updated**: January 2026  
**Version**: 1.0.5
