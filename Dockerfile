FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    curl \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set the working directory
WORKDIR /var/www/html

# Copy Laravel project files
COPY . .

# Set correct permissions
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
RUN chown -R www-data:www-data /var/www/html

# Install Composer dependencies
RUN apt-get update && apt-get install -y wget
RUN wget -O composer-setup.php https://getcomposer.org/installer
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-dev --optimize-autoloader

# Set the correct document root
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf

# Expose port 80
EXPOSE 80

# Clear config & cache
RUN php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear && php artisan config:cache

# Start Apache
CMD ["apache2-foreground"]
