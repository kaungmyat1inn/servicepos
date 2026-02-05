# Production Dockerfile
FROM php:8.2-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libzip-dev \
    unzip \
    icu-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libwebp-dev \
    oniguruma-dev \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    zip

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy project files first for better layer caching
COPY composer.json composer.lock* ./

# Install dependencies (production only)
RUN composer install --no-interaction --no-dev --optimize-autoloader --no-scripts

# Copy remaining application files
COPY . .

# Create storage directories
RUN mkdir -p storage/framework/{sessions,views,cache} \
    storage/logs \
    bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Expose port 3001
EXPOSE 3001

# Run as non-root user
RUN addgroup -g 1000 -S app && adduser -u 1000 -S app -G app
USER app

# Set default command
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

