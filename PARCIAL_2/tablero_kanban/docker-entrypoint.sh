#!/bin/sh
set -e
cd /app

[ -f .env ] || cp .env.example .env
grep -q '^APP_KEY=.\+' .env || php artisan key:generate --force

until php artisan migrate --force; do
    echo "Esperando a PostgreSQL..."
    sleep 2
done

echo ">> Listo: http://localhost:8000"
exec php artisan serve --host=0.0.0.0 --port=8000
