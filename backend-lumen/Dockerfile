# Lumen (PHP 8.2 + Apache)
FROM php:8.2-apache

# Install system deps and PHP extensions
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libpng-dev libonig-dev libicu-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip intl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite

# Set working dir
WORKDIR /var/www/html

# Copy composer from official image to install dependencies without separate stage
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy app files
COPY . /var/www/html

# Set correct Apache DocumentRoot to public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Install PHP dependencies (no dev for prod)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Ensure writable directories
RUN mkdir -p bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Expose port
EXPOSE 80

# Apache listens on 80; use 8000 externally via compose port mapping
CMD ["apache2-foreground"]
