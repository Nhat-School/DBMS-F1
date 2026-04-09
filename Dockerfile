FROM php:8.2-apache

# Install MySQL extension for PHP
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy application code
COPY ./src /var/www/html/
