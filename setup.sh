#!/usr/bin/env bash
# Instalación rápida de Raiders Paradise en macOS / Linux (requiere PHP 8.2+ y Composer)
set -e
cd "$(dirname "$0")"
echo "== Raiders Paradise: instalación =="
[ -f vendor/autoload.php ] || composer install
[ -f .env ] || cp .env.example .env
grep -q "^APP_KEY=base64" .env || php artisan key:generate --ansi
[ -f database/database.sqlite ] || touch database/database.sqlite
php artisan config:clear
php artisan db:wipe --force   # borra todas las tablas aunque no exista "migrations"
php artisan migrate --seed --force
echo
echo "Listo. Abre http://127.0.0.1:8000  (usuario admin@raidersparadise.co / Raiders2026*)"
php artisan serve
