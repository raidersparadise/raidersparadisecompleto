@echo off
REM Instalacion rapida de Raiders Paradise en Windows (requiere PHP 8.2+ y Composer)
cd /d %~dp0
echo == Raiders Paradise: instalacion ==
if not exist vendor\autoload.php ( call composer install || goto error ) else ( echo Dependencias PHP ya incluidas. )
if not exist .env copy .env.example .env
findstr /b /c:"APP_KEY=base64" .env >nul || php artisan key:generate --ansi
if not exist database\database.sqlite type nul > database\database.sqlite
php artisan config:clear
REM db:wipe borra TODAS las tablas aunque no exista la tabla "migrations"
php artisan db:wipe --force || goto error
php artisan migrate --seed --force || goto error
echo.
echo Listo. Abre http://127.0.0.1:8000  (usuario admin@raidersparadise.co / Raiders2026*)
php artisan serve
goto :eof

:error
echo.
echo *** La instalacion se detuvo por un error. Revisa el mensaje de arriba. ***
exit /b 1
