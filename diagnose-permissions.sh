#!/bin/bash

# Diagnostic script to find correct web server user and check permissions

echo "🔍 Laravel Permission Diagnostics"
echo "=================================="
echo ""

# 1. Check current directory ownership
echo "1️⃣  Current Directory Ownership:"
ls -la | head -5
echo ""

# 2. Check public directory ownership
echo "2️⃣  Public Directory Ownership:"
ls -la public/ | head -3
echo ""

# 3. Check storage directory ownership
echo "3️⃣  Storage Directory Ownership:"
ls -la storage/framework/ 2>/dev/null || echo "Cannot access storage/framework/"
echo ""

# 4. Check who owns files
echo "4️⃣  File Ownership Details:"
stat -c 'User: %U, Group: %G, Permissions: %a' public/index.php 2>/dev/null || \
stat -f 'User: %Su, Group: %Sg, Permissions: %Lp' public/index.php 2>/dev/null
echo ""

# 5. Check running PHP processes
echo "5️⃣  Running PHP-FPM Processes:"
ps aux | grep -E 'php-fpm|php82-fpm' | grep -v grep | head -5
echo ""

# 6. Check Apache/Nginx processes
echo "6️⃣  Web Server Processes:"
ps aux | grep -E 'apache|httpd|nginx' | grep -v grep | head -5
echo ""

# 7. Check if we can write to storage
echo "7️⃣  Write Test:"
if touch storage/framework/views/test-write.txt 2>/dev/null; then
    echo "✅ Can write to storage/framework/views/"
    rm storage/framework/views/test-write.txt
else
    echo "❌ Cannot write to storage/framework/views/"
    echo "   Error: Permission denied"
fi
echo ""

# 8. Suggest fix command
echo "8️⃣  Suggested Fix:"
PUBLIC_OWNER=$(stat -c '%U:%G' public/index.php 2>/dev/null || stat -f '%Su:%Sg' public/index.php 2>/dev/null)
if [ -n "$PUBLIC_OWNER" ]; then
    echo "Run this command:"
    echo ""
    echo "  chown -R $PUBLIC_OWNER storage bootstrap/cache"
    echo "  chmod -R 775 storage bootstrap/cache"
    echo "  php artisan view:clear"
    echo "  systemctl restart plesk-php82-fpm"
    echo ""
else
    echo "Could not detect owner. Please check manually:"
    echo "  ls -la public/"
fi

echo "=================================="
echo "✅ Diagnostics complete!"
