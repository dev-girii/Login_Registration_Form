# Dockerfile for Render / Railway deployment
# PHP + Apache + MongoDB + MySQL PDO

FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    zlib1g-dev \
    libicu-dev \
    libssl-dev \
    build-essential \
    pkg-config \
    ca-certificates \
  && rm -rf /var/lib/apt/lists/*

# Install PHP extensions: PDO + MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Install MongoDB PHP extension
RUN pecl channel-update pecl.php.net \
  && pecl install mongodb \
  && docker-php-ext-enable mongodb

# Enable Apache rewrite module
RUN a2enmod rewrite

# Copy application files
COPY . /var/www/html/

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html \
  && chmod -R 755 /var/www/html

# Install Composer (optional)
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
  && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
  && php -r "unlink('composer-setup.php');" \
  || true

WORKDIR /var/www/html

EXPOSE 80

# Start Apache
CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]
