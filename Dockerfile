# SplashAudit Dockerfile
# Multi-stage build for production-ready container

FROM php:7.4-apache as base

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage

# Apache configuration
COPY docker/apache-config.conf /etc/apache2/sites-available/000-default.conf

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]


# Development stage
FROM base as development

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install development dependencies
RUN composer install --no-interaction --prefer-dist

# Enable Xdebug for development
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

ENV APP_ENV=development
ENV APP_DEBUG=true


# Production stage
FROM base as production

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install production dependencies only
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Remove unnecessary files
RUN rm -rf tests/ docker/ *.md

ENV APP_ENV=production
ENV APP_DEBUG=false

# Security: Run as non-root user
USER www-data
