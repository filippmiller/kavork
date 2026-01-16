#!/bin/bash
set -e

PORT=${PORT:-80}
echo "Starting entrypoint, PORT=${PORT}"

# Configure Apache port - more robust replacement
echo "Configuring Apache port to ${PORT}..."

# Update ports.conf - handle various formats
if grep -q "Listen 80" /etc/apache2/ports.conf; then
    sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
else
    echo "Listen ${PORT}" >> /etc/apache2/ports.conf
fi

# Update VirtualHost - handle various formats
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf 2>/dev/null || true
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf 2>/dev/null || true

echo "=== ports.conf ==="
cat /etc/apache2/ports.conf
echo "=== 000-default.conf ==="
cat /etc/apache2/sites-available/000-default.conf | head -20

echo "Starting Apache on port ${PORT}..."
exec apache2-foreground
