#!/bin/sh
set -e

PORT=${PORT:-80}
echo "Starting entrypoint, PORT=${PORT}"

# Enable PHP error logging to stdout for Railway visibility
echo "Configuring PHP error logging..."
cat > /usr/local/etc/php/conf.d/error_logging.ini << 'PHPEOF'
log_errors = On
error_log = /dev/stderr
display_errors = Off
error_reporting = E_ALL
PHPEOF

# Fix MPM conflict - only use prefork
echo "Fixing MPM configuration..."
rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*
ln -sf /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf 2>/dev/null || true
ln -sf /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load 2>/dev/null || true

# Ensure mod_rewrite is enabled
echo "Enabling mod_rewrite..."
a2enmod rewrite 2>/dev/null || true

# Completely rewrite Apache port configuration for reliability
echo "Configuring Apache to listen on port ${PORT}..."

# Create clean ports.conf
cat > /etc/apache2/ports.conf << EOF
Listen ${PORT}
EOF

# Create clean VirtualHost config with mod_rewrite rules
cat > /etc/apache2/sites-available/000-default.conf << EOF
<VirtualHost *:${PORT}>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html/frontend/web

    <Directory /var/www/html/frontend/web>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted

        # URL rewriting for Yii2 pretty URLs
        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteBase /
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule . index.php [L]
        </IfModule>
    </Directory>

    ErrorLog /dev/stderr
    CustomLog /dev/stdout combined
</VirtualHost>
EOF

echo "=== ports.conf ==="
cat /etc/apache2/ports.conf
echo "=== 000-default.conf ==="
cat /etc/apache2/sites-available/000-default.conf

# Start Yii queue worker in background for email processing
echo "Starting Yii queue worker for background email jobs..."
cd /var/www/html
nohup php yii queue/listen --verbose=1 > /var/log/queue-worker.log 2>&1 &
QUEUE_PID=$!
echo "Queue worker started with PID: ${QUEUE_PID}"
echo "Queue worker logs: /var/log/queue-worker.log"

echo "Starting Apache on port ${PORT}..."
exec apache2-foreground
