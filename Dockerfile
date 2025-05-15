FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && \
    apt-get install -y \
    libonig-dev \
    libpng-dev \
    libxml2-dev \
    unzip \
    zip \
    git \
    curl \
    libzip-dev && \
    docker-php-ext-configure zip && \
    docker-php-ext-install zip bcmath exif gd mbstring mysqli pcntl pdo pdo_mysql && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

# Set up composer and install dependencies
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy composer files and install dependencies
COPY composer.json ./
RUN composer install --no-scripts --no-autoloader

# Copy Apache configuration
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# Copy the application
COPY . /var/www/html
WORKDIR /var/www/html

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/app/students/template \
    && a2ensite 000-default.conf

# Generate optimized autoload files
RUN composer dump-autoload --optimize

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && find /var/www/html -type f -exec chmod 644 {} \;
