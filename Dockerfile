FROM php:8.2-cli

# Install dependencies required by Laravel and Node.js
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    git \
    libonig-dev \
    libpng-dev \
    libxml2-dev \
    curl \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy all project files into the container
COPY . .

# Install PHP dependencies using Composer
RUN composer install --optimize-autoloader --no-dev

# Install Node dependencies and build assets (Tailwind/Vite)
RUN npm install
RUN npm run build

# Set permissions for storage and cache folders
RUN chmod -R 777 storage bootstrap/cache

# Expose the port (Render/Koyeb will inject the PORT environment variable)
EXPOSE 8000

# Start the application and run database migrations
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
