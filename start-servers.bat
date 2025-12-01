@echo off
echo ========================================
echo   TMS SaaS - Initialization Script
echo ========================================
echo.

REM Check if Docker is running
docker ps >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Docker is not running. Please start Docker Desktop and try again.
    pause
    exit /b 1
)

echo [1/9] Building Docker containers...
docker-compose build
if errorlevel 1 (
    echo [ERROR] Failed to build containers
    pause
    exit /b 1
)

echo.
echo [2/9] Starting database and services...
docker-compose up -d pgsql redis
if errorlevel 1 (
    echo [ERROR] Failed to start database services
    pause
    exit /b 1
)

echo.
echo [3/9] Waiting for database to be ready...
timeout /t 15 /nobreak > nul

REM Check database connection
docker exec tms_saas_pgsql pg_isready -U tms_user >nul 2>&1
if errorlevel 1 (
    echo [WARNING] Database might not be ready yet. Continuing anyway...
)

echo.
echo [4/9] Starting application services...
docker-compose up -d app nginx queue
if errorlevel 1 (
    echo [ERROR] Failed to start application services
    pause
    exit /b 1
)

echo.
echo [5/9] Waiting for application to be ready...
timeout /t 10 /nobreak > nul

echo.
echo [6/9] Installing MinIO package (AWS S3 Flysystem)...
docker exec tms_saas_app composer require league/flysystem-aws-s3-v3:^3.0 --no-interaction --quiet 2>nul
if errorlevel 1 (
    echo [WARNING] Failed to install MinIO package. The system will use database fallback for file storage.
) else (
    echo [OK] MinIO package installed successfully
)

echo.
echo [7/9] Running Laravel setup...
echo   - Generating application key...
docker exec tms_saas_app php artisan key:generate --force >nul 2>&1

echo   - Running database migrations...
docker exec tms_saas_app php artisan migrate --force
if errorlevel 1 (
    echo [ERROR] Failed to run migrations
    pause
    exit /b 1
)

echo   - Seeding database...
docker exec tms_saas_app php artisan db:seed --force >nul 2>&1

echo   - Clearing and caching configuration...
docker exec tms_saas_app php artisan config:clear >nul 2>&1
docker exec tms_saas_app php artisan cache:clear >nul 2>&1
docker exec tms_saas_app php artisan route:clear >nul 2>&1
docker exec tms_saas_app php artisan view:clear >nul 2>&1

echo.
echo [8/9] Starting WuzAPI for WhatsApp integration...
docker-compose up -d wuzapi
if errorlevel 1 (
    echo [WARNING] Failed to start WuzAPI. WhatsApp integration may not be available.
)

echo.
echo [9/9] Verifying all migrations are applied...
docker exec tms_saas_app php artisan migrate:status | findstr "Pending" >nul 2>&1
if errorlevel 1 (
    echo [OK] All migrations are applied
) else (
    echo [WARNING] Some migrations may be pending. Run 'docker exec tms_saas_app php artisan migrate' to apply them.
)

echo.
echo ========================================
echo   Initialization Complete!
echo ========================================
echo.
echo Services available:
echo   - Application: http://localhost:8080
echo   - WuzAPI: http://localhost:8081
echo   - Database: localhost:5432
echo   - Redis: localhost:6379
echo.
echo WhatsApp Integration:
echo   - Login: http://localhost:8081/login?token=tms_whatsapp_token_123
echo   - API Docs: http://localhost:8081/api
echo.
echo Useful commands:
echo   - View logs: docker-compose logs -f
echo   - Stop services: docker-compose down
echo   - Restart: docker-compose restart
echo   - Run migrations: docker exec tms_saas_app php artisan migrate
echo.
echo ========================================
pause








