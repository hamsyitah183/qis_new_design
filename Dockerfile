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

# Ensure Laravel directories exist & are writable
RUN mkdir -p /var/www/html/storage/logs /var/run \
    && chown -R www-data:www-data /var/www/html /var/run \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copy Supervisor config
COPY ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose PHP-FPM port
EXPOSE 9000

# Start with entrypoint script
ENTRYPOINT ["docker-entrypoint.sh"]
