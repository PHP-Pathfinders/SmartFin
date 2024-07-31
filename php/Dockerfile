FROM php:8.3-fpm

# Install dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    procps \
    net-tools \
    zlib1g-dev \
    g++ \
    git \
    libicu-dev \
    zip \
    libzip-dev \
    libpng-dev \
    librabbitmq-dev \
    supervisor \
    && docker-php-ext-install \
    intl \
    opcache \
    pdo \
    pdo_mysql \
    zip \
    gd \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && pecl install amqp \
    && docker-php-ext-enable amqp

WORKDIR /var/www/project

# Copy Supervisor configuration files
COPY php/supervisor/ /etc/supervisor/conf.d/


RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN curl -sS https://get.symfony.com/cli/installer | bash

# Ensure supervisor directories exist
RUN mkdir -p /var/log/supervisor /var/run/supervisor

# Command to run Supervisor
CMD ["/usr/bin/supervisord","-n", "-c", "/etc/supervisor/supervisord.conf"]