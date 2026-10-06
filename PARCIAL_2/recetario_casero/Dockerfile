# =====================================================================================
# Recetario casero - imagen de la aplicacion (Apache + PHP 8.4 + Laravel 13)
#
#   docker compose up -d --build
#
# TODO se construye aqui dentro, solo con lo que hay en git: no hace falta PHP, Composer,
# Node ni PostgreSQL en tu equipo.
#
# Etapas:  vendor -> vendor-dev -> assets -> base -> test | runtime
#   - runtime  (la ultima) es la imagen de la aplicacion; docker-compose.yml la pide con "target".
#   - test     es la misma imagen mas las dependencias de desarrollo y PHPUnit.
# =====================================================================================

# ---------- 1) Dependencias de PHP (sin las de desarrollo) ----------
# composer:2 trae PHP 8.5, pero el runtime es PHP 8.4: composer.json fija "platform.php"
# en 8.4.0 para que composer.lock se resuelva (y se instale) como para PHP 8.4.
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-progress --no-interaction
COPY . .
RUN composer dump-autoload --no-dev --optimize --no-scripts --no-interaction

# ---------- 2) Lo mismo, mas las dependencias de desarrollo (solo para las pruebas) ----------
FROM vendor AS vendor-dev
RUN composer install --no-scripts --no-autoloader --prefer-dist --no-progress --no-interaction \
    && composer dump-autoload --optimize --no-scripts --no-interaction

# ---------- 3) Estilos y JavaScript (Tailwind + Vite) ----------
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json .npmrc ./
RUN npm ci --no-audit --no-fund
COPY vite.config.js ./
COPY resources ./resources
RUN npm run build

# ---------- 4) Base comun: PHP 8.4 + Apache + extensiones ----------
FROM php:8.4-apache AS base
RUN apt-get update \
    && apt-get install -y --no-install-recommends curl libpq-dev \
    && docker-php-ext-install -j"$(nproc)" opcache pdo_pgsql \
    && a2enmod rewrite \
    && echo 'ServerName localhost' > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername \
    && apt-get clean && rm -rf /var/lib/apt/lists/*
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php/recetario.ini /usr/local/etc/php/conf.d/recetario.ini
WORKDIR /var/www/html

# ---------- 5) Pruebas: docker compose --profile test run --rm --build test ----------
FROM base AS test
COPY --from=vendor-dev /app/ ./
# El .env vacio evita que "php artisan test" muestre como aviso la lectura (silenciada) de un .env inexistente.
RUN touch .env \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views \
                storage/logs bootstrap/cache \
    && php artisan package:discover --ansi
ENTRYPOINT []
CMD ["php", "artisan", "test"]

# ---------- 6) Imagen final de la aplicacion (debe ser la ultima etapa) ----------
FROM base AS runtime

LABEL org.opencontainers.image.title="Recetario casero" \
      org.opencontainers.image.description="Cada usuario guarda sus propias recetas de cocina (Laravel 13 + PostgreSQL)" \
      org.opencontainers.image.licenses="MIT"

COPY docker/entrypoint.sh /usr/local/bin/recetario-entrypoint
COPY --from=vendor /app/ ./
COPY --from=assets /app/public/build ./public/build

RUN sed -i 's/\r$//' /usr/local/bin/recetario-entrypoint \
    && chmod +x /usr/local/bin/recetario-entrypoint \
    && mkdir -p storage/app storage/framework/cache/data storage/framework/sessions storage/framework/views \
                storage/logs bootstrap/cache \
    && php artisan package:discover --ansi \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

EXPOSE 80
HEALTHCHECK --interval=10s --timeout=5s --start-period=120s --retries=5 \
    CMD curl -fsS http://localhost/up > /dev/null || exit 1

ENTRYPOINT ["recetario-entrypoint"]
CMD ["apache2-foreground"]