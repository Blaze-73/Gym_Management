# Use the official PHP image with Apache
FROM php:8.2-apache

# 1. Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    libpq-dev

# 2. Install PHP extensions (including pgsql for Supabase)
RUN docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd

# 3. Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Set working directory
WORKDIR /var/www/html

# 5. Copy project files into the container
COPY . .

# 6. Install PHP dependencies
# We use --no-dev to keep the image small and fast
RUN composer install --no-dev --optimize-autoloader

# 7. Set permissions for Laravel storage and cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 8. Configure Apache to point to the /public folder
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri-e 's!SubstituteAnyCwd /var/www/html!SubstituteAnyCwd /var/www/html/public!g' /etc/apache2/sites-available/000-default.conf && \
    sed -ri-e 's!DocumentRoot /var/www/html!DocumentRoot /var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# 9. Expose port 80
EXPOSE 80
