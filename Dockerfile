FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring zip gd exif curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set Apache DocumentRoot to public/
RUN sed -ri -e 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's|/var/www/html|/var/www/html/public|g' /etc/apache2/apache2.conf

# Allow .htaccess overrides
RUN sed -ri 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first (for better caching)
COPY composer.json composer.lock* ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy application code
COPY . .

# Create storage directories and set permissions
RUN mkdir -p storage/uploads/thumbnails \
    && chown -R www-data:www-data storage/ \
    && chmod -R 775 storage/

# PHP configuration
RUN echo "upload_max_filesize=50M" > /usr/local/etc/php/conf.d/app.ini \
    && echo "post_max_size=50M" >> /usr/local/etc/php/conf.d/app.ini \
    && echo "memory_limit=256M" >> /usr/local/etc/php/conf.d/app.ini \
    && echo "max_execution_time=120" >> /usr/local/etc/php/conf.d/app.ini \
    && echo "log_errors=On" >> /usr/local/etc/php/conf.d/app.ini \
    && echo "error_log=/var/log/apache2/php_errors.log" >> /usr/local/etc/php/conf.d/app.ini

# Entrypoint script for runtime permission fix
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]
