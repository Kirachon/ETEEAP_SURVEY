# Windows Deployment Guide (Beginner-Friendly)

This guide helps you run the ETEEAP Survey on Windows without Docker.

## Important: serve from `public/`

Your web server DocumentRoot must point to:
- `<project>\\public`

## Option A: Laragon (recommended)

Use the full Laragon guide:
- `docs/SETUP_LARAGON_BEGINNER.md`

## Option B: XAMPP (quick path)

1) Install XAMPP and start Apache + MySQL.
2) Copy the project into:
   - `C:\\xampp\\htdocs\\ETEEAP_SURVEY`
3) Configure a VirtualHost with DocumentRoot:
   - `C:\\xampp\\htdocs\\ETEEAP_SURVEY\\public`
4) Run the installer:
   - `http://eteeap.local/install`
5) Admin login:
   - `http://eteeap.local/admin/login`

### XAMPP: minimal VirtualHost example

1) Enable vhosts include (XAMPP `httpd.conf`):
- `LoadModule rewrite_module modules/mod_rewrite.so`
- `Include conf/extra/httpd-vhosts.conf`

2) Add a vhost in `apache\\conf\\extra\\httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
  ServerName eteeap.local
  DocumentRoot "C:/xampp/htdocs/ETEEAP_SURVEY/public"

  <Directory "C:/xampp/htdocs/ETEEAP_SURVEY/public">
    AllowOverride All
    Require all granted
  </Directory>
</VirtualHost>
```

3) Add a hosts entry (run as Administrator):
- `C:\\Windows\\System32\\drivers\\etc\\hosts`

Add:
```
127.0.0.1 eteeap.local
```

4) Restart Apache in XAMPP control panel.

### Storage permission (common installer blocker)

The installer must write to:
- `<project>\\storage\\`

If the installer says “Storage not writable”, grant Modify permission to your Windows user for the `storage` folder.

## Production note

Windows can work for internal deployments, but for public-facing production, Linux is strongly recommended.
