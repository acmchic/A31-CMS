#!/bin/bash

echo "🚀 Deploying FileSharing module to server..."

# 1. Pull latest code
echo "📥 Pulling latest code from Git..."
git pull origin master

# 2. Update dependencies
echo "📦 Installing/updating dependencies..."
composer install --no-dev --optimize-autoloader

# 3. Clear all caches
echo "🧹 Clearing all caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# 4. Run migrations (only new ones)
echo "🗄️ Running new migrations..."
php artisan migrate --path=Modules/FileSharing/database/migrations

# 5. Run seeders for new module
echo "🌱 Running FileSharing permission seeder..."
php artisan db:seed --class="Modules\FileSharing\Database\Seeders\FileSharingPermissionSeeder"

# 6. Enable new module
echo "🔧 Enabling FileSharing module..."
php artisan module:enable FileSharing

# 7. Check modules status
echo "📋 Checking modules status..."
php artisan module:list

# 8. Set proper permissions
echo "🔐 Setting file permissions..."
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/

# 9. Restart services
echo "🔄 Restarting web services..."
sudo systemctl restart nginx
sudo systemctl restart php8.1-fpm

# 10. Test routes
echo "🧪 Testing routes..."
php artisan route:list | grep file-sharing

echo "✅ Deployment completed successfully!"
echo "📝 FileSharing module is now available at: /file-sharing"
echo "🎯 Dashboard card should be visible for users with 'file_sharing.view' permission"
