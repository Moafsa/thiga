#!/bin/bash
# Build script for Laravel application

echo "🚀 Starting build process..."

# Clear all caches
echo "📦 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
echo "⚡ Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Run migrations (optional - uncomment if needed)
# echo "🗄️ Running migrations..."
# php artisan migrate --force

echo "✅ Build completed successfully!"


