#!/bin/bash

echo "=========================================="
echo "PIUS Academy Server Diagnostic"
echo "=========================================="
echo ""

# 1. Check web servers
echo "1. Checking Web Servers..."
echo "----------------------------"
if systemctl is-active --quiet nginx; then
    echo "✅ Nginx is running"
    WEBSERVER="nginx"
elif systemctl is-active --quiet apache2; then
    echo "✅ Apache is running"
    WEBSERVER="apache2"
else
    echo "❌ No web server running!"
    WEBSERVER="none"
fi
echo ""

# 2. Check Laravel
echo "2. Checking Laravel..."
echo "----------------------------"
if ps aux | grep -q "[p]hp artisan serve"; then
    echo "✅ Laravel is running"
    ps aux | grep "[p]hp artisan serve"
else
    echo "❌ Laravel is NOT running!"
    echo "   Run: cd /path/to/backend && php artisan serve --host=127.0.0.1 --port=8000 &"
fi
echo ""

# 3. Check ports
echo "3. Checking Ports..."
echo "----------------------------"
echo "Port 80 (HTTP):"
sudo netstat -tlnp | grep :80 || echo "❌ Not listening"
echo ""
echo "Port 443 (HTTPS):"
sudo netstat -tlnp | grep :443 || echo "❌ Not listening"
echo ""
echo "Port 8000 (Laravel):"
sudo netstat -tlnp | grep :8000 || echo "❌ Not listening"
echo ""

# 4. Test Laravel directly
echo "4. Testing Laravel API..."
echo "----------------------------"
LARAVEL_TEST=$(curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8000/api/packages 2>/dev/null)
if [ "$LARAVEL_TEST" = "200" ]; then
    echo "✅ Laravel API responds: HTTP $LARAVEL_TEST"
    echo "   Sample response:"
    curl -s http://127.0.0.1:8000/api/packages | head -c 200
    echo "..."
else
    echo "❌ Laravel API error: HTTP $LARAVEL_TEST"
fi
echo ""

# 5. Test through web server
echo "5. Testing API through Web Server..."
echo "----------------------------"
API_TEST=$(curl -s -o /dev/null -w "%{http_code}" https://prijava.pius-academy.com/api/packages 2>/dev/null)
CONTENT_TYPE=$(curl -s -I https://prijava.pius-academy.com/api/packages 2>/dev/null | grep -i "content-type" | cut -d' ' -f2-)
if [ "$API_TEST" = "200" ]; then
    echo "✅ API responds: HTTP $API_TEST"
    echo "   Content-Type: $CONTENT_TYPE"
    if [[ "$CONTENT_TYPE" == *"application/json"* ]]; then
        echo "   ✅ Correct content type (JSON)"
    else
        echo "   ❌ Wrong content type! Should be application/json"
        echo "   This means web server is NOT proxying /api to Laravel"
    fi
else
    echo "❌ API error: HTTP $API_TEST"
fi
echo ""

# 6. Check web server config
echo "6. Web Server Configuration..."
echo "----------------------------"
if [ "$WEBSERVER" = "nginx" ]; then
    echo "Nginx config files:"
    ls -la /etc/nginx/sites-enabled/
    echo ""
    echo "Checking for /api proxy in config:"
    if grep -r "location /api" /etc/nginx/sites-enabled/ > /dev/null; then
        echo "✅ Found /api location block"
        grep -A 5 "location /api" /etc/nginx/sites-enabled/*
    else
        echo "❌ No /api location block found!"
        echo "   You need to add proxy configuration"
    fi
elif [ "$WEBSERVER" = "apache2" ]; then
    echo "Apache config files:"
    ls -la /etc/apache2/sites-enabled/
    echo ""
    echo "Checking for /api proxy in config:"
    if grep -r "ProxyPass /api" /etc/apache2/sites-enabled/ > /dev/null; then
        echo "✅ Found ProxyPass for /api"
        grep "ProxyPass /api" /etc/apache2/sites-enabled/*
    else
        echo "❌ No ProxyPass for /api found!"
        echo "   You need to add proxy configuration"
    fi
fi
echo ""

# 7. Check PostgreSQL
echo "7. Checking PostgreSQL..."
echo "----------------------------"
if systemctl is-active --quiet postgresql; then
    echo "✅ PostgreSQL is running"
else
    echo "❌ PostgreSQL is NOT running!"
fi
echo ""

# 8. Check Laravel .env
echo "8. Checking Laravel .env..."
echo "----------------------------"
if [ -f "/path/to/backend/.env" ]; then
    echo "APP_ENV: $(grep APP_ENV /path/to/backend/.env | cut -d'=' -f2)"
    echo "APP_DEBUG: $(grep APP_DEBUG /path/to/backend/.env | cut -d'=' -f2)"
    echo "APP_URL: $(grep APP_URL /path/to/backend/.env | cut -d'=' -f2)"
    echo "DB_CONNECTION: $(grep DB_CONNECTION /path/to/backend/.env | cut -d'=' -f2)"
    echo "SANCTUM_STATEFUL_DOMAINS: $(grep SANCTUM_STATEFUL_DOMAINS /path/to/backend/.env | cut -d'=' -f2)"
else
    echo "❌ .env file not found at /path/to/backend/.env"
    echo "   Update the path in this script"
fi
echo ""

# 9. Summary
echo "=========================================="
echo "SUMMARY"
echo "=========================================="
if [ "$WEBSERVER" != "none" ] && [ "$LARAVEL_TEST" = "200" ] && [ "$API_TEST" = "200" ] && [[ "$CONTENT_TYPE" == *"application/json"* ]]; then
    echo "✅ Everything looks good!"
    echo "   If admin dashboard still shows white screen:"
    echo "   1. Clear browser cache (Ctrl+Shift+Del)"
    echo "   2. Hard reload (Ctrl+F5)"
    echo "   3. Try incognito mode"
else
    echo "❌ Issues found:"
    [ "$WEBSERVER" = "none" ] && echo "   - No web server running"
    [ "$LARAVEL_TEST" != "200" ] && echo "   - Laravel not responding"
    [ "$API_TEST" != "200" ] && echo "   - API not accessible through web server"
    [[ "$CONTENT_TYPE" != *"application/json"* ]] && echo "   - Web server not proxying /api to Laravel"
    echo ""
    echo "   See WEB_SERVER_FIX.md for solutions"
fi
echo ""
