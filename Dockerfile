FROM php:8.4-cli

# Install system dependencies & Node.js
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    libzip-dev \
    zip \
    unzip \
    libpq-dev \
    curl \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get install -y nodejs \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd intl zip

WORKDIR /var/www/html

# Copy Composer from official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy project files into container
COPY . .

# Install PHP packages
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Install Node packages & compile Filament/Vite assets
RUN npm install
RUN npm run build

# Optional: Run filament upgrades
RUN php artisan filament:upgrade

# Define port dynamically for Railway (Railway supplies PORT automatically)
ENV PORT="8000"
EXPOSE 8000

# Gunakan PHP Artisan Server (paling bebas konflik Apache)
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT}
