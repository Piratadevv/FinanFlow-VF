FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libsqlite3-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_sqlite mbstring exif pcntl bcmath \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Node.js 20
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy project files
COPY . .

# Create .env from example
RUN cp .env.example .env

# Install PHP dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Install Node dependencies and build assets
RUN npm install && npm run build

# Set permissions
RUN chmod -R 775 storage bootstrap/cache

# Create SQLite database file
RUN mkdir -p database && touch database/database.sqlite

# Generate app key, run migrations, create storage link
RUN php artisan key:generate \
    && php artisan migrate --force --seed \
    && php artisan storage:link || true

# Set correct permissions after setup
RUN chmod -R 775 storage bootstrap/cache database

EXPOSE 8000

CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
