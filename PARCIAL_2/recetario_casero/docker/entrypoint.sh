#!/bin/bash
# Se ejecuta cada vez que arranca el contenedor "app".
set -e
cd /var/www/html

# Laravel lee casi toda su configuracion de las variables de entorno del contenedor.
# El archivo .env solo se usa para guardar la APP_KEY (ver abajo).
[ -f .env ] || touch .env

# APP_KEY: si no se indica por variable de entorno, se genera UNA sola vez y se guarda
# en el volumen de storage, asi sobrevive aunque se recree el contenedor (las sesiones
# no se invalidan). Tambien se escribe en .env porque "docker compose exec" no hereda
# las variables que exporta este script.
if [ -z "${APP_KEY:-}" ]; then
    mkdir -p storage/app
    KEY_FILE=storage/app/.app_key
    if [ ! -s "$KEY_FILE" ]; then
        php artisan key:generate --show --no-interaction > "$KEY_FILE"
    fi
    APP_KEY="$(tr -d '\r\n' < "$KEY_FILE")"
    export APP_KEY
    sed -i '/^APP_KEY=/d' .env
    echo "APP_KEY=${APP_KEY}" >> .env
fi

mkdir -p storage/app storage/framework/cache/data storage/framework/sessions \
         storage/framework/views storage/logs bootstrap/cache

# Vistas compiladas viejas (el volumen sobrevive a reconstruir la imagen): se regeneran solas.
php artisan view:clear --no-interaction > /dev/null 2>&1 || true

# Espera a PostgreSQL y crea las tablas con las migraciones.
# La base de datos "recetario" ya la crea el contenedor db con POSTGRES_DB.
intentos=0
until php artisan migrate --force --no-interaction; do
    intentos=$((intentos + 1))
    if [ "$intentos" -ge 30 ]; then
        echo "!! No se pudo migrar la base de datos despues de $intentos intentos." >&2
        exit 1
    fi
    echo ".. PostgreSQL aun no responde; reintentando en 3 s ($intentos/30)..."
    sleep 3
done

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true

echo ">> Recetario casero listo en ${APP_URL:-http://localhost}"
exec docker-php-entrypoint "$@"