FROM php:8.1-apache

RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libxml2-dev \
    libicu-dev \
    libzip-dev \
    unzip \
    wget \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mysqli xml intl zip

RUN wget https://downloads.joomla.org/cms/joomla5/5-1-0/Joomla_5-1-0-Stable-Full_Package.zip \
    && unzip Joomla_5-1-0-Stable-Full_Package.zip -d /var/www/html \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && rm Joomla_5-1-0-Stable-Full_Package.zip

# Expose de HTTP-poort
EXPOSE 80
