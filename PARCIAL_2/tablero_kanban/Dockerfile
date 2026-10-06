ARG PHP_IMAGE=php:8.2-cli

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-progress --no-interaction
COPY . .
RUN composer dump-autoload --no-dev --optimize --no-scripts --no-interaction

FROM ${PHP_IMAGE}

# Solo instala pdo_pgsql si la imagen base no lo trae
RUN php -m | grep -qix pdo_pgsql || ( \
      apt-get update \
      && apt-get install -y --no-install-recommends libpq-dev \
      && docker-php-ext-install pdo_pgsql \
      && rm -rf /var/lib/apt/lists/* )

WORKDIR /app
COPY --from=vendor /app/ ./

RUN sed -i 's/\r$//' docker-entrypoint.sh \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && php artisan package:discover --ansi

EXPOSE 8000
HEALTHCHECK NONE
ENTRYPOINT ["sh", "/app/docker-entrypoint.sh"]
