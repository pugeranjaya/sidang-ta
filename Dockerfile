FROM php:8.2-cli

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

# Clear bootstrap cache, regenerate autoload, and discover packages
RUN rm -f bootstrap/cache/*.php \
    && composer dump-autoload --optimize --no-dev \
    && php artisan package:discover --ansi

# Install Node dependencies and build assets (Vite/Tailwind)
RUN npm install && npm run build

# Set permissions for storage and cache folders
RUN chmod -R 777 storage bootstrap/cache

# Expose port
EXPOSE 8000

# Start: clear cache, run migrations, then serve
CMD rm -f bootstrap/cache/*.php && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
