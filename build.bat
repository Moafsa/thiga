@echo off
REM Build script for Laravel application (Windows)

echo 🚀 Starting build process...

REM Clear all caches
echo 📦 Clearing caches...
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

REM Run migrations
echo 🗄️ Running migrations...
php artisan migrate --force

REM Optimize for production
echo ⚡ Optimizing for production...
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

REM Create required directories
echo 📁 Creating storage directories...
if not exist "storage\app\public\cache\photos" mkdir "storage\app\public\cache\photos"
if not exist "storage\app\public\drivers" mkdir "storage\app\public\drivers"

REM Verify scheduled tasks
echo ⏰ Verifying scheduled tasks...
php artisan schedule:list

echo ✅ Build completed successfully!
pause











