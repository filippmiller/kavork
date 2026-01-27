# Build Process Documentation

**Project:** Kavork
**Platform:** Railway
**Last Updated:** 2026-01-27

---

## Overview

Kavork uses **Docker-based deployment** on Railway with automatic builds triggered by Git pushes to the main branch.

### Build Flow

```
Git Push → Railway Detects Change → Docker Build → Deploy → Health Check → Live
```

**Average Build Time:** ~8 minutes
**Target Build Time:** ~2 minutes (after optimization)

---

## Build Configuration

### Dockerfile Location
`/Dockerfile` (project root)

### Build Steps

1. **Base Image:** `php:8.2-apache`
2. **System Dependencies:** Install PHP extensions (gd, pdo, mysql, zip, intl)
3. **Composer Install:** Install PHP dependencies
4. **Apache Configuration:** Configure virtual host for Yii2
5. **File Permissions:** Set runtime directory permissions
6. **Queue Worker:** Start background job processor

---

## Detailed Build Process

### Step 1: Base Image (30 seconds)
```dockerfile
FROM php:8.2-apache
```
Downloads PHP 8.2 with Apache pre-installed.

### Step 2: System Packages (60-90 seconds)
```dockerfile
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libzip-dev libicu-dev unzip git curl
```
Installs system libraries needed for PHP extensions.

### Step 3: PHP Extensions (60-90 seconds)
```dockerfile
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql mysqli zip intl opcache
```
Compiles and installs PHP extensions from source.

### Step 4: Composer (10 seconds)
```dockerfile
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer
```
Installs Composer for dependency management.

### Step 5: Application Code (5 seconds)
```dockerfile
COPY site_demo/ /var/www/html/
```
Copies application files into container.

### Step 6: Composer Dependencies (5-10 minutes) ⚠️ SLOW
```dockerfile
RUN cd /var/www/html && composer update --no-dev \
    --optimize-autoloader --no-interaction --ignore-platform-reqs
```

**Current Issue:** Uses `composer update` which:
- Recalculates all 276+ dependencies from scratch
- Downloads metadata from Packagist
- Resolves entire dependency tree
- Takes **5-10 minutes**

**Optimization:** Change to `composer install`:
```dockerfile
RUN cd /var/www/html && composer install --no-dev \
    --optimize-autoloader --no-interaction
```
- Reads `composer.lock` for exact versions
- No dependency resolution needed
- Takes **30-60 seconds**
- **Reduces build time by 80%**

### Step 7: Configuration (5 seconds)
```dockerfile
COPY deploy-config/start_param.php /var/www/html/common/config/
COPY deploy-config/main-local.php /var/www/html/common/config/
# ... more config files
```
Copies production-specific configuration.

### Step 8: Apache Setup (5 seconds)
```dockerfile
ENV APACHE_DOCUMENT_ROOT=/var/www/html/frontend/web
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf
```
Configures Apache to serve from Yii2's web directory.

### Step 9: Permissions (5 seconds)
```dockerfile
RUN mkdir -p /var/www/html/frontend/runtime \
    && chown -R www-data:www-data /var/www/html
```
Creates runtime directories with correct permissions.

### Step 10: Entrypoint (Startup)
```dockerfile
CMD ["/entrypoint.sh"]
```
Runs startup script that:
- Configures PHP error logging
- Sets up Apache ports
- Starts queue worker
- Launches Apache

---

## Build Optimization Strategies

### Immediate Wins

1. **Use `composer install` instead of `update`**
   - Impact: -5 to -7 minutes
   - Risk: Low (composer.lock is committed)
   - Status: Staged, ready to deploy

### Future Optimizations

2. **Multi-stage Docker build**
   - Separate build stage from runtime stage
   - Reduces final image size
   - Impact: -30 seconds, smaller image

3. **Layer caching optimization**
   - Reorder Dockerfile steps
   - Put changing files last
   - Impact: Faster rebuilds when only code changes

4. **Railway build cache**
   - Cache vendor directory between builds
   - Railway-specific configuration needed
   - Impact: -30 seconds for dependencies

---

## Build Triggers

### Automatic Builds
- ✅ Push to `main` branch
- ✅ Merge pull request to `main`

### Manual Builds
- Railway dashboard → "Deploy" → "Redeploy"
- Railway CLI: `railway up`

---

## Environment Variables

Set in Railway dashboard:

### Database
- `MYSQLHOST` - Database hostname
- `MYSQLPORT` - Database port (3306)
- `MYSQLDATABASE` - Database name
- `MYSQLUSER` - Database username
- `MYSQLPASSWORD` - Database password

### Email (SMTP)
- `SMTP_HOST` - SMTP server (smtp.gmail.com)
- `SMTP_USERNAME` - Email address
- `SMTP_PASSWORD` - Email password
- `SMTP_PORT` - SMTP port (587)

### Application
- `PORT` - Web server port (set by Railway, typically 8080)

---

## Deployment Checklist

Before deploying to production:

- [ ] Test changes locally
- [ ] Verify composer dependencies resolve: `composer update --dry-run`
- [ ] Check for PHP syntax errors: `php -l file.php`
- [ ] Review git diff for unintended changes
- [ ] Ensure `composer.lock` is committed
- [ ] Deploy during low-traffic hours if possible
- [ ] Monitor logs during/after deployment
- [ ] Test critical paths (login, checkout, queue)

---

## Rollback Procedure

If deployment fails or causes issues:

### Option 1: Git Revert (Recommended)
```bash
git revert HEAD
git push
```
Railway will auto-deploy the revert.

### Option 2: Railway Dashboard
1. Go to Railway dashboard
2. Find previous successful deployment
3. Click "Redeploy"

### Option 3: Railway CLI
```bash
railway rollback
```

---

## Monitoring

### Health Checks

- **Railway automatic:** `GET /healthcheck.php` every 30 seconds
- **Custom:** `GET /queue-status` (when deployed)

### Logs

```bash
# View live logs
railway logs --tail 100

# View logs from specific time
railway logs --since 1h

# Filter logs
railway logs | grep ERROR
```

---

## Common Build Issues

### Issue: Composer dependency conflict
**Error:** "Your requirements could not be resolved"
**Fix:** Check `composer.json` version constraints, run `composer update --dry-run`

### Issue: PHP extensions missing
**Error:** "extension xxx is missing from your system"
**Fix:** Add to Dockerfile's `docker-php-ext-install` line

### Issue: Permission denied
**Error:** "mkdir: cannot create directory"
**Fix:** Check `chown` commands in Dockerfile

### Issue: Port already in use
**Error:** "bind: address already in use"
**Fix:** Ensure no hardcoded ports, use `$PORT` environment variable

---

## Performance Metrics

### Current Build Performance

| Stage | Time | % of Total |
|-------|------|-----------|
| Base image | 30s | 6% |
| System packages | 90s | 19% |
| PHP extensions | 90s | 19% |
| Composer | 10s | 2% |
| Copy files | 5s | 1% |
| **Composer update** | **300s** | **63%** ⚠️ |
| Config & permissions | 15s | 3% |
| **Total** | **~8 min** | **100%** |

### Target Performance (After Optimization)

| Stage | Time | Improvement |
|-------|------|-------------|
| Composer install | 45s | -255s (-85%) |
| **Total** | **~2 min** | **-6 min (-75%)** |

---

## References

- [Railway Dockerfile Docs](https://docs.railway.app/deploy/dockerfiles)
- [Composer Install vs Update](https://getcomposer.org/doc/01-basic-usage.md)
- [Docker Best Practices](https://docs.docker.com/develop/dev-best-practices/)
