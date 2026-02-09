FROM php:8.4-fpm

# Arguments
ARG user=www
ARG uid=1000

USER root

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
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libwebp-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Set working directory
WORKDIR /var/www

# Copy existing application directory
COPY . /var/www

# Install composer dependencies (as root, then chown)
RUN composer install --no-dev --optimize-autoloader --no-interaction && \
    chown -R $user:$user /var/www

RUN mkdir -p /var/www/storage/logs /var/www/bootstrap/cache \
&& chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
&& chmod -R 775 /var/www/storage /var/www/bootstrap/cache

USER $user

# Expose port 9000
EXPOSE 9000

CMD ["php-fpm"]
