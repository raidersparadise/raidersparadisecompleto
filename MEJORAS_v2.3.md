# Mejoras v2.3 — Imágenes, etiquetas, alertas de stock y SMTP

Fecha: 2026-11-07. Pruebas: `php artisan test` → **37 pruebas aprobadas** (31 anteriores + 6 nuevas en `tests/Feature/MejorasTest.php`).

## Para activarlas en tu equipo

```bash
php artisan migrate          # crea 19_metadatos_imagen, 20_etiquetas y 21_notificaciones
php artisan config:clear
```

La base `database/database.sqlite` incluida en este .zip **ya está migrada**. Si usas MySQL (`database/sql/*.sql`), ejecuta `php artisan migrate`.

En `php.ini` (XAMPP) deben estar activas `extension=gd`, `extension=exif` y `extension=openssl`. Además, `upload_max_filesize = 10M` y `post_max_size = 40M`.

---

## 1. Compresión de imágenes + metadatos

| Archivo | Cambio |
|---|---|
| `app/Support/ImagenOptimizada.php` | **Nuevo.** Lee EXIF (cámara, fecha, GPS), endereza la foto del celular, la reduce a 1200 px de ancho como máximo (nunca agranda) y la guarda en **WebP calidad 80** sin EXIF ni GPS. |
| `Admin/ProductoController.php` | La foto principal, la galería y los cuadros 360° pasan por `ImagenOptimizada`. El límite de subida pasa de 2 MB a 8 MB porque ahora se comprime. |
| `19_metadatos_imagen.php` + `ProductoImagen` | Columnas `ancho`, `alto`, `peso_bytes`, `camara` y `fecha_toma` en `producto_imagen`. |

- **Funciona sin instalar nada:** usa la extensión GD de PHP.
- Si instalas **Intervention Image** (`composer require intervention/image-laravel:^1.5`), se usa automáticamente.

Resultado de prueba con una foto de celular de 3000×2000 con GPS: pasó de 95 KB a 4 KB, quedó en 1200×1800 (enderezada) y sin EXIF.

## 2. Etiquetas en el buscador

| Archivo | Cambio |
|---|---|
| `20_etiquetas.php` | Tablas `etiqueta` (`slug` único) y `producto_etiqueta`. |
| `app/Models/Etiqueta.php` | **Nuevo.** `Etiqueta::idsDesdeTexto('cuero, touring')` crea las que falten (máximo 10, sin repetir). |
| `Producto::etiquetas()` | Relación muchos a muchos. |
| `admin/productos/form.blade.php` | Campo "Etiquetas (separadas por coma)". |
| `TiendaController@catalogo` | La búsqueda `q` también encuentra por etiqueta. Hay un nuevo filtro `?etiqueta=slug`. |
| `tienda/producto.blade.php` | Muestra las etiquetas (`#cuero`) como enlaces al catálogo filtrado. |

Se hizo con tablas propias en lugar de `spatie/laravel-tags`, porque ese paquete requiere MySQL y el proyecto usa SQLite.

## 3. Alertas de stock bajo

| Archivo | Cambio |
|---|---|
| `app/Notifications/StockBajo.php` | **Nuevo.** Envía un correo con la lista de productos y lo guarda en la tabla `notifications`. |
| `app/Support/AlertaStock.php` | **Nuevo.** Avisa **solo cuando un producto pasa de "ok" a "bajo" o "agotado"**, así no llegan correos repetidos. Si el correo falla, lo registra en el log sin interrumpir la venta. |
| `Producto::sincronizarStock()`, `InventarioController`, `PedidoService` | Disparan la alerta al mover el inventario desde el panel y al **confirmar una compra**. La alerta sale después de confirmar la transacción. |
| `routes/console.php` | Comando `php artisan inventario:alertar`, programado todos los días a las 7:00 a. m. (hora de Bogotá). |
| `Usuario::administradores()` | Destinatarios: administradores activos. |
| `layouts/admin.blade.php` + `raiders.css` | Campanita 🔔 con el contador de alertas sin leer. Se marcan como leídas al abrir Inventario. |

Para que el resumen diario corra solo:

- **En local:** `php artisan schedule:work`
- **En Linux:** cron `* * * * * cd /ruta && php artisan schedule:run >> /dev/null 2>&1`
- **En Windows:** Programador de tareas ejecutando `php artisan schedule:run` cada minuto.

## 4. SMTP

Laravel ya trae Symfony Mailer, así que no hay que instalar nada. En `.env` y `.env.example` quedó comentado el bloque de Gmail. Para activarlo:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tucorreo@gmail.com
MAIL_PASSWORD=contrasena_de_aplicacion   # https://myaccount.google.com/apppasswords
MAIL_FROM_ADDRESS="tucorreo@gmail.com"
```

Para probar:

```bash
php artisan config:clear
php artisan inventario:alertar
```

Mientras siga `MAIL_MAILER=log`, los correos quedan escritos en `storage/logs/laravel.log`.

## 5. Contraseñas

No requirieron cambios: el modelo `Usuario` ya usa el cast `'password' => 'hashed'` (bcrypt). Opcionalmente puedes poner `HASH_DRIVER=argon2id` en `.env`.
