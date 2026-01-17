# Kavork - Development & Deployment Notes

## Project Overview
Kavork is a franchise management system built with Yii2 PHP framework, deployed on Railway.

**Live URL:** https://kavork-app-production.up.railway.app/

## Quick Start for Development

### Prerequisites
- Docker
- Railway CLI (`npm install -g @railway/cli`)
- MySQL client (optional, for database access)

### Local Development
```bash
# Build and run locally
docker build -t kavork .
docker run -p 8080:8080 -e PORT=8080 kavork
```

### Deploy to Railway
```bash
cd C:/dev/kavork
railway up
```

## Architecture

### Docker Setup
- **Base Image:** `php:8.0-apache` (PHP 8.0.30)
  - Note: PHP 8.1 causes segmentation faults with this codebase
- **Document Root:** `/var/www/html/frontend/web`
- **Port:** 8080 (Railway default)

### Key Files
```
kavork/
├── Dockerfile                    # Docker build configuration
├── deploy-config/
│   ├── entrypoint.sh            # Apache startup script
│   ├── start_param.php          # Environment detection
│   ├── main-local.php           # Database config (uses Railway env vars)
│   ├── frontend-main-local.php  # Frontend config
│   └── params-local.php         # Site parameters (PayPal, etc.)
├── site_demo/                    # Yii2 application
│   ├── frontend/                 # Frontend module
│   │   ├── web/                  # Web root (Apache serves from here)
│   │   ├── config/               # Frontend configs
│   │   └── controllers/          # Controllers
│   ├── backend/                  # Backend admin
│   ├── common/                   # Shared code
│   └── console/                  # CLI commands
└── docs/                         # Documentation
```

## Configuration

### Environment Variables (Railway)
Required environment variables in Railway:
- `MYSQLHOST` - MySQL host
- `MYSQLPORT` - MySQL port
- `MYSQLDATABASE` - Database name
- `MYSQLUSER` - Database user
- `MYSQLPASSWORD` - Database password
- `PORT` - HTTP port (Railway sets this to 8080)

### Database
- **Type:** MySQL (Railway managed)
- **Tables:** 48 tables imported from `timecafe_docowor.sql`
- Key tables: `users`, `cafe`, `franchisee`, `franchisee_tariffs`, etc.

### Config Files That Get Copied During Build
The Dockerfile copies config files from `deploy-config/` to their Yii2 locations:
```dockerfile
COPY deploy-config/start_param.php /var/www/html/common/config/start_param.php
COPY deploy-config/main-local.php /var/www/html/common/config/main-local.php
COPY deploy-config/frontend-main-local.php /var/www/html/frontend/config/main-local.php
COPY deploy-config/params-local.php /var/www/html/frontend/config/params-local.php
COPY deploy-config/params-local.php /var/www/html/common/config/params-local.php
```

## Issues Fixed During Setup

### 1. Entrypoint Not Found
**Error:** `exec: /entrypoint.sh: not found`
**Cause:** Windows CRLF line endings + bash shebang
**Fix:**
- Changed shebang from `#!/bin/bash` to `#!/bin/sh`
- Added `sed -i 's/\r$//'` in Dockerfile to strip Windows line endings

### 2. Apache MPM Conflict
**Error:** `AH00534: apache2: Configuration error: More than one MPM loaded`
**Cause:** Multiple MPM modules enabled (event + prefork)
**Fix:** Explicitly remove mpm_event and mpm_worker in Dockerfile and entrypoint:
```bash
rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*
```

### 3. Composer Dependencies
**Error:** `fzaninotto/faker requires PHP ^7.x`
**Fix:** Use `--ignore-platform-reqs` flag:
```dockerfile
RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs
```

### 4. PayPal Config Keys
**Error:** `Undefined array key 'paypal_client_id'`
**Fix:** Added placeholder PayPal keys in `params-local.php`:
```php
'paypal_client_id' => 'sandbox-test-id',
'paypal_client_secret' => 'sandbox-test-secret',
```

### 5. Missing Database Tables
**Error:** `SQLSTATE[42S02]: Base table or view not found: 1146 Table 'railway.franchisee_tariffs'`
**Fix:** Imported full schema from `timecafe_docowor.sql` and inserted required data

### 6. PHP Segmentation Faults
**Error:** `child pid XX exit signal Segmentation fault (11)`
**Cause:** PHP 8.1 compatibility issues with legacy code
**Fix:** Downgraded to PHP 8.0:
```dockerfile
FROM php:8.0-apache
```
Also disabled OPcache entirely:
```dockerfile
RUN rm -f /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini
```

### 7. URL Rewriting (mod_rewrite)
**Error:** `/login` returns 404, but `/index.php?r=site/login` works
**Cause:** mod_rewrite not properly enabled
**Fix:** Added explicit `a2enmod rewrite` in entrypoint.sh

## URLs

### Main Pages
- **Landing:** https://kavork-app-production.up.railway.app/
- **Login:** https://kavork-app-production.up.railway.app/index.php?r=site/login
  - Or `/login` after mod_rewrite fix is deployed

### Healthcheck
- **URL:** https://kavork-app-production.up.railway.app/healthcheck.php
- Returns 200 with server info

## Troubleshooting

### Check Logs
```bash
railway logs
```

### Test Deployment
```bash
# Check if site responds
curl -I https://kavork-app-production.up.railway.app/

# Check healthcheck
curl https://kavork-app-production.up.railway.app/healthcheck.php
```

### Common Issues
1. **500 errors:** Check Railway logs for PHP errors
2. **404 on pretty URLs:** mod_rewrite not enabled or .htaccess not processed
3. **Segfaults:** Use PHP 8.0, not 8.1
4. **Database errors:** Check Railway MySQL credentials in environment

## Excluded from Project
Per requirements, the following were excluded:
- eBay integration
- Sniper functionality
- Bidnapper functionality

(No code related to these was found in the codebase)

## Session History (2026-01-17)
1. Found handoff documents describing deployment issues
2. Fixed entrypoint.sh (shebang + line endings)
3. Fixed Apache MPM conflicts
4. Added Composer install to Docker build
5. Configured params-local.php with required keys
6. Imported database schema (48 tables)
7. Downgraded PHP 8.1 to 8.0 to fix segfaults
8. Disabled OPcache to prevent crashes
9. Enabled mod_rewrite for pretty URLs
