FROM dunglas/frankenphp:php8.4

RUN apt-get update && apt-get install -y \
    unzip \
    p7zip-full \
    git \
    && rm -rf /var/lib/apt/lists/*

RUN install-php-extensions \
    pdo_mysql \
    intl \
    zip \
    opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
