#!/bin/bash
set -e

# Wait for database to be ready
echo "Waiting for database..."
sleep 5

# Install composer dependencies if vendor doesn't exist
if [ ! -d "vendor" ]; then
    echo "Installing composer dependencies..."
    composer install --no-dev --optimize-autoloader --no-interaction
fi

# Install npm dependencies if node_modules doesn't exist
if [ ! -d "node_modules" ]; then
    echo "Installing npm dependencies..."
    npm install
fi

# Rebuild frontend assets with environment variables
echo "Building frontend assets..."
npm run build

# Clear Laravel caches
php artisan config:clear
php artisan cache:clear

# Run migrations
php artisan migrate --force

# Start supervisord
echo "Starting supervisor..."
exec /usr/bin/supervisord -n
