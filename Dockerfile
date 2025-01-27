FROM php:8.3.10

RUN apt-get update -y && apt-get install -y \
    openssl \
    zip \
    unzip \
    git \
    libpq-dev

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN docker-php-ext-install pdo pdo_pgsql

RUN php -m | grep mbstring


WORKDIR /app

COPY . /app

RUN composer install --no-dev --optimize-autoloader

RUN php artisan key:generate

EXPOSE 8000

CMD php artisan serve --host=0.0.0.0 --port=8000