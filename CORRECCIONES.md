# Errores encontrados y correcciones

Diagnóstico del repositorio recibido (último commit `868f024 Merge pull request #16 … feat/modelo_julian`).

## Estado inicial

El proyecto era un esqueleto de Laravel 12 con 15 migraciones y un solo modelo (`Categoria`). No había controladores, rutas de negocio, autenticación, vistas (solo la página de bienvenida de Laravel) ni datos de prueba. Por eso no existía un "frontend" y un "backend" que unir: se construyeron ambos sobre el modelo de datos del equipo, conservando nombres de tablas y columnas.

## Errores que impedían o afectaban el funcionamiento

| # | Archivo | Problema | Corrección |
|---|---|---|---|
| 1 | `04_cliente.php`, `07_marca.php` | `->nullabel()` (error de escritura). Laravel no falla, pero **ignora** el método: las columnas quedaban `NOT NULL`. | `->nullable()` |
| 2 | `database/migrations` | Se borraron las migraciones base de Laravel (`sessions`, `cache`, `jobs`). Con `SESSION_DRIVER=database` y `CACHE_STORE=database` del `.env`, **cualquier página fallaba** con "table sessions not found". | Nueva migración `00_00_laravel_base.php` |
| 3 | `app/Models/User.php` + `config/auth.php` | La autenticación apuntaba a la tabla `users`, que no existe (la tabla del equipo es `usuario`). El login nunca podía funcionar. | Modelo `Usuario` (tabla `usuario`, PK `id_usuario`) configurado en `auth.php`; se eliminó `User` y su factory |
| 4 | `DatabaseSeeder.php` | Creaba un `User` con columna `name`, inexistente: `db:seed` fallaba. | Seeders nuevos (roles, 5 usuarios, catálogo, ventas demo) |
| 5 | `Categoria.php` | Relación con `Producto::class`, clase que no existía. | Creados los 15 modelos con sus relaciones |
| 6 | `08_proveedor.php` | `telefono_proveedor` como `integer`: un celular de 10 dígitos (3001234567) **desborda** el INT de MySQL. | `string(20)` |
| 7 | `02_usuario.php` | Sin `remember_token` ni `timestamps`; `password` de 60 caracteres. | Agregados; `password` de 255; campo `activo` |
| 8 | `14_detalle_pedido.php` | FK a producto con `onDelete('cascade')`: borrar un producto **borraba el historial de ventas**. | `restrictOnDelete()`; el panel inactiva en lugar de borrar |
| 9 | `13_pedido.php` | Enum mezclaba mayúsculas (`"Entregado"`) con minúsculas (`"en_curso"`); sin estado inicial. | `pendiente, programado, en_curso, entregado, cancelado` |
| 10 | `12_inventario.php` | `;` suelto y sin restricción: un producto podía tener varios registros de inventario. | `id_producto` único |
| 11 | `04_cliente.php` | El cliente no estaba relacionado con su cuenta de acceso (`usuario`). | `id_usuario` opcional y único |
| 12 | `05_pqr.php` | `id_usuario` obligatorio: el cliente no podía radicar una PQR sin que ya hubiera un empleado asignado. | Opcional (responsable asignado después); `tipo`, `asunto`, `respuesta`, `fecha_cierre` |
| 13 | `15_factura.php` | Sin número de factura ni subtotal; PK `id` inconsistente con el resto. | `id_factura`, `numero_factura`, `subtotal`; una factura por pedido |
| 14 | `09_producto.php` | `nombre_producto` de 40 caracteres (no alcanza para «Alpinestars T-GP Plus R v4 Air»). | 120 caracteres; `sku` único; `destacado` |
| 15 | `10_carrito.php`, `11_detalle_carrito.php` | Un cliente podía tener varios carritos y el mismo producto repetido. | Restricciones únicas |
| 16 | `docker-compose.yml` vs `.env.example` | Docker levantaba MySQL pero la app usaba SQLite; no migraba. | Variables de entorno en el servicio `app` y `docker/entrypoint.sh` que espera a MySQL y siembra datos |
| 17 | `.env.example` | Idioma inglés y zona horaria UTC (las fechas de pedidos salían 5 horas adelantadas). | `es`, `es_CO`, `America/Bogota`; mensajes de validación en español (`lang/es`) |

## Mejoras de seguridad incluidas

- Contraseñas con hash (bcrypt), regeneración de sesión al ingresar y protección CSRF en todos los formularios.
- Límite de intentos en login, registro y PQR (`throttle`).
- Autorización por rol (`rol:` middleware) y verificación de propiedad: un cliente no puede ver pedidos, facturas ni ítems del carrito de otro.
- Compra dentro de una **transacción** con bloqueo de inventario (`lockForUpdate`) para evitar vender más unidades de las disponibles; el precio se toma del producto, no del formulario.
- Un administrador no puede desactivarse ni cambiarse el rol a sí mismo; los usuarios se desactivan en vez de borrarse (trazabilidad).

## Limitaciones conocidas

- El pago es **simulado** (no hay pasarela real); la factura es de prueba y no se reporta a la DIAN.
- No hay recuperación de contraseña por correo (`MAIL_MAILER=log`).
- Las pruebas automáticas se ejecutaron con SQLite. Las consultas de reportes usan SQL estándar compatible con MySQL 8 (`ONLY_FULL_GROUP_BY`), pero conviene ejecutar `php artisan test` y revisar los reportes una vez sobre la base MySQL del equipo.
