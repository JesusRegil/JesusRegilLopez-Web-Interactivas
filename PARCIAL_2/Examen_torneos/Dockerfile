# --- Etapa 1: dependencias de PHP (Composer) ---
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-progress --no-interaction
COPY . .
RUN composer dump-autoload --no-dev --optimize --no-scripts --no-interaction

# --- Etapa 2: la app (Laravel + SQLite en el mismo contenedor) ---
FROM php:8.2-cli
WORKDIR /app
COPY --from=vendor /app/ ./

# La base de datos SQLite vive en /data (volumen) para no perderla al reiniciar
ENV DB_DATABASE=/data/database.sqlite
RUN mkdir -p /data storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && sed -i 's/\r$//' docker-entrypoint.sh \
    && php artisan package:discover --ansi

VOLUME /data
EXPOSE 8000
ENTRYPOINT ["sh", "/app/docker-entrypoint.sh"]