#!/bin/sh
set -e
cd /app

[ -f .env ] || cp .env.example .env
sed -i 's/\r$//' .env
# artisan serve solo hereda variables del .env, así que guardamos ahí la ruta de la BD
sed -i '/^DB_DATABASE=/d' .env
echo "DB_DATABASE=$DB_DATABASE" >> .env
grep -q '^APP_KEY=.\+' .env || php artisan key:generate --force

# Primera vez: crea la BD, las tablas y los datos demo. Siguientes veces: solo migra.
if [ ! -f "$DB_DATABASE" ]; then
    touch "$DB_DATABASE"
    php artisan migrate --force --seed
else
    php artisan migrate --force
fi

echo ">> Listo: http://localhost:8000"
exec php artisan serve --host=0.0.0.0 --port=8000