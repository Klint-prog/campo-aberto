FROM php:8.3-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
    bash \
    git \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    postgresql-dev \
    unzip \
    zip \
    linux-headers \
    $PHPIZE_DEPS \
    && docker-php-ext-install \
    intl \
    mbstring \
    opcache \
    pcntl \
    pdo_pgsql \
    zip \
    && pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

CMD ["php-fpm"]
