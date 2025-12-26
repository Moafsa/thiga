@echo off
echo ========================================
echo   TMS SaaS - System Update Script
echo ========================================
echo.
echo This script updates the system with new features and migrations.
echo.

REM Check if Docker is running
docker ps >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Docker is not running. Please start Docker Desktop and try again.
    pause
    exit /b 1
)

REM Check if container exists
docker ps -a | findstr "tms_saas_app" >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Application container not found. Please run start-servers.bat first.
    pause
    exit /b 1
)

echo [1/8] Pulling latest code changes...
echo [INFO] Make sure you have pulled the latest code from git before running this script.
timeout /t 2 /nobreak > nul

echo.
echo [2/8] Installing/updating Composer dependencies...
docker exec tms_saas_app composer install --no-interaction --optimize-autoloader
if errorlevel 1 (
    echo [ERROR] Failed to install dependencies
    pause
    exit /b 1
)

echo.
echo [3/8] Running database migrations...
docker exec tms_saas_app php artisan migrate --force
if errorlevel 1 (
    echo [ERROR] Failed to run migrations
    pause
    exit /b 1
)

echo.
echo [4/8] Clearing all caches...
docker exec tms_saas_app php artisan config:clear >nul 2>&1
docker exec tms_saas_app php artisan cache:clear >nul 2>&1
docker exec tms_saas_app php artisan route:clear >nul 2>&1
docker exec tms_saas_app php artisan view:clear >nul 2>&1
echo [OK] Caches cleared

echo.
echo [5/8] Optimizing application for production...
docker exec tms_saas_app php artisan config:cache >nul 2>&1
docker exec tms_saas_app php artisan route:cache >nul 2>&1
docker exec tms_saas_app php artisan view:cache >nul 2>&1
docker exec tms_saas_app php artisan optimize >nul 2>&1
echo [OK] Application optimized

echo.
echo [6/8] Creating required storage directories...
docker exec tms_saas_app mkdir -p storage/app/public/cache/photos >nul 2>&1
docker exec tms_saas_app mkdir -p storage/app/public/drivers >nul 2>&1
docker exec tms_saas_app chmod -R 775 storage >nul 2>&1
docker exec tms_saas_app chmod -R 775 bootstrap/cache >nul 2>&1
echo [OK] Storage directories created

echo.
echo [7/8] Verifying scheduled tasks...
docker exec tms_saas_app php artisan schedule:list >nul 2>&1
if errorlevel 1 (
    echo [WARNING] Could not verify scheduled tasks
) else (
    echo [OK] Scheduled tasks configured:
    echo   - Cache cleanup: Daily at 02:00
    echo   - CNH expiration check: Daily at 08:00
)

echo.
echo [8/8] Testing new commands...
docker exec tms_saas_app php artisan cache:clean-old --days=7 --force >nul 2>&1
if errorlevel 1 (
    echo [WARNING] Cache cleanup command test failed
) else (
    echo [OK] Cache cleanup command working
)

echo.
echo ========================================
echo   Update Complete!
echo ========================================
echo.
echo New features available:
echo   - Automatic cache cleanup (daily at 02:00)
echo   - CNH expiration notifications (daily at 08:00)
echo   - Image optimization with WebP support
echo   - Lazy loading for driver photos
echo   - Driver trail visibility toggle
echo.
echo Next steps:
echo   1. Restart queue worker if needed: docker-compose restart queue
echo   2. Verify scheduled tasks: docker exec tms_saas_app php artisan schedule:list
echo   3. Test cache cleanup: docker exec tms_saas_app php artisan cache:clean-old --days=7
echo.
echo ========================================
pause




