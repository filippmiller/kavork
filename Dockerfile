FROM php:8.0-apache

# Install PHP extensions required by Yii2 and Composer
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql mysqli zip intl opcache \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite and fix MPM conflict - forcefully remove conflicting MPMs
RUN rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* \
    && a2enmod mpm_prefork rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application code
COPY site_demo/ /var/www/html/

# Install PHP dependencies with Composer (skip security checks for legacy packages)
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN cd /var/www/html && composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

# Cache bust: 2026-01-17-v4

# Completely disable OPcache to prevent segfaults
RUN rm -f /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

# Increase PHP upload limits
RUN echo "upload_max_filesize = 100M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 100M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_execution_time = 600" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 512M" >> /usr/local/etc/php/conf.d/uploads.ini
# Copy Docker-specific config files from deploy-config folder
COPY deploy-config/start_param.php /var/www/html/common/config/start_param.php
COPY deploy-config/main-local.php /var/www/html/common/config/main-local.php
COPY deploy-config/console-main-local.php /var/www/html/console/config/main-local.php
COPY deploy-config/frontend-main-local.php /var/www/html/frontend/config/main-local.php
COPY deploy-config/params-local.php /var/www/html/frontend/config/params-local.php
COPY deploy-config/params-local.php /var/www/html/common/config/params-local.php
COPY deploy-config/params-local.php /var/www/html/console/config/params-local.php


COPY deploy-config/entrypoint.sh /entrypoint.sh

# Fix Windows line endings and make executable
RUN sed -i 's/\r$//' /entrypoint.sh && chmod +x /entrypoint.sh

# Configure Apache to serve from frontend/web
ENV APACHE_DOCUMENT_ROOT=/var/www/html/frontend/web
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Allow .htaccess overrides
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Create runtime directories Yii2 needs
RUN mkdir -p /var/www/html/frontend/runtime \
    && mkdir -p /var/www/html/backend/runtime \
    && mkdir -p /var/www/html/console/runtime \
    && mkdir -p /var/www/html/frontend/web/assets

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 8080

CMD ["/entrypoint.sh"]
