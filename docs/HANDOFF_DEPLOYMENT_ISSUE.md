# Kavork PHP Deployment - Handoff Document

## Project Overview
**Kavork** is a Timecafe/Anticafe management system built with **Yii2 PHP framework**. The goal is to deploy it to **Railway** using a Dockerfile-based deployment.

## Current Status: PHP Build Succeeds, Healthcheck Fails

The Dockerfile build completes successfully (~99 seconds), but the container fails healthcheck with:
```
/usr/local/bin/docker-php-entrypoint: 9: exec: /entrypoint.sh: not found
```

## What Has Been Done

### 1. Removed Python/FastAPI Files
Railway was detecting Python files and using nixpacks instead of the Dockerfile. Removed:
- `site_demo/pyproject.toml`
- `site_demo/requirements.txt`
- `site_demo/app/` directory
- `site_demo/alembic/` directory

### 2. Fixed Dockerfile
- Removed composer install step (vendor directory already exists in repo)
- Configured Apache to serve from `frontend/web`
- Added healthcheck.php endpoint

### 3. Fixed Line Endings (Partially)
Created `.gitattributes` to force LF line endings for shell scripts:
```
*.sh text eol=lf
entrypoint.sh text eol=lf
```

**Verified:** `git show HEAD:deploy-config/entrypoint.sh | head -1 | od -c` shows LF endings in git.

## The Problem
Despite the file having LF endings in git, the container reports:
```
exec: /entrypoint.sh: not found
```

This error typically means:
1. File doesn't exist at the path
2. Shebang interpreter not found (e.g., `/bin/bash\r`)
3. File permissions issue

## Key Files

### Dockerfile (C:\dev\kavork\Dockerfile)
```dockerfile
FROM php:8.1-apache

# Install PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libzip-dev libicu-dev unzip git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql mysqli zip intl opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite
WORKDIR /var/www/html

# Copy application (vendor directory included)
COPY site_demo/ /var/www/html/

# Copy deploy configs
COPY deploy-config/start_param.php /var/www/html/common/config/start_param.php
COPY deploy-config/main-local.php /var/www/html/common/config/main-local.php
COPY deploy-config/frontend-main-local.php /var/www/html/frontend/config/main-local.php
COPY deploy-config/params-local.php /var/www/html/frontend/config/params-local.php
COPY deploy-config/params-local.php /var/www/html/common/config/params-local.php
COPY deploy-config/entrypoint.sh /entrypoint.sh

RUN chmod +x /entrypoint.sh

# Apache config
ENV APACHE_DOCUMENT_ROOT=/var/www/html/frontend/web
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Yii2 runtime dirs
RUN mkdir -p /var/www/html/frontend/runtime \
    && mkdir -p /var/www/html/backend/runtime \
    && mkdir -p /var/www/html/console/runtime \
    && mkdir -p /var/www/html/frontend/web/assets

RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 8080
CMD ["/entrypoint.sh"]
```

### entrypoint.sh (C:\dev\kavork\deploy-config\entrypoint.sh)
```bash
#!/bin/bash
set -e

PORT=${PORT:-80}
echo "Starting entrypoint, PORT=${PORT}"

# Completely rewrite Apache port configuration for reliability
echo "Configuring Apache to listen on port ${PORT}..."

cat > /etc/apache2/ports.conf << EOF
Listen ${PORT}
EOF

cat > /etc/apache2/sites-available/000-default.conf << EOF
<VirtualHost *:${PORT}>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html/frontend/web

    <Directory /var/www/html/frontend/web>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/error.log
    CustomLog \${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

echo "Starting Apache on port ${PORT}..."
exec apache2-foreground
```

### railway.json (C:\dev\kavork\railway.json)
```json
{
  "$schema": "https://railway.app/railway.schema.json",
  "build": {
    "builder": "DOCKERFILE",
    "dockerfilePath": "Dockerfile"
  },
  "deploy": {
    "healthcheckPath": "/healthcheck.php",
    "healthcheckTimeout": 300,
    "restartPolicyType": "ON_FAILURE"
  }
}
```

## Possible Solutions to Try

### 1. Use Inline Entrypoint (Most Likely Fix)
Instead of copying entrypoint.sh, write the script inline in the Dockerfile:
```dockerfile
RUN printf '#!/bin/bash\nset -e\nPORT=${PORT:-80}\necho "Listen $PORT" > /etc/apache2/ports.conf\nexec apache2-foreground\n' > /entrypoint.sh && chmod +x /entrypoint.sh
```

### 2. Change Shebang to /bin/sh
The php:8.1-apache image might not have bash at /bin/bash. Try:
```bash
#!/bin/sh
```

### 3. Use dos2unix in Dockerfile
Add line ending conversion in the Dockerfile:
```dockerfile
COPY deploy-config/entrypoint.sh /entrypoint.sh
RUN apt-get update && apt-get install -y dos2unix && dos2unix /entrypoint.sh && chmod +x /entrypoint.sh
```

### 4. Bypass Entrypoint Entirely
Use CMD to run Apache directly and handle port via environment:
```dockerfile
# Remove entrypoint.sh references
CMD ["apache2-foreground"]
```
And configure Apache port at build time or via environment variables.

## MySQL Database
- Already running on Railway with 20+ tables
- Environment variables configured:
  - MYSQLHOST=mysql.railway.internal
  - MYSQLPORT=3306
  - MYSQLDATABASE=railway
  - MYSQLUSER=root
  - MYSQLPASSWORD=[set in Railway]

## Railway Project Details
- Project: kavork
- Service: kavork-app
- Environment: production
- URL: https://kavork-app-production.up.railway.app

## Commands
```bash
# Deploy
cd /c/dev/kavork && railway up

# Check logs
cd /c/dev/kavork && railway logs

# Check variables
cd /c/dev/kavork && railway variables
```

## Git History (Recent)
```
3f92023 Add .gitattributes to force LF line endings for shell scripts
ddf0004 Remove Python/FastAPI files - this is a PHP project
feb92de Rewrite Apache config in entrypoint for reliability
5bcfd70 Add simple PHP test file to debug Apache
7b403aa Simplify Dockerfile: use existing vendor directory, improve entrypoint
```

## Next Steps
1. **Fix the entrypoint.sh execution issue** - try one of the solutions above
2. Once container starts, verify healthcheck.php returns 200
3. Test the main site at /
4. If Yii2 errors occur, check database connectivity and config files
