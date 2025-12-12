@echo off
REM Build script for Laravel application (Windows)

echo 🚀 Starting build process...

REM Clear all caches
echo 📦 Clearing caches...
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

REM Optimize for production
echo ⚡ Optimizing for production...
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

REM Run migrations (optional - uncomment if needed)
REM echo 🗄️ Running migrations...
REM php artisan migrate --force

echo ✅ Build completed successfully!
pause


