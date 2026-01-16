#!/bin/bash
# Update Apache port configuration at runtime
sed -i "s/Listen 80/Listen ${PORT:-80}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT:-80}>/" /etc/apache2/sites-available/000-default.conf
exec apache2-foreground
