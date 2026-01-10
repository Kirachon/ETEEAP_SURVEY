# Self-Hosting Guide (Production, Beginner-Friendly)

This is a practical checklist for running the ETEEAP Survey on a server with a real domain.

## 0) What “production ready” means here

- HTTPS enabled
- `APP_ENV=production` and `APP_DEBUG=false`
- installer disabled after first run
- database backups configured
- an update process that includes migrations

## 1) Pick your deployment method

Recommended:
- Docker on a Linux VPS (simplest repeatable deployments)

Alternative:
- Non-Docker LAMP/LEMP (`docs/DEPLOY_NO_DOCKER_BEGINNER.md`)

## 2) Set production configuration

Ensure these are set (environment variables or `storage/config.php`):
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://your-domain`
- `SESSION_SECURE=true`

## 3) DNS + firewall

1) Point your domain to your server’s public IP (A/AAAA record).
2) Allow inbound ports:
- 80/tcp (HTTP)
- 443/tcp (HTTPS)

On Ubuntu with UFW:
```bash
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

## 3) HTTPS

Use a valid TLS certificate (Let’s Encrypt recommended).

## 3b) Recommended HTTPS approach (reverse proxy)

The simplest setup is to run the app on an internal port and place a reverse proxy in front.

Example (Caddy):
1) Install Caddy
2) Configure a site to reverse_proxy to the app container port

If you expose the app on `localhost:8000`, your Caddyfile can be:
```caddy
survey.example.com {
  reverse_proxy 127.0.0.1:8000
}
```

## 4) Backups

Do daily DB backups and test restores.

```bash
mysqldump -u eteeap_user -p eteeap_survey > eteeap_$(date +%Y%m%d).sql
gzip eteeap_*.sql
```

## 5) Disable installer

After install, confirm:
- `storage/install.lock` exists
- Production servers do not set `INSTALLER_ALLOW=true`

## 6) Docker production walkthrough (recommended)

1) Install Docker on your server (Ubuntu):
```bash
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo usermod -aG docker $USER
newgrp docker
```

2) Clone:
```bash
sudo mkdir -p /opt/eteeap
sudo chown -R $USER:$USER /opt/eteeap
cd /opt/eteeap
git clone https://github.com/Kirachon/ETEEAP_SURVEY.git .
```

3) Edit `docker-compose.yml` for production values (minimum):
- strong DB passwords
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://your-domain`

4) Start:
```bash
docker compose up -d
docker compose ps
```

5) Create/update your admin account:
- Recommended: temporarily enable installer and use `/install` once, then lock it again.
- Alternative: update the `admin_users` table directly.

## 6) Admin hardening

- Change the default admin credentials
- Consider restricting `/admin` access (VPN/IP allowlist) for internal deployments

## 7) Upgrade workflow

For every update:
1) Backup DB
2) Deploy code
3) Run new migrations (if any)
4) Smoke test: survey submit + admin dashboard

## 8) Common production pitfalls

- “Redirects go to localhost”: set `APP_URL` to your real domain (or leave it blank so the app derives it from the request host).
- “Installer is exposed”: remove `INSTALLER_ALLOW` and ensure `storage/install.lock` exists.
- “Schema out of date”: apply new SQL in `database/migrations/` in order.
