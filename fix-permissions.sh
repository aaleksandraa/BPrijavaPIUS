#!/bin/bash

# Fix Laravel Storage Permissions
# Run this on the server to fix permission issues

echo "Fixing Laravel storage permissions..."

# Set ownership to web server user (usually www-data or psaserv for Plesk)
chown -R psaserv:psacln storage bootstrap/cache

# Set directory permissions
find storage -type d -exec chmod 775 {} \;
find bootstrap/cache -type d -exec chmod 775 {} \;

# Set file permissions
find storage -type f -exec chmod 664 {} \;
find bootstrap/cache -type f -exec chmod 664 {} \;

# Clear compiled views
php artisan view:clear
php artisan cache:clear
php artisan config:clear

echo "Permissions fixed!"
echo ""
echo "Storage directories:"
ls -la storage/
echo ""
echo "Framework directories:"
ls -la storage/framework/
