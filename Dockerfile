FROM php:8.4-fpm

WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
        libzip-dev \
        unzip \
        curl \
        gnupg \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        supervisor \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo_mysql pcntl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer from the official Composer image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy the application code first
COPY . .

# Copy Docker-specific .env (needed for artisan commands)
COPY .env.docker .env

# Install PHP dependencies (now app files are available for autoloading)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy dependency files and install Node dependencies
COPY package.json package-lock.json ./
RUN npm install

# Ensure Laravel directories exist & are writable
RUN mkdir -p /var/www/html/storage/logs /var/run \
    && chown -R www-data:www-data /var/www/html /var/run \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Run storage link (now .env is available)
RUN php artisan storage:link

# Build frontend assets inside the container
RUN npm run build

# Copy Supervisor config
COPY ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Expose PHP-FPM port
EXPOSE 9000

# Start supervisord to run Reverb and Queue workers
CMD ["/usr/bin/supervisord", "-n"]
