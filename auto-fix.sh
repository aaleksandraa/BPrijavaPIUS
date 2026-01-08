#!/bin/bash

echo "=========================================="
echo "PIUS Academy Auto-Fix Script"
echo "=========================================="
echo ""

# Postavi putanju do backend-a
BACKEND_PATH="/var/www/vhosts/pius-academy.com/prijava.pius-academy.com/backend"

# Provjeri da li putanja postoji
if [ ! -d "$BACKEND_PATH" ]; then
    echo "❌ Backend folder not found at: $BACKEND_PATH"
    echo "   Please update BACKEND_PATH in this script"
    exit 1
fi

echo "✅ Backend found at: $BACKEND_PATH"
echo ""

# 1. Pokreni Laravel
echo "1. Starting Laravel..."
echo "----------------------------"
cd "$BACKEND_PATH"

# Provjeri da li već radi
if ps aux | grep -q "[p]hp artisan serve"; then
    echo "✅ Laravel is already running"
else
    echo "Starting Laravel on 127.0.0.1:8000..."
    nohup php artisan serve --host=127.0.0.1 --port=8000 > /dev/null 2>&1 &
    sleep 3
    
    if ps aux | grep -q "[p]hp artisan serve"; then
        echo "✅ Laravel started successfully"
    else
        echo "❌ Failed to start Laravel"
        exit 1
    fi
fi
echo ""

# 2. Testiraj Laravel
echo "2. Testing Laravel API..."
echo "----------------------------"
LARAVEL_RESPONSE=$(curl -s http://127.0.0.1:8000/api/packages)
if [[ "$LARAVEL_RESPONSE" == *"data"* ]]; then
    echo "✅ Laravel API is working"
    echo "   Sample: ${LARAVEL_RESPONSE:0:100}..."
else
    echo "❌ Laravel API not responding correctly"
    echo "   Response: $LARAVEL_RESPONSE"
fi
echo ""

# 3. Seed pakete
echo "3. Checking packages in database..."
echo "----------------------------"
PACKAGE_COUNT=$(php artisan tinker --execute="echo \App\Models\Package::count();" 2>/dev/null | tail -1)
echo "Packages in database: $PACKAGE_COUNT"

if [ "$PACKAGE_COUNT" -lt "5" ]; then
    echo "Seeding packages..."
    php artisan db:seed --class=PackageSeeder
    echo "✅ Packages seeded"
else
    echo "✅ Packages already exist"
fi
echo ""

# 4. Clear cache
echo "4. Clearing cache..."
echo "----------------------------"
php artisan config:clear > /dev/null 2>&1
php artisan cache:clear > /dev/null 2>&1
php artisan route:clear > /dev/null 2>&1
echo "✅ Cache cleared"
echo ""

# 5. Testiraj API kroz web server
echo "5. Testing API through web server..."
echo "----------------------------"
API_STATUS=$(curl -s -o /dev/null -w "%{http_code}" https://prijava.pius-academy.com/api/packages)
CONTENT_TYPE=$(curl -s -I https://prijava.pius-academy.com/api/packages | grep -i "content-type" | cut -d' ' -f2-)

echo "Status: $API_STATUS"
echo "Content-Type: $CONTENT_TYPE"

if [ "$API_STATUS" = "200" ] && [[ "$CONTENT_TYPE" == *"application/json"* ]]; then
    echo "✅ API is working correctly through web server"
else
    echo "❌ API is NOT working correctly"
    if [[ "$CONTENT_TYPE" == *"text/html"* ]]; then
        echo "   Problem: Web server is returning HTML instead of JSON"
        echo "   Solution: Web server needs proxy configuration for /api"
        echo ""
        echo "   See WEB_SERVER_FIX.md for instructions"
    fi
fi
echo ""

# 6. Summary
echo "=========================================="
echo "SUMMARY"
echo "=========================================="
echo ""

if [ "$API_STATUS" = "200" ] && [[ "$CONTENT_TYPE" == *"application/json"* ]]; then
    echo "✅ Everything is working!"
    echo ""
    echo "If admin dashboard still shows white screen:"
    echo "1. Clear browser cache (Ctrl+Shift+Del)"
    echo "2. Hard reload (Ctrl+F5)"
    echo "3. Try incognito mode"
else
    echo "❌ Issues found:"
    echo ""
    if [ "$API_STATUS" != "200" ]; then
        echo "- API not responding (HTTP $API_STATUS)"
    fi
    if [[ "$CONTENT_TYPE" != *"application/json"* ]]; then
        echo "- Web server not proxying /api to Laravel"
        echo ""
        echo "Next steps:"
        echo "1. Find your web server config file"
        echo "2. Add proxy configuration for /api"
        echo "3. See WEB_SERVER_FIX.md for details"
        echo ""
        echo "Quick commands:"
        echo ""
        echo "# Find config files:"
        echo "find /etc/nginx /etc/apache2 /var/www/vhosts -name '*.conf' 2>/dev/null | grep -i pius"
        echo ""
        echo "# For Nginx, add this BEFORE 'location /':"
        echo "location /api {"
        echo "    proxy_pass http://127.0.0.1:8000;"
        echo "    proxy_set_header Host \$host;"
        echo "}"
        echo ""
        echo "# For Apache, add this:"
        echo "ProxyPass /api http://127.0.0.1:8000/api"
        echo "ProxyPassReverse /api http://127.0.0.1:8000/api"
    fi
fi
echo ""
