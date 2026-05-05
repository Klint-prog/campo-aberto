FROM php:8.3-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
    bash \
    git \
    icu-dev \
    libzip-dev \
    linux-headers \
    oniguruma-dev \
    postgresql-dev \
    unzip \
    zip \
    $PHPIZE_DEPS \
    && docker-php-ext-install \
        bcmath \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_pgsql \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN addgroup -g 1000 campo && adduser -D -G campo -u 1000 campo

USER campo

CMD ["php-fpm"]
