#!/bin/bash

# PIUS Academy - API Diagnostics
# Comprehensive API testing

echo "🔍 PIUS Academy - API Diagnostics"
echo "=================================="
echo ""

BACKEND_DIR="/var/www/vhosts/pius-academy.com/api.prijava.pius-academy.com"
API_URL="https://api.prijava.pius-academy.com"

cd "$BACKEND_DIR"

echo "📋 Step 1: Check database connection"
php artisan db:show
echo ""

echo "📊 Step 2: Count records in database"
echo "Packages:"
php artisan tinker --execute="echo 'Count: ' . \App\Models\Package::count(); echo PHP_EOL; \App\Models\Package::all()->each(function(\$p) { echo \$p->name . ' (' . \$p->slug . ')' . PHP_EOL; });"
echo ""

echo "Users:"
php artisan tinker --execute="echo 'Count: ' . \App\Models\User::count();"
echo ""

echo "Students:"
php artisan tinker --execute="echo 'Count: ' . \App\Models\Student::count();"
echo ""

echo "Contracts:"
php artisan tinker --execute="echo 'Count: ' . \App\Models\Contract::count();"
echo ""

echo "📡 Step 3: Test API endpoints (without auth)"
echo ""

echo "Test 1: /up (health check)"
curl -s "$API_URL/up" | head -c 200
echo ""
echo ""

echo "Test 2: /packages (public)"
curl -s "$API_URL/packages" | head -c 500
echo ""
echo ""

echo "🔐 Step 4: Check routes"
php artisan route:list | grep -E "packages|students|contracts" | head -20
echo ""

echo "📝 Step 5: Check last 20 lines of Laravel log"
tail -20 storage/logs/laravel.log
echo ""

echo "🔧 Step 6: Check .env configuration"
grep -E "APP_URL|APP_ENV|APP_DEBUG|DB_" .env
echo ""

echo "=================================="
echo "Diagnostics completed!"
echo ""
