#!/bin/bash

echo "🔧 Fixing CORS in Production"
echo "============================="
echo ""

# Check if we're in the backend directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: Not in Laravel backend directory!"
    echo "Please run: cd /var/www/vhosts/pius-academy.com/api.prijava.pius-academy.com"
    exit 1
fi

echo "📍 Current directory: $(pwd)"
echo ""

# Step 1: Clear all caches
echo "1️⃣ Clearing Laravel caches..."
echo "--------------------------------"
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo "✅ Caches cleared"
echo ""

# Step 2: Check .env
echo "2️⃣ Checking .env configuration..."
echo "-----------------------------------"
echo "FRONTEND_URL: $(grep FRONTEND_URL .env | cut -d'=' -f2)"
echo "APP_URL: $(grep APP_URL .env | cut -d'=' -f2)"
echo "SESSION_DOMAIN: $(grep SESSION_DOMAIN .env | cut -d'=' -f2)"
echo ""

# Step 3: Test CORS middleware
echo "3️⃣ Testing CORS response..."
echo "----------------------------"
echo "Testing OPTIONS request to /api/packages..."
curl -I -X OPTIONS \
  -H "Origin: https://prijava.pius-academy.com" \
  -H "Access-Control-Request-Method: POST" \
  -H "Access-Control-Request-Headers: Content-Type, Authorization" \
  https://api.prijava.pius-academy.com/api/packages 2>&1 | grep -i "access-control"

echo ""
echo "Testing POST request to /api/auth/login..."
curl -I -X POST \
  -H "Origin: https://prijava.pius-academy.com" \
  -H "Content-Type: application/json" \
  https://api.prijava.pius-academy.com/api/auth/login 2>&1 | grep -i "access-control"

echo ""

# Step 4: Restart PHP-FPM
echo "4️⃣ Restarting PHP-FPM..."
echo "------------------------"
systemctl restart plesk-php82-fpm
if [ $? -eq 0 ]; then
    echo "✅ PHP-FPM restarted"
else
    echo "⚠️  Could not restart PHP-FPM (may need sudo)"
fi
echo ""

# Step 5: Check logs
echo "5️⃣ Checking recent logs..."
echo "---------------------------"
if [ -f "storage/logs/laravel.log" ]; then
    echo "Last 10 lines of Laravel log:"
    tail -10 storage/logs/laravel.log
else
    echo "No Laravel log found"
fi
echo ""

# Step 6: Final test
echo "6️⃣ Final CORS test..."
echo "----------------------"
echo "Testing from frontend origin..."
CORS_TEST=$(curl -s -I -X OPTIONS \
  -H "Origin: https://prijava.pius-academy.com" \
  -H "Access-Control-Request-Method: POST" \
  https://api.prijava.pius-academy.com/api/auth/login)

if echo "$CORS_TEST" | grep -q "Access-Control-Allow-Origin"; then
    echo "✅ CORS headers are present!"
    echo "$CORS_TEST" | grep "Access-Control"
else
    echo "❌ CORS headers are MISSING!"
    echo ""
    echo "Full response:"
    echo "$CORS_TEST"
fi
echo ""

echo "✅ CORS fix complete!"
echo ""
echo "📋 Next steps:"
echo "  1. Test in browser: https://prijava.pius-academy.com"
echo "  2. Open DevTools (F12) → Network tab"
echo "  3. Try login or registration"
echo "  4. Check if CORS error is gone"
echo ""
echo "🐛 If still not working:"
echo "  - Check Apache/Plesk configuration"
echo "  - Check if .htaccess is blocking headers"
echo "  - Check PHP-FPM error log: tail -f /var/log/plesk-php82-fpm/error.log"
