#!/bin/bash

# Fix Laravel storage permissions for Plesk
# Run this after git pull or deployment

echo "🔧 Fixing Laravel storage permissions..."

# First, let's find the correct web server user
echo "🔍 Detecting web server user..."

# Check who owns the public directory
PUBLIC_OWNER=$(stat -c '%U:%G' public/index.php 2>/dev/null || stat -f '%Su:%Sg' public/index.php 2>/dev/null)

if [ -n "$PUBLIC_OWNER" ]; then
    echo "✅ Detected web server user: $PUBLIC_OWNER"
    WEB_USER="$PUBLIC_OWNER"
else
    # Fallback: try to detect from running processes
    echo "⚠️  Could not detect from public/ directory, checking processes..."

    # Try to find PHP-FPM user
    PHP_USER=$(ps aux | grep -E 'php-fpm|php82-fpm' | grep -v root | grep -v grep | head -1 | awk '{print $1}')

    if [ -n "$PHP_USER" ]; then
        # Get the group
        PHP_GROUP=$(id -gn "$PHP_USER" 2>/dev/null)
        WEB_USER="$PHP_USER:$PHP_GROUP"
        echo "✅ Detected from PHP-FPM: $WEB_USER"
    else
        echo "❌ Could not auto-detect web server user!"
        echo "Please run manually:"
        echo "  ls -la public/"
        echo "  ps aux | grep php-fpm"
        exit 1
    fi
fi

# Set ownership to detected web server user
echo "📝 Setting ownership to: $WEB_USER"
chown -R "$WEB_USER" storage bootstrap/cache

# Set directory permissions (775 = rwxrwxr-x)
echo "📝 Setting directory permissions..."
find storage -type d -exec chmod 775 {} \;
find bootstrap/cache -type d -exec chmod 775 {} \;

# Set file permissions (664 = rw-rw-r--)
echo "📝 Setting file permissions..."
find storage -type f -exec chmod 664 {} \;
find bootstrap/cache -type f -exec chmod 664 {} \;

echo "✅ Permissions fixed!"

# Clear Laravel caches
echo "🧹 Clearing Laravel caches..."
php artisan view:clear
php artisan cache:clear
php artisan config:clear

echo "✅ Caches cleared!"

# Restart PHP-FPM
echo "🔄 Restarting PHP-FPM..."
systemctl restart plesk-php82-fpm 2>/dev/null || echo "⚠️  Could not restart PHP-FPM (may need sudo)"

echo ""
echo "✅ All done! Test your application now."
echo ""
echo "📋 Verification:"
echo "  ls -la storage/framework/views/"
echo "  touch storage/framework/views/test.txt && rm storage/framework/views/test.txt"
