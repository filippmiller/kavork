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

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for caching
COPY site_demo/composer.json site_demo/composer.lock* ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy application code
COPY site_demo/ /var/www/html/

# Create Docker-specific config files (these are gitignored normally)
RUN echo '<?php\n\
defined("YII_DEBUG") or define("YII_DEBUG", false);\n\
defined("YII_LOG_LEVEL") or define("YII_LOG_LEVEL", 0);\n\
defined("YII_ENV") or define("YII_ENV", "prod");\n\
defined("ROOT") or define("ROOT", realpath(__DIR__ . "/../../"));\n\
' > /var/www/html/common/config/start_param.php

RUN echo '<?php\n\
$dbHost = getenv("MYSQLHOST") ?: "localhost";\n\
$dbPort = getenv("MYSQLPORT") ?: "3306";\n\
$dbName = getenv("MYSQLDATABASE") ?: "railway";\n\
$dbUser = getenv("MYSQLUSER") ?: "root";\n\
$dbPass = getenv("MYSQLPASSWORD") ?: "";\n\
return [\n\
    "components" => [\n\
        "db" => [\n\
            "class" => "yii\\\\db\\\\Connection",\n\
            "dsn" => "mysql:host={$dbHost};port={$dbPort};dbname={$dbName}",\n\
            "username" => $dbUser,\n\
            "password" => $dbPass,\n\
            "charset" => "utf8",\n\
        ],\n\
        "mailer" => [\n\
            "class" => "yii\\\\swiftmailer\\\\Mailer",\n\
            "viewPath" => "@common/mail",\n\
            "useFileTransport" => true,\n\
        ],\n\
        "cache" => [\n\
            "class" => "yii\\\\caching\\\\FileCache",\n\
        ],\n\
    ],\n\
];\n\
' > /var/www/html/common/config/main-local.php

RUN echo '<?php\n\
return [\n\
    "components" => [\n\
        "request" => [\n\
            "cookieValidationKey" => getenv("COOKIE_VALIDATION_KEY") ?: "kavork-railway-prod-2026",\n\
        ],\n\
    ],\n\
];\n\
' > /var/www/html/frontend/config/main-local.php

RUN echo '<?php return [];' > /var/www/html/frontend/config/params-local.php
RUN echo '<?php return [];' > /var/www/html/common/config/params-local.php

# Configure Apache to serve from frontend/web
ENV APACHE_DOCUMENT_ROOT /var/www/html/frontend/web
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

# Use PORT environment variable from Railway
RUN sed -i 's/Listen 80/Listen ${PORT:-80}/g' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:${PORT:-80}>/g' /etc/apache2/sites-available/000-default.conf

EXPOSE 8080

CMD ["apache2-foreground"]
