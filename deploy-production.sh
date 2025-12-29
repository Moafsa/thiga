#!/bin/bash

set -euo pipefail

echo "========================================"
echo "  TMS SaaS - Production Deployment"
echo "========================================"
echo ""
echo "This script deploys the application to production."
echo "WARNING: This will update the production environment!"
echo ""

read -p "Are you sure you want to continue? (yes/no): " confirm
if [ "$confirm" != "yes" ]; then
    echo "Deployment cancelled."
    exit 0
fi

# Check if using production docker-compose
if [ ! -f "docker-compose.prod.yml" ]; then
    echo "[ERROR] docker-compose.prod.yml not found"
    exit 1
fi

echo "[1/10] Pulling latest code..."
git pull origin main || git pull origin master

echo ""
echo "[2/10] Building production containers..."
docker-compose -f docker-compose.prod.yml build --no-cache

echo ""
echo "[3/10] Installing Composer dependencies..."
docker exec tms_saas_app_prod composer install --no-dev --optimize-autoloader --no-interaction

echo ""
echo "[4/10] Running database migrations..."
docker exec tms_saas_app_prod php artisan migrate --force

echo ""
echo "[5/10] Clearing all caches..."
docker exec tms_saas_app_prod php artisan config:clear
docker exec tms_saas_app_prod php artisan cache:clear
docker exec tms_saas_app_prod php artisan route:clear
docker exec tms_saas_app_prod php artisan view:clear

echo ""
echo "[6/10] Optimizing for production..."
docker exec tms_saas_app_prod php artisan config:cache
docker exec tms_saas_app_prod php artisan route:cache
docker exec tms_saas_app_prod php artisan view:cache
docker exec tms_saas_app_prod php artisan optimize

echo ""
echo "[7/10] Creating storage directories..."
docker exec tms_saas_app_prod mkdir -p storage/app/public/cache/photos
docker exec tms_saas_app_prod mkdir -p storage/app/public/drivers
docker exec tms_saas_app_prod chmod -R 775 storage
docker exec tms_saas_app_prod chmod -R 775 bootstrap/cache

echo ""
echo "[8/10] Restarting services..."
docker-compose -f docker-compose.prod.yml restart app queue

echo ""
echo "[9/10] Verifying scheduled tasks..."
docker exec tms_saas_app_prod php artisan schedule:list

echo ""
echo "[10/10] Testing new commands..."
docker exec tms_saas_app_prod php artisan cache:clean-old --days=7 --force >/dev/null 2>&1
if [ $? -eq 0 ]; then
    echo "[OK] Cache cleanup command working"
else
    echo "[WARNING] Cache cleanup command test failed"
fi

echo ""
echo "========================================"
echo "  Deployment Complete!"
echo "========================================"
echo ""
echo "IMPORTANT: Configure cron for scheduled tasks:"
echo "  * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1"
echo ""
echo "Or use supervisor/systemd to run:"
echo "  php artisan schedule:work"
echo ""
echo "========================================"






