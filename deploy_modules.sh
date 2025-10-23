#!/bin/bash

echo "🚀 Deploying new modules..."

# Pull latest code
echo "📥 Pulling latest code..."
git pull origin master

# Update dependencies
echo "📦 Installing dependencies..."
composer install --no-dev --optimize-autoloader

# Run migrations
echo "🗄️ Running migrations..."
php artisan migrate

# Run new module seeders
echo "🌱 Running seeders..."
php artisan db:seed --class="Modules\FileSharing\Database\Seeders\FileSharingPermissionSeeder"

# Clear all caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Enable new modules
echo "🔧 Enabling modules..."
php artisan module:enable FileSharing

# Check modules status
echo "📋 Checking modules..."
php artisan module:list

# Restart services
echo "🔄 Restarting services..."
sudo systemctl restart nginx
sudo systemctl restart php8.1-fpm

echo "✅ Deployment completed!"
