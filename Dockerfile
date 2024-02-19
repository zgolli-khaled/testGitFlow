# Use an official PHP image as base
FROM php:8.3-fpm
# Set working directory
WORKDIR /var/www/html
# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pdo_mysql

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy composer files
COPY composer.json composer.lock ./

# Install application dependencies
RUN composer install --no-scripts --no-autoloader

# Copy the rest of the application
COPY . .

# Expose port 9000 to communicate with PHP-FPM
EXPOSE 9000

