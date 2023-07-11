# PHP Dependencies
FROM composer:latest as vendor
RUN apk add --no-cache freetype libpng libjpeg-turbo freetype-dev libpng-dev libjpeg-turbo-dev && \
  docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
  NPROC=$(grep -c ^processor /proc/cpuinfo 2>/dev/null || 1) && \
  docker-php-ext-install -j$(nproc) gd pdo_mysql && \
  apk del --no-cache freetype-dev libpng-dev libjpeg-turbo-dev
COPY . /app
RUN composer install \
    --no-scripts
RUN composer dump-autoload

# Frontend
FROM node:latest  as frontend
COPY . /app
WORKDIR /app
RUN npm install && npm run build && npm install shiki

# Application
FROM php:8.2-apache
ENV port 8000
ENV uid 1000
ENV user oneup
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
# Install system dependencies
RUN apt-get update && apt-get upgrade -y && apt-get install  \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip  \
    gnupg \
    libnode108 \
    sudo \
    wget -yyq

# download helper script
# @see https://github.com/mlocati/docker-php-extension-installer/
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
# install extensions
RUN docker-php-ext-configure pcntl --enable-pcntl
RUN chmod uga+x /usr/local/bin/install-php-extensions && sync && install-php-extensions \
    opcache \
    pdo_mysql \
    redis \
    gd \
    pcntl \
    intl \
;

ADD https://cacerts.digicert.com/DigiCertGlobalRootG2.crt.pem /var/www/html/ssl/
ADD https://cacerts.digicert.com/BaltimoreCyberTrustRoot.crt /var/www/html/ssl/

RUN echo 'memory_limit=2560M' >> /usr/local/etc/php/conf.d/php.ini
RUN echo 'upload_max_filesize=100M' >> /usr/local/etc/php/conf.d/php.ini
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN useradd -o -u ${uid} -G www-data,root -m -s /bin/bash ${user}
RUN sed -s -i -e "s/80/${port}/" /etc/apache2/ports.conf /etc/apache2/sites-available/*.conf
RUN a2enmod rewrite

COPY --chown=www-data:www-data . /var/www/html
COPY --chown=www-data:www-data --from=vendor /app/vendor/ /var/www/html/vendor/
COPY --chown=www-data:www-data --from=frontend /app/public/build/ /var/www/html/public/build/
COPY --chown=www-data:www-data --from=frontend /app/public/mix-manifest.json /var/www/html/public/mix-manifest.json

RUN chown www-data:www-data -R /var/www/html/ssl
RUN chmod 770 -R /var/www/html/ssl
RUN chmod 770 -R /var/www/html/storage
RUN chmod 770 -R /var/www/html/bootstrap

RUN echo "Cmnd_Alias UPDATER_ONLY = /usr/local/bin/updater.sh" > /etc/sudoers.d/update
RUN echo "%www-data ALL=(ALL) NOPASSWD:UPDATER_ONLY"  >> /etc/sudoers.d/update
RUN echo '#!/bin/bash' >> /usr/local/bin/updater.sh
RUN echo 'sudo apt-get update && sudo apt-get upgrade -y' >> /usr/local/bin/updater.sh
RUN chmod 0755 /usr/local/bin/updater.sh

RUN apt update && apt upgrade -y
USER $user
