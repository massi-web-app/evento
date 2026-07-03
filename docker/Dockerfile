FROM php:8.3-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    curl \
    zip \
    unzip \
    git \
    supervisor \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd xml zip opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*


# Copy custom php.ini settings
#COPY ./php.ini /usr/local/etc/php/

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /opt/backend

#Copy project files
COPY ./ ./

# Supervisor
# COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf


# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader


# Set permissions for php
RUN chown -R www-data:www-data /opt/backend \
    && find /opt/backend/storage /opt/backend/bootstrap/cache -type d -exec chmod 775 {} \; \
    && find /opt/backend/storage /opt/backend/bootstrap/cache -type f -exec chmod 664 {} \;


# Expose port 9000 and start PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]
