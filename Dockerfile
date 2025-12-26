# Dockerfile for Render.com deployment
# Uses PHP + Apache, installs mongodb PHP extension
FROM php:8.2-apache

# Install system packages needed to build and enable mongodb extension
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

# Install the MongoDB PHP extension via PECL and enable it
RUN pecl channel-update pecl.php.net \
  && pecl install mongodb \
  && docker-php-ext-enable mongodb

# Enable Apache rewrite if you need it
RUN a2enmod rewrite

# Copy application files
COPY . /var/www/html/

# Ensure files are readable by Apache
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Install Composer (optional) and run install if composer.json exists
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
  && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
  && php -r "unlink('composer-setup.php');" \
  || true

WORKDIR /var/www/html

EXPOSE 80

# Render uses the Dockerfile CMD. Use Apache run in foreground.
CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]
