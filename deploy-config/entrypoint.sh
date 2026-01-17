#!/bin/sh
set -e

PORT=${PORT:-80}
echo "Starting entrypoint, PORT=${PORT}"

# Completely rewrite Apache port configuration for reliability
echo "Configuring Apache to listen on port ${PORT}..."

# Create clean ports.conf
cat > /etc/apache2/ports.conf << EOF
Listen ${PORT}
EOF

# Create clean VirtualHost config
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

echo "=== ports.conf ==="
cat /etc/apache2/ports.conf
echo "=== 000-default.conf ==="
cat /etc/apache2/sites-available/000-default.conf

echo "Starting Apache on port ${PORT}..."
exec apache2-foreground
