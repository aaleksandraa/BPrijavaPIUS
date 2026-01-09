#!/bin/bash

# PIUS Academy Production Fix Script
# Fixes CORS and HTTPS issues

echo "🚀 PIUS Academy Production Fix"
echo "================================"
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}❌ Please run as root (sudo)${NC}"
    exit 1
fi

echo "📋 Step 1: Backup current .env"
if [ -f .env ]; then
    cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
    echo -e "${GREEN}✅ Backup created${NC}"
else
    echo -e "${YELLOW}⚠️  No .env file found${NC}"
fi

echo ""
echo "📝 Step 2: Update .env configuration"
echo "Please manually update these values in .env:"
echo ""
echo "APP_URL=https://api.prijava.pius-academy.com"
echo "APP_DEBUG=false"
echo "SESSION_DOMAIN=.pius-academy.com"
echo "SESSION_SECURE_COOKIE=true"
echo "SESSION_SAME_SITE=none"
echo "FRONTEND_URL=https://prijava.pius-academy.com"
echo ""
read -p "Press Enter when you've updated .env..."

echo ""
echo "🧹 Step 3: Clear Laravel cache"
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo -e "${GREEN}✅ Cache cleared${NC}"

echo ""
echo "🔄 Step 4: Restart services"
systemctl restart php8.2-fpm
systemctl restart nginx
echo -e "${GREEN}✅ Services restarted${NC}"

echo ""
echo "🧪 Step 5: Testing API"
echo ""

# Test API endpoint
echo "Testing: https://api.prijava.pius-academy.com/packages"
RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" https://api.prijava.pius-academy.com/packages)

if [ "$RESPONSE" = "200" ]; then
    echo -e "${GREEN}✅ API is responding (HTTP $RESPONSE)${NC}"
else
    echo -e "${RED}❌ API returned HTTP $RESPONSE${NC}"
fi

echo ""
echo "Testing CORS headers..."
CORS_HEADER=$(curl -s -I https://api.prijava.pius-academy.com/packages \
    -H "Origin: https://prijava.pius-academy.com" | grep -i "access-control-allow-origin")

if [ -n "$CORS_HEADER" ]; then
    echo -e "${GREEN}✅ CORS headers present:${NC}"
    echo "   $CORS_HEADER"
else
    echo -e "${RED}❌ CORS headers missing${NC}"
fi

echo ""
echo "📊 Step 6: Check logs"
echo ""
echo "Last 5 lines from Laravel log:"
tail -n 5 storage/logs/laravel.log 2>/dev/null || echo "No log file found"

echo ""
echo "Last 5 lines from Nginx error log:"
tail -n 5 /var/log/nginx/error.log 2>/dev/null || echo "No log file found"

echo ""
echo "================================"
echo "✨ Fix completed!"
echo ""
echo "Next steps:"
echo "1. Open https://prijava.pius-academy.com/registracija"
echo "2. Check DevTools → Network tab"
echo "3. Verify packages are loading without CORS errors"
echo ""
echo "If issues persist, check:"
echo "- SSL certificates: sudo certbot certificates"
echo "- Nginx config: /etc/nginx/sites-available/api.prijava.pius-academy.com"
echo "- Laravel logs: tail -f storage/logs/laravel.log"
echo ""
