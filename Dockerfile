FROM php:8.2.0-apache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install required Linux libraries
RUN apt-get update -y && apt-get install -y \
    libicu-dev \
    libmariadb-dev \
    unzip zip \
    zlib1g-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libzip-dev \
    mariadb-client

# Install PHP extensions
RUN docker-php-ext-install gettext intl pdo_mysql gd pcntl zip

# Configure GD extension with FreeType and JPEG support
RUN docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Set Apache document root to /var/www/html/public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copy Composer and NodeJS from their official images
COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer
COPY --from=node:23.10.0 /usr/local /usr/local

# Copy project files
COPY . /var/www/html

# Set working directory
WORKDIR /var/www/html

# Set permissions for Laravel (PRODUCTION ADDITION)
RUN chown -R www-data:www-data /var/www/html/storage 
RUN chown -R www-data:www-data /var/www/html/bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/public
RUN chmod +x /var/www/html/*.sh && \
    chown www-data:www-data /var/www/html/*.sh