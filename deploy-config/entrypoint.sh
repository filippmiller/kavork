#!/bin/bash
set -e

echo "Starting entrypoint, PORT=${PORT:-80}"

# Update Apache port configuration at runtime
echo "Configuring Apache port..."
sed -i "s/Listen 80/Listen ${PORT:-80}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT:-80}>/" /etc/apache2/sites-available/000-default.conf

echo "Apache ports.conf:"
cat /etc/apache2/ports.conf

echo "Starting Apache..."
exec apache2-foreground
