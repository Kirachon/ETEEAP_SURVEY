# DSWD Setup (Beginner Guide)

This guide is for DSWD deployments where:
- You have an internal SMTP relay (common in office networks).
- You want to use a root `.env` file for configuration (DB + SMTP) without hardcoding credentials in `src/config/config.local.php`.

## 1) Create a root `.env`

In the project root, copy:
- `.env.example` → `.env`

Then update these (examples only):

### 1.1 Database via env vars

```env
DB_HOST=REPLACE_WITH_DB_HOST
DB_PORT=3306
DB_NAME=REPLACE_WITH_DB_NAME
DB_USER=REPLACE_WITH_DB_USER
DB_PASS=REPLACE_WITH_DB_PASSWORD
```

### 1.2 SMTP via DSWD internal relay (Port 25, no auth)

If DSWD provides an internal SMTP relay (example: `172.16.x.x`), you can configure:

```env
SMTP_HOST=REPLACE_WITH_DSWD_SMTP_RELAY_IP
SMTP_PORT=25
SMTP_ENCRYPTION=none
SMTP_AUTH=false
SMTP_USER=
SMTP_PASS=
SMTP_FROM_EMAIL=app-mailer1@dswd.gov.ph
SMTP_FROM_NAME=ETEEAP Survey OTP
```

If your existing env file uses these keys (also supported):

```env
SMTP_USER=app-mailer1@dswd.gov.ph
SMTP_PASS=
SMTP_NAME="Pamilya sa Bagong Pilipinas"
```

Notes for that format:
- Add `SMTP_AUTH=false` and `SMTP_ENCRYPTION=none` (required for most port 25 relays).
- `SMTP_NAME` is supported as an alias for `SMTP_FROM_NAME`.

Notes:
- Port `25` is typically for server-to-server mail relay and usually works only inside the internal network.
- Some relays only accept connections from whitelisted IPs and may require a specific `SMTP_FROM_EMAIL`.

## 2) Load `.env` from `config.local.php` (non-Docker)

If you are NOT using Docker and your server cannot set environment variables directly, use `config.local.php` to load the root `.env`.

1. Copy:
   - `src/config/config.local.php.example` → `src/config/config.local.php`
2. The example file already includes an optional `.env` loader that reads the project-root `.env` and calls `putenv()` for missing keys.

Important:
- Do not commit `src/config/config.local.php` or `.env`.
- Ensure your web server document root is `public/` so `.env` is not web-accessible.

## 3) Docker deployments (recommended for dev)

For Docker, you typically do NOT need `src/config/config.local.php`.

1. Put your DSWD SMTP + DB values in the root `.env`
2. Start containers:
   - `docker compose up -d --build`

To verify the container sees your env vars:
- `docker compose exec -T app sh -lc 'printenv | egrep \"^(DB|SMTP|OTP)_\"'`

## 4) Quick OTP email test (Docker)

This sends a test email to the configured sender address (`SMTP_FROM_EMAIL` or `SMTP_USER`):

```bash
docker compose exec -T app php -r "define('APP_ROOT','/var/www/html'); require APP_ROOT.'/src/config/app.php'; require APP_ROOT.'/src/helpers/validation.php'; require APP_ROOT.'/src/services/PhpMailerMailer.php'; \$to=getenv('SMTP_FROM_EMAIL')?:getenv('SMTP_USER'); if(!is_string(\$to)||\$to===''){fwrite(STDERR,'No recipient configured via SMTP_FROM_EMAIL/SMTP_USER'.PHP_EOL); exit(2);} PhpMailerMailer::sendText(\$to,'OTP SMTP send test','If you received this email, OTP sending is configured correctly.'); echo 'SEND_OK'.PHP_EOL;"
```

## 5) Troubleshooting

- If you are seeing Mailpit emails instead of real email: your `.env` was not applied (check `printenv` output inside the container).
- If relay rejects sending: ask your network/email admin to whitelist your server IP and confirm allowed `From` address.
- If you need STARTTLS/SSL instead of port 25 (external SMTP like Gmail/Workspace):
  - Use `SMTP_PORT=587` + `SMTP_ENCRYPTION=starttls`, or
  - Use `SMTP_PORT=465` + `SMTP_ENCRYPTION=ssl`
