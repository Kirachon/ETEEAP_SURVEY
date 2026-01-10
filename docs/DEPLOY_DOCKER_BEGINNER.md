# Docker Deployment Guide (Beginner-Friendly)

This guide gets the ETEEAP Survey running end-to-end using Docker Compose.

## What you will have at the end

- Survey: `http://localhost:8000/`
- Admin login: `http://localhost:8000/admin/login`
- phpMyAdmin: `http://localhost:8080/`

## 1) Prerequisites

- Docker Desktop (Windows/macOS) or Docker Engine (Linux)
- Git

## 2) Clone the project

```bash
git clone https://github.com/Kirachon/ETEEAP_SURVEY.git
cd ETEEAP_SURVEY
```

## 3) Start containers

```bash
docker compose up -d
docker compose ps
```

## 4) Verify it’s working

Open:
- `http://localhost:8000/` (should redirect to consent)
- `http://localhost:8000/admin/login`
- `http://localhost:8080/` (phpMyAdmin)

## 4b) Database initialization notes (important)

The `db` container auto-initializes the database only on the **first** run of the MySQL volume.

- First start (empty volume): MySQL runs the init scripts mounted in `docker-compose.yml`:
  - `database/schema.sql`
  - `database/seed.sql`
- Later restarts (existing volume): those init scripts do **not** run again.

For upgrades, apply new migrations manually:

```bash
# Example: apply all migrations in order (repeat for each new migration file)
docker compose exec -T db mysql -u root -p<root_password> eteeap_survey < database/migrations/2026-01-09_update_q27_will_apply.sql
```

Use the credentials from `docker-compose.yml` (`MYSQL_ROOT_PASSWORD`, `MYSQL_USER`, `MYSQL_PASSWORD`).

## 5) Default credentials (dev only)

For local/dev, a default admin user is seeded in:
- `database/seed.sql`

For production, create your own admin via the installer (recommended) or update directly in the database.

## 5b) Optional: use the web installer

You can also use the web installer at:
- `http://localhost:8000/install`

It can create/update an admin user and writes `storage/config.php`.

Note: after a successful install, the installer locks itself by creating `storage/install.lock`.

## 6) Rebuild CSS (optional)

Only needed if you changed Tailwind styles:

```bash
npm ci
npm run build:css
```

## 7) Useful commands

```bash
# Logs
docker compose logs -f app
docker compose logs -f db

# Enter the PHP container
docker compose exec app bash

# PHP syntax check
docker compose exec -T app php -l public/index.php
```

## 8) Troubleshooting

### 8.1 “Port is already allocated”

Another service is using a port (8000/8080/3307).
Edit the host-side port mappings in `docker-compose.yml` and restart:

```bash
docker compose down
docker compose up -d
```

### 8.2 Database changes not applying

If you changed `database/schema.sql` or `database/seed.sql` after the first run, MySQL will not auto-reapply them.

Options:
- Preferred: apply new SQL changes as migrations in `database/migrations/` and run them manually.
- Destructive (dev only): reset DB volume:
  ```bash
  docker compose down -v
  docker compose up -d
  ```

### 8.3 404 when navigating to routes

Ensure `public/.htaccess` exists and Apache has `mod_rewrite` enabled (the provided Docker image enables it).

