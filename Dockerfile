# Build stage for frontend assets
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY vite.config.js ./
COPY postcss.config.js* tailwind.config.js* ./
RUN npm run build

# Production stage
FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    libicu-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_sqlite pdo_mysql mbstring exif pcntl bcmath gd zip intl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files and install dependencies
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy the rest of the application
COPY . .

# Copy built frontend assets from the frontend stage
COPY --from=frontend /app/public/build ./public/build

# Run post-install scripts
RUN composer dump-autoload --optimize

# Create SQLite database and storage directories
RUN mkdir -p database storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && touch database/database.sqlite \
    && chmod -R 775 storage bootstrap/cache database \
    && chown -R www-data:www-data storage bootstrap/cache database

# Copy .env.example as .env if .env doesn't exist, then generate key and cache config
RUN cp .env.example .env \
     && sed -i 's|APP_URL=http://localhost|APP_URL=https://finanflowunimagec.onrender.com|' .env \
    && php artisan key:generate --force \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan migrate --force

# Expose port (Render sets the PORT env variable)
EXPOSE 10000

# Start command - Render sets PORT env var
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}
