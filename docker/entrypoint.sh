#!/bin/sh
# Espera a MySQL, migra con datos de prueba (solo la primera vez) y arranca Laravel
set -e
[ -f .env ] || cp .env.example .env
grep -q '^APP_KEY=base64' .env || php artisan key:generate --force
echo "Esperando la base de datos..."
until php -r "new PDO('mysql:host=${DB_HOST};port=${DB_PORT}', '${DB_USERNAME}', '${DB_PASSWORD}');" 2>/dev/null; do sleep 2; done
if ! php artisan migrate:status >/dev/null 2>&1 || php artisan migrate:status | grep -q "Pending"; then
  php artisan migrate:fresh --seed --force
fi
exec php artisan serve --host=0.0.0.0 --port=8000
