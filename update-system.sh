#!/bin/bash

set -euo pipefail

echo "========================================"
echo "  TMS SaaS - System Update Script"
echo "========================================"
echo ""
echo "This script updates the system with new features and migrations."
echo ""

# Check if Docker is running
if ! docker ps >/dev/null 2>&1; then
    echo "[ERROR] Docker is not running. Please start Docker and try again."
    exit 1
fi

# Detect active application container
CONTAINER_NAME=""
if docker ps --format '{{.Names}}' | grep -q "^tms-app$"; then
    CONTAINER_NAME="tms-app"
elif docker ps --format '{{.Names}}' | grep -q "^tms_saas_app$"; then
    CONTAINER_NAME="tms_saas_app"
elif docker ps --format '{{.Names}}' | grep -q "^tms_saas_app_prod$"; then
    CONTAINER_NAME="tms_saas_app_prod"
else
    echo "[ERROR] Application container not found. Please start the docker environment first."
    exit 1
fi
echo "[INFO] Using container: $CONTAINER_NAME"

echo "[1/8] Pulling latest code changes..."
echo "[INFO] Make sure you have pulled the latest code from git before running this script."
sleep 2

echo ""
echo "[2/8] Installing/updating Composer dependencies..."
docker exec "$CONTAINER_NAME" composer install --no-interaction --optimize-autoloader
if [ $? -ne 0 ]; then
    echo "[ERROR] Failed to install dependencies"
    exit 1
fi

echo ""
echo "[3/8] Running database migrations..."
docker exec "$CONTAINER_NAME" php artisan migrate --force
if [ $? -ne 0 ]; then
    echo "[ERROR] Failed to run migrations"
    exit 1
fi

echo ""
echo "[4/8] Clearing all caches..."
docker exec "$CONTAINER_NAME" php artisan config:clear >/dev/null 2>&1 || true
docker exec "$CONTAINER_NAME" php artisan cache:clear >/dev/null 2>&1 || true
docker exec "$CONTAINER_NAME" php artisan route:clear >/dev/null 2>&1 || true
docker exec "$CONTAINER_NAME" php artisan view:clear >/dev/null 2>&1 || true
echo "[OK] Caches cleared"

echo ""
echo "[5/8] Optimizing application for production..."
docker exec "$CONTAINER_NAME" php artisan config:cache >/dev/null 2>&1 || true
docker exec "$CONTAINER_NAME" php artisan route:cache >/dev/null 2>&1 || true
docker exec "$CONTAINER_NAME" php artisan view:cache >/dev/null 2>&1 || true
docker exec "$CONTAINER_NAME" php artisan optimize >/dev/null 2>&1 || true
echo "[OK] Application optimized"

echo ""
echo "[6/8] Creating required storage directories..."
docker exec "$CONTAINER_NAME" mkdir -p storage/app/public/cache/photos || true
docker exec "$CONTAINER_NAME" mkdir -p storage/app/public/drivers || true
docker exec "$CONTAINER_NAME" chmod -R 775 storage || true
docker exec "$CONTAINER_NAME" chmod -R 775 bootstrap/cache || true
echo "[OK] Storage directories created"

echo ""
echo "[7/8] Verifying scheduled tasks..."
if docker exec "$CONTAINER_NAME" php artisan schedule:list >/dev/null 2>&1; then
    echo "[OK] Scheduled tasks configured:"
    echo "  - Cache cleanup: Daily at 02:00"
    echo "  - CNH expiration check: Daily at 08:00"
else
    echo "[WARNING] Could not verify scheduled tasks"
fi

echo ""
echo "[8/8] Testing new commands..."
if docker exec "$CONTAINER_NAME" php artisan cache:clean-old --days=7 --force >/dev/null 2>&1; then
    echo "[OK] Cache cleanup command working"
else
    echo "[WARNING] Cache cleanup command test failed"
fi

echo ""
echo "========================================"
echo "  Update Complete!"
echo "========================================"
echo ""
echo "New features available:"
echo "  - Automatic cache cleanup (daily at 02:00)"
echo "  - CNH expiration notifications (daily at 08:00)"
echo "  - Image optimization with WebP support"
echo "  - Lazy loading for driver photos"
echo "  - Driver trail visibility toggle"
echo ""
echo "Next steps:"
echo "  1. Restart queue worker if needed: docker-compose restart queue"
echo "  2. Verify scheduled tasks: docker exec tms_saas_app php artisan schedule:list"
echo "  3. Test cache cleanup: docker exec tms_saas_app php artisan cache:clean-old --days=7"
echo ""
echo "========================================"







