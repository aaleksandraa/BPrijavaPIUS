#!/bin/bash
set -euo pipefail

echo "PIUS Backend - Deploy"
echo "====================="

cd "$(dirname "$0")"

echo "Pull latest code..."
git pull origin main

echo "Clear caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "Rebuild config cache..."
php artisan config:cache
php artisan route:cache

echo "Set permissions..."
chown -R pius-academy:psaserv storage bootstrap/cache app 2>/dev/null || true
chmod -R 775 storage bootstrap/cache

echo "Restart PHP-FPM..."
systemctl restart plesk-php82-fpm

echo "Health check..."
curl -s -o /dev/null -w "API /up status: %{http_code}\n" https://api.prijava.pius-academy.com/up

echo "Backend deploy complete."
