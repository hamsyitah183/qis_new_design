#!/bin/bash
set -e

# Wait for database to be ready
echo "Waiting for database..."
sleep 5

# Remove old build cache and rebuild frontend assets with environment variables
echo "Cleaning old builds..."
rm -rf public/build public/build2 public/hot

echo "Building frontend assets..."
npm run build

# Create storage link if it doesn't exist
if [ ! -L "public/storage" ]; then
    echo "Creating storage link..."
    php artisan storage:link
fi

# Run migrations BEFORE clearing cache (cache table needs to exist)
echo "Running migrations..."
php artisan migrate --force

# Clear Laravel caches AFTER migrations
echo "Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Start supervisord
echo "Starting supervisor..."
exec /usr/bin/supervisord -n
