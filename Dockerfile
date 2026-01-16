FROM php:8.1-apache

# Install PHP extensions required by Yii2
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql mysqli zip intl opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application code (vendor directory is included in site_demo)
COPY site_demo/ /var/www/html/

# Copy Docker-specific config files from deploy-config folder
COPY deploy-config/start_param.php /var/www/html/common/config/start_param.php
COPY deploy-config/main-local.php /var/www/html/common/config/main-local.php
COPY deploy-config/frontend-main-local.php /var/www/html/frontend/config/main-local.php
COPY deploy-config/params-local.php /var/www/html/frontend/config/params-local.php
COPY deploy-config/params-local.php /var/www/html/common/config/params-local.php
COPY deploy-config/entrypoint.sh /entrypoint.sh

RUN chmod +x /entrypoint.sh

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
