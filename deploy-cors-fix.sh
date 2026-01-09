#!/bin/bash

# PIUS Academy - CORS 404 Fix Deployment Script
# This script deploys the CORS middleware fix to production

set -e  # Exit on error

echo "🚀 PIUS Academy - CORS 404 Fix Deployment"
echo "=========================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Check if running in backend directory
if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Error: Must run from backend directory${NC}"
    echo "cd /var/www/backend && sudo bash deploy-cors-fix.sh"
    exit 1
fi

echo -e "${BLUE}📋 Step 1: Backup current files${NC}"
BACKUP_DIR="backups/$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"
cp -r app/Http/Middleware "$BACKUP_DIR/" 2>/dev/null || true
cp bootstrap/app.php "$BACKUP_DIR/" 2>/dev/null || true
cp config/app.php "$BACKUP_DIR/" 2>/dev/null || true
cp config/cors.php "$BACKUP_DIR/" 2>/dev/null || true
echo -e "${GREEN}✅ Backup created in $BACKUP_DIR${NC}"
echo ""

echo -e "${BLUE}📥 Step 2: Pull latest code${NC}"
git fetch origin
CURRENT_BRANCH=$(git branch --show-current)
echo "Current branch: $CURRENT_BRANCH"
read -p "Pull from origin/$CURRENT_BRANCH? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    git pull origin "$CURRENT_BRANCH"
    echo -e "${GREEN}✅ Code updated${NC}"
else
    echo -e "${YELLOW}⚠️  Skipped git pull${NC}"
fi
echo ""

echo -e "${BLUE}📦 Step 3: Install/Update dependencies${NC}"
composer install --no-dev --optimize-autoloader
echo -e "${GREEN}✅ Dependencies updated${NC}"
echo ""

echo -e "${BLUE}🧹 Step 4: Clear all caches${NC}"
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo -e "${GREEN}✅ Caches cleared${NC}"
echo ""

echo -e "${BLUE}🔧 Step 5: Optimize for production${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo -e "${GREEN}✅ Optimizations applied${NC}"
echo ""

echo -e "${BLUE}🔐 Step 6: Set permissions${NC}"
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
echo -e "${GREEN}✅ Permissions set${NC}"
echo ""

echo -e "${BLUE}🔄 Step 7: Restart services${NC}"
systemctl restart php8.2-fpm
systemctl restart nginx
echo -e "${GREEN}✅ Services restarted${NC}"
echo ""

echo -e "${BLUE}🧪 Step 8: Testing${NC}"
echo ""

# Test 1: Check if API is responding
echo "Test 1: API Health Check"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://api.prijava.pius-academy.com/up 2>/dev/null || echo "000")
if [ "$HTTP_CODE" = "200" ]; then
    echo -e "${GREEN}✅ API is responding (HTTP $HTTP_CODE)${NC}"
else
    echo -e "${RED}❌ API returned HTTP $HTTP_CODE${NC}"
fi
echo ""

# Test 2: Check OPTIONS request
echo "Test 2: OPTIONS Preflight Request"
RESPONSE=$(curl -s -X OPTIONS https://api.prijava.pius-academy.com/packages \
    -H "Origin: https://prijava.pius-academy.com" \
    -H "Access-Control-Request-Method: GET" \
    -H "Access-Control-Request-Headers: authorization,content-type" \
    -w "\nHTTP_CODE:%{http_code}" 2>/dev/null)

HTTP_CODE=$(echo "$RESPONSE" | grep "HTTP_CODE:" | cut -d: -f2)
CORS_HEADER=$(echo "$RESPONSE" | grep -i "access-control-allow-origin" || echo "")

if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "204" ]; then
    echo -e "${GREEN}✅ OPTIONS request successful (HTTP $HTTP_CODE)${NC}"
else
    echo -e "${RED}❌ OPTIONS request failed (HTTP $HTTP_CODE)${NC}"
fi

if [ -n "$CORS_HEADER" ]; then
    echo -e "${GREEN}✅ CORS headers present${NC}"
else
    echo -e "${YELLOW}⚠️  CORS headers not detected (might be OK if handled by Nginx)${NC}"
fi
echo ""

# Test 3: Check GET request
echo "Test 3: GET Request with CORS"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://api.prijava.pius-academy.com/packages \
    -H "Origin: https://prijava.pius-academy.com" 2>/dev/null || echo "000")
if [ "$HTTP_CODE" = "200" ]; then
    echo -e "${GREEN}✅ GET request successful (HTTP $HTTP_CODE)${NC}"
else
    echo -e "${RED}❌ GET request failed (HTTP $HTTP_CODE)${NC}"
fi
echo ""

echo -e "${BLUE}📊 Step 9: Check logs${NC}"
echo ""
echo "Last 5 lines from Laravel log:"
tail -n 5 storage/logs/laravel.log 2>/dev/null || echo "No log file found"
echo ""
echo "Last 5 lines from Nginx error log:"
tail -n 5 /var/log/nginx/error.log 2>/dev/null || echo "No log file found"
echo ""

echo "=========================================="
echo -e "${GREEN}✨ Deployment completed!${NC}"
echo ""
echo "Next steps:"
echo "1. Open https://prijava.pius-academy.com/registracija"
echo "2. Check DevTools → Network tab"
echo "3. Verify packages are loading without CORS errors"
echo ""
echo "If issues persist:"
echo "- Check Laravel logs: tail -f storage/logs/laravel.log"
echo "- Check Nginx logs: sudo tail -f /var/log/nginx/error.log"
echo "- Verify .env settings: grep -E 'APP_URL|FRONTEND_URL' .env"
echo ""
echo "Backup location: $BACKUP_DIR"
echo ""
