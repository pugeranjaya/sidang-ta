FROM php:8.2-cli

# Allow composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER=1

# Install system dependencies (termasuk libicu-dev untuk intl)
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    git \
    libonig-dev \
    libpng-dev \
    libxml2-dev \
    libicu-dev \
    curl \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files first (better Docker caching)
COPY composer.json composer.lock ./

# Install PHP dependencies without running scripts (project files not copied yet)
RUN composer install --optimize-autoloader --no-dev --no-scripts

# Copy all project files
COPY . .

# Pastikan bootstrap/cache ada dan writable, lalu discover packages
RUN mkdir -p bootstrap/cache \
    && chmod -R 777 bootstrap/cache \
    && rm -f bootstrap/cache/*.php \
    && composer dump-autoload --optimize --no-dev --no-scripts \
    && php artisan package:discover --ansi

# Install Node dependencies and build assets (Vite/Tailwind)
RUN npm install && npm run build

# Set permissions for storage and cache folders
RUN chmod -R 777 storage bootstrap/cache

# Copy and prepare startup script
RUN chmod +x /app/start.sh && sed -i 's/\r$//' /app/start.sh

# Expose port
EXPOSE 8000

CMD ["/app/start.sh"]
