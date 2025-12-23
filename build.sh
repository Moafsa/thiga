#!/bin/bash
# Build script for Laravel application

echo "🚀 Starting build process..."

# Clear all caches
echo "📦 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Run migrations
echo "🗄️ Running migrations..."
php artisan migrate --force

# Optimize for production
echo "⚡ Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Create required directories
echo "📁 Creating storage directories..."
mkdir -p storage/app/public/cache/photos
mkdir -p storage/app/public/drivers
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Verify scheduled tasks
echo "⏰ Verifying scheduled tasks..."
php artisan schedule:list

echo "✅ Build completed successfully!"











