FROM php:8.1-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Install Composer explicitly version 2
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install composer dependencies first
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy application source
COPY . .

# Ensure directories exist with correct case
RUN mkdir -p /var/www/html/App/Controllers \
    /var/www/html/App/Models \
    /var/www/html/App/Helpers \
    /var/www/html/App/Config

# Permissions (after COPY!)
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type f -exec chmod 644 {} \; \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && chmod -R 775 /var/www/html/public/assets/uploads \
    && chmod -R +x /var/www/html/App

# Apache Virtual Host configuration
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && echo '<VirtualHost *:80>\n\
    ServerName localhost\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
    Options -Indexes +FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
    </VirtualHost>' > /etc/apache2/sites-available/000-default.conf

EXPOSE 80

CMD ["apache2-foreground"]
