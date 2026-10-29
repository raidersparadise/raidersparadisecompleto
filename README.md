# Raiders Paradise

Tienda en línea de **accesorios premium para motociclistas** (cascos, guantes, chaquetas, impermeables, botas y accesorios) con panel administrativo, control de inventario, pedidos, facturación, PQR y **reportes gerenciales** para el administrador.

Proyecto formativo del programa **Análisis y Desarrollo de Software (ADSO) del SENA**. Hecho con **Laravel 12 (PHP 8.2+)** y vistas **Blade**. El frontend **no necesita Node ni compilación**: los estilos (`public/css/raiders.css`) y el JavaScript (`public/js/raiders.js`, que también dibuja las gráficas) se sirven directamente.

Colores de marca: `#363088` índigo, `#E84615` brasa, `#1E2247` noche y `#391C1D` cuero.

---

## Contenido

1. [Requisitos](#1-requisitos)
2. [Puesta en marcha rápida](#2-puesta-en-marcha-rápida-sqlite-sin-instalar-base-de-datos)
3. [Usuarios de prueba](#3-usuarios-de-prueba)
4. [Base de datos: SQLite y MySQL](#4-base-de-datos-sqlite-y-mysql)
5. [Qué incluye](#5-qué-incluye)
6. [Guía de pruebas manuales](#6-guía-de-pruebas-manuales)
7. [Pruebas automáticas](#7-pruebas-automáticas)
8. [Dónde está el frontend y dónde el backend](#8-dónde-está-el-frontend-y-dónde-el-backend)
9. [Documentación del código fuente](#9-documentación-del-código-fuente)
10. [Problemas comunes](#10-problemas-comunes)
11. [Flujo de trabajo con Git](#11-flujo-de-trabajo-con-git)
12. [Documentación del proyecto](#12-documentación-del-proyecto)

---

## 1. Requisitos

| Herramienta | Versión | Comprobar |
|---|---|---|
| PHP | 8.2 o superior (con `pdo_sqlite` o `pdo_mysql`, `mbstring`, `fileinfo`) | `php -v` |
| Composer | 2.x | `composer -V` |
| (Opcional) MySQL o MariaDB | MySQL 8+ / MariaDB 10.4+ (XAMPP, Laragon o Docker) | |
| (Opcional) VS Code | Cualquier versión reciente | |

> En Windows lo más fácil es **Laragon** (versión *Full*) o **XAMPP**, que ya traen PHP. Laragon también trae Composer; con XAMPP se instala aparte desde getcomposer.org.

## 2. Puesta en marcha rápida (SQLite, sin instalar base de datos)

1. Abre la carpeta `raidersparadise` en **VS Code** (`Archivo > Abrir carpeta`). Acepta instalar las extensiones recomendadas.
2. Abre la terminal de VS Code (`Ctrl + ñ`) y ejecuta:

**Windows**
```bat
.\setup.bat
```
**macOS / Linux**
```bash
./setup.sh
```

El script hace todo: `composer install`, crea `.env`, genera la llave, crea la base de datos, la reinicia (`db:wipe`), ejecuta migraciones y datos de prueba, y arranca el servidor. La primera vez tarda 1 o 2 minutos. Si algo falla, se detiene y muestra el error.

> El script **borra y recrea** la base de datos. Úsalo solo para instalar o para volver a los datos de prueba.

3. Cuando aparezca `Server running on [http://127.0.0.1:8000]`, abre **http://127.0.0.1:8000**. No cierres esa terminal mientras uses la página.

Los días siguientes basta con:
```bash
php artisan serve
```

### Paso a paso manual (equivalente)
```bash
composer install
cp .env.example .env          # en Windows: copy .env.example .env
php artisan key:generate
php artisan db:wipe
php artisan migrate --seed
php artisan serve
```

En VS Code también puedes usar `Terminal > Ejecutar tarea…` → **Raiders: iniciar servidor**, **Raiders: reiniciar base de datos con datos de prueba** o **Raiders: ejecutar pruebas**.

## 3. Usuarios de prueba

Contraseña de todos: **`Raiders2026*`**

| # | Nombre | Correo | Rol | Puede |
|---|---|---|---|---|
| 1 | Andrés Rojas | `admin@raidersparadise.co` | **Administrador** | Todo, incluidos **reportes** y usuarios |
| 2 | Camila Herrera | `ventas@raidersparadise.co` | Vendedor | Productos, inventario, pedidos, clientes y PQR |
| 3 | Juan Pérez | `juan.perez@correo.co` | Cliente | Comprar, ver pedidos y facturas, radicar PQR |
| 4 | María Torres | `maria.torres@correo.co` | Cliente | Lo mismo que Juan |
| 5 | Santiago López | `santiago.lopez@correo.co` | Cliente | Lo mismo que Juan |

En la pantalla de ingreso (solo en entorno local) hay un desplegable **«Cuentas de prueba»** que llena el correo con un clic.

Datos de demostración que se cargan junto con los usuarios:

| Tabla | Registros |
|---|---|
| rol | 3 |
| usuario | 5 |
| cliente | 8 (3 con cuenta web y 5 sin cuenta) |
| categoria / marca / proveedor | 6 / 11 / 3 |
| producto e inventario | 24 (con productos en stock bajo y agotados para probar alertas) |
| pedido y factura | 85 cada una, repartidos en los últimos 4 meses |
| detalle_pedido | 172 |
| pqr | 7 en distintos estados |

Para volver al estado inicial: `php artisan db:wipe` y luego `php artisan migrate --seed`.

## 4. Base de datos: SQLite y MySQL

### 4.1 SQLite (por defecto)

La base es el archivo `database/database.sqlite` y queda vinculada al proyecto por `.env` (`DB_CONNECTION=sqlite`). Para ver los datos:

- En VS Code instala la extensión **SQLite Viewer** (qwtel) y haz clic en `database/database.sqlite`.
- O usa el programa **DB Browser for SQLite** (sqlitebrowser.org).

### 4.2 MySQL con el script SQL (phpMyAdmin)

1. Enciende **Apache** y **MySQL** en XAMPP (o **Iniciar todo** en Laragon).
2. Entra a **http://localhost/phpmyadmin** → pestaña **Importar** → elige `database/sql/raidersparadise_mysql.sql` → **Continuar**. El script crea la base `raidersparadise` con sus 22 tablas y los datos de prueba. Puedes volver a importarlo cuando quieras reiniciar los datos (borra y recrea las tablas).
3. En `.env` deja así el bloque de base de datos:
   ```
   # DB_CONNECTION=sqlite
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=raidersparadise
   DB_USERNAME=root
   DB_PASSWORD=
   ```
4. Ejecuta:
   ```bash
   php artisan config:clear
   php artisan migrate:status
   php artisan serve
   ```
   Si todas las migraciones aparecen como **Ran**, el proyecto ya está leyendo MySQL.

> Si `http://localhost/phpmyadmin` muestra «HTTP Error 404.0» de IIS (el servidor web de Windows ocupa el puerto 80), mueve Apache al puerto 8080: en XAMPP, **Config → httpd.conf** (`Listen 8080`, `ServerName localhost:8080`), **Config → httpd-ssl.conf** (`443` → `4433`) y **Config → Service and Port Settings**; luego entra a **http://localhost:8080/phpmyadmin**.

### 4.3 MySQL con las migraciones de Laravel

1. Crea la base de datos `raidersparadise` (cotejamiento `utf8mb4_unicode_ci`).
2. Configura `.env` como en el paso 3 anterior.
3. `php artisan db:wipe` y luego `php artisan migrate --seed`.

> `migrate:fresh` solo borra las tablas si existe la tabla `migrations`. Si la base tiene tablas de una importación o prueba anterior sin esa tabla, aparece «Table 'rol' already exists»: usa `db:wipe` antes de migrar.

### 4.4 Archivos de `database/sql/`

| Archivo | Contenido |
|---|---|
| `raidersparadise_mysql.sql` | Estructura completa y datos de prueba |
| `raidersparadise_solo_estructura.sql` | Solo las tablas, vacías |
| `consultas_validacion.sql` | 9 consultas para revisar usuarios y roles, últimos pedidos, detalle de un pedido, stock bajo, ventas por mes, consistencia de facturas (subtotal + IVA = total), PQR, reportes generados y conteo por tabla |

> ⚠️ Las migraciones originales se **corrigieron**. Si el equipo ya había migrado una base MySQL con la versión anterior, reiníciala con `db:wipe` + `migrate --seed` o vuelve a importar el script.

### 4.5 Docker

```bash
docker compose up --build
```
La aplicación queda en http://localhost:8000; la primera vez migra y carga los datos de prueba automáticamente (`docker/entrypoint.sh`). phpMyAdmin queda en http://localhost:8881 (usuario `laravel`, contraseña `laravel`). MySQL queda expuesto en el puerto `3308` del equipo.

## 5. Qué incluye

**Tienda (clientes)**
- Inicio, catálogo con búsqueda, filtros (categoría, marca, precio) y orden; ficha de producto con stock en tiempo real.
- Registro e ingreso; carrito guardado en la base de datos; checkout con dirección y medio de pago (Nequi, PSE, tarjeta, contra entrega). El pago es **simulado**: no hay pasarela real.
- «Mis pedidos» con seguimiento, factura imprimible con IVA 19 % desglosado, «Mis datos» y «Mis PQR».
- Formulario de PQR para visitantes o clientes, con número de radicado.

**Panel administrativo (`/admin`)**
- Dashboard: ventas del mes frente al mes anterior, pedidos, pedidos por despachar, productos por reponer, gráfica de ventas de 30 días, participación por categoría, últimos pedidos y alertas de inventario.
- CRUD de productos (con carga de imagen), categorías, marcas y proveedores.
- Inventario con entradas, salidas y ajustes; filtros de stock bajo y agotado.
- Pedidos: cambio de estado; al **cancelar** se devuelven las unidades al inventario y se anula la factura.
- Clientes con historial; PQR con asignación de responsable y respuesta obligatoria para cerrar o rechazar.
- Usuarios y roles (solo administrador). Los usuarios se desactivan en lugar de borrarse.

**Permisos por rol**

| Módulo | Administrador | Vendedor | Cliente |
|---|---|---|---|
| Tienda, carrito, compras, mis pedidos | No (usa cuenta de cliente) | No | Sí |
| Dashboard, productos, inventario, pedidos, clientes, PQR, catálogos | Sí | Sí | No (403) |
| Reportes | Sí | No (403) | No (403) |
| Usuarios | Sí | No (403) | No (403) |

**Reportes (solo administrador)**: cada uno en **pantalla**, **PDF** (versión imprimible, «Guardar como PDF») y **Excel** (CSV con `;` y tildes correctas):

| Reporte | Contenido |
|---|---|
| Ventas por periodo | Ventas diarias, pedidos, unidades, ticket promedio, IVA, medio de pago más usado |
| Productos más vendidos | Ranking por ingresos y unidades, participación |
| Ventas por categoría | Participación de cada categoría |
| Mejores clientes | Clientes por valor comprado y última compra |
| Pedidos por estado | Detalle de pedidos, estado y medio de pago |
| Estado del inventario | Existencias, mínimos, alertas y valor del inventario |
| PQR | Por tipo y estado, responsable y días promedio de respuesta |

Cada reporte generado queda registrado en la tabla `reporte` (historial visible en la pantalla de reportes).

## 6. Guía de pruebas manuales

**Como cliente** (`juan.perez@correo.co`)
1. Catálogo → filtra por *Cascos*, marca *Shoei* y ordena por precio.
2. Sin sesión, intenta agregar al carrito: te envía al ingreso.
3. Ingresa, agrega productos (prueba con *Rev'It Sand 4 H2O*: está agotado y no debe dejarte).
4. Carrito → cambia cantidades → **Continuar al pago** → **Confirmar y pagar**.
5. Revisa el pedido, la factura (**Imprimir o guardar PDF**) y radica una PQR.
6. Entra a `/admin`: debe mostrar **403**.

**Como vendedor** (`ventas@raidersparadise.co`)
1. Pedidos → cambia el estado del pedido de Juan.
2. Inventario → verifica que bajaron las unidades compradas.
3. Cancela un pedido: las unidades regresan y la factura queda *Anulada*.
4. PQR → asigna responsable y ciérrala (sin respuesta debe mostrar error).
5. Entra a `/admin/reportes`: debe mostrar **403**.

**Como administrador** (`admin@raidersparadise.co`)
1. Revisa el dashboard (indicadores, gráficas y alertas).
2. Crea un producto con imagen y verifica que aparezca en la tienda.
3. Intenta eliminar la categoría *Cascos*: no debe permitirlo.
4. Reportes → genera cada uno en **Ver**, **PDF** y **Excel**, y revisa el historial.
5. Usuarios → crea uno, desactívalo y comprueba que no puede ingresar.

Para validar en la base de datos lo que hiciste en la página, usa las consultas de `database/sql/consultas_validacion.sql` o abre `database/database.sqlite` con SQLite Viewer.

## 7. Pruebas automáticas

```bash
php artisan test
php artisan test --filter=TiendaTest      # solo la tienda
php artisan test --filter=compra_completa # una prueba puntual
```

**23 pruebas, 171 verificaciones**, todas aprobadas. Cubren: carga de páginas públicas, los 5 usuarios de prueba, login incorrecto y cuentas inactivas, registro, permisos por rol, compra completa (descuento de inventario, factura e IVA), límites de stock y productos agotados, privacidad entre clientes, cancelación con devolución de inventario, PQR de visitantes y cierre con respuesta obligatoria, todas las pantallas del panel, los 7 reportes en sus 3 formatos, cuadre del reporte de ventas, creación de productos con imagen, protección de productos con ventas y de categorías con productos, movimientos de inventario y gestión de usuarios.

Las pruebas usan una base SQLite temporal en memoria: no modifican tus datos.

## 8. Dónde está el frontend y dónde el backend

Regla rápida: **`resources/views/` y `public/` son frontend; `app/`, `routes/` y `database/` son backend.**

### Frontend

| Ubicación | Qué contiene |
|---|---|
| `resources/views/layouts/` | Marcos de página: `tienda`, `admin` y `hoja` (facturas y reportes imprimibles) |
| `resources/views/tienda/` | `home`, `catalogo`, `producto`, `carrito`, `checkout`, `pqr` |
| `resources/views/auth/` | `login`, `registro` |
| `resources/views/cuenta/` | `pedidos`, `pedido`, `factura`, `perfil`, `pqrs` |
| `resources/views/admin/` | `dashboard`, `productos/`, `inventario`, `pedidos/`, `clientes/`, `pqr/`, `usuarios/`, `maestros/`, `reportes/` |
| `resources/views/partials/` y `components/` | Tarjeta de producto, alertas, paginación, logo e íconos |
| `resources/views/errors/` | Páginas 403, 404, 419 y 500 |
| `public/css/raiders.css` | Todo el diseño: colores, tipografía, componentes y versión móvil |
| `public/js/raiders.js` | Interacciones y gráficas SVG (sin librerías) |
| `public/images/` | Logo, ícono e ilustraciones de productos |

### Backend

| Ubicación | Qué hace |
|---|---|
| `routes/web.php` | Todas las URL y qué rol puede entrar a cada una |
| `app/Http/Controllers/` | `TiendaController`, `AuthController`, `CarritoController`, `CheckoutController`, `CuentaController`, `PqrController` |
| `app/Http/Controllers/Admin/` | `DashboardController`, `ProductoController`, `MaestroController` (categorías, marcas, proveedores), `InventarioController`, `PedidoController`, `ClienteController`, `PqrController`, `UsuarioController`, `ReporteController` |
| `app/Http/Middleware/EnsureRol.php` | Control de acceso por rol (`rol:Administrador,Vendedor`) |
| `app/Services/PedidoService.php` | Compra, factura, inventario y cambios de estado (en transacciones) |
| `app/Services/ReporteService.php` | Cálculo de los 7 reportes (compatible con MySQL y SQLite) |
| `app/Models/` | `Usuario`, `Rol`, `Cliente`, `Producto`, `Categoria`, `Marca`, `Proveedor`, `Inventario`, `Carrito`, `DetalleCarrito`, `Pedido`, `DetallePedido`, `Factura`, `Pqr`, `Reporte` |
| `app/Support/Formato.php` | Formato de pesos colombianos (directiva Blade `@cop`) |
| `database/migrations/` | `00_00_laravel_base` (tablas internas de Laravel) y `01` a `15` (modelo de datos) |
| `database/seeders/` | `RolSeeder`, `UsuarioSeeder` (5 usuarios), `CatalogoSeeder`, `DemoVentasSeeder` |
| `database/sql/` | Scripts MySQL y consultas de validación |
| `lang/es/` | Mensajes de validación en español |

### Otros archivos

| Ubicación | Uso |
|---|---|
| `config/`, `bootstrap/app.php`, `.env` | Configuración |
| `tests/` | Pruebas automáticas |
| `docs/` | Documentación del código fuente generada (`docs/api`) y su guía (`docs/README.md`) |
| `tools/generar-documentacion.php` | Generador de la documentación HTML |
| `phpdoc.dist.xml` | Configuración opcional de phpDocumentor |
| `Dockerfile`, `docker-compose.yml`, `docker/entrypoint.sh` | Entorno Docker |
| `setup.bat`, `setup.sh` | Instalación rápida |
| `.vscode/` | Extensiones recomendadas y tareas de VS Code (se comparten con el equipo en el repositorio) |
| `vendor/` | Dependencias de Composer; no se edita ni se sube a GitHub |

**Cómo se conectan:** al entrar a `/catalogo`, `routes/web.php` llama a `TiendaController@catalogo`, que consulta los productos con el modelo `Producto` y los envía a la vista `tienda/catalogo.blade.php`, dibujada con `raiders.css`.

## 9. Documentación del código fuente

El código está documentado en el propio fuente:

| Lenguaje | Formato |
|---|---|
| PHP (`app/`, `database/`, `routes/`, `tests/`, `tools/`) | PHPDoc: encabezado de archivo, clase, propiedades, constantes y cada método con `@param`, `@return` y `@throws` |
| JavaScript (`public/js/raiders.js`) | JSDoc |
| Vistas Blade | Comentario `{{-- --}}` al inicio: propósito, controlador que la usa y variables |
| CSS (`public/css/raiders.css`) | Encabezado con colores de marca e índice de secciones |

**Ver la documentación:** abre `docs/api/index.html` en el navegador (doble clic). Incluye una página por clase, las rutas, el diccionario de datos y el catálogo de vistas y funciones JavaScript.

**Regenerarla** después de cambiar el código o sus comentarios:
```bash
php tools/generar-documentacion.php
```

**Verificar el estilo PSR-12** (Laravel Pint, incluido en el proyecto):
```bash
vendor/bin/pint --test --preset=psr12 app database routes tests tools
```

Más detalles y una plantilla para archivos nuevos en `docs/README.md`.

## 10. Problemas comunes

| Mensaje o síntoma | Solución |
|---|---|
| «php no se reconoce como comando» | Abre la terminal desde Laragon o agrega PHP al PATH y reinicia VS Code |
| «could not find driver» | En `php.ini` quita el `;` de `extension=pdo_sqlite`, `extension=sqlite3` (o `extension=pdo_mysql`) y reinicia la terminal |
| «Table 'rol' already exists» | La base tenía tablas sin la tabla `migrations`: `php artisan db:wipe` y luego `php artisan migrate --seed` |
| «Address already in use» | `php artisan serve --port=8001` |
| «419 Página expirada» | La sesión venció: recarga la página |
| «Access denied for user root» | Pon la contraseña de MySQL en `DB_PASSWORD` |
| «Unknown database 'raidersparadise'» | Importa el script SQL o crea la base antes de migrar |
| phpMyAdmin muestra «HTTP Error 404.0» | IIS ocupa el puerto 80: mueve Apache al 8080 (sección 4.2) |
| MySQL de XAMPP en otro puerto | Revisa el puerto en el panel de XAMPP (a veces 3307) y cámbialo en `DB_PORT` |
| No se ven cambios de CSS | Recarga con `Ctrl + F5` |
| Cambié `.env` y no pasa nada | `php artisan config:clear` |
| Errores del servidor | Revisa `storage/logs/laravel.log` |

## 11. Flujo de trabajo con Git

### Subir el proyecto a un repositorio nuevo

1. En GitHub crea un repositorio **vacío** (sin README, sin `.gitignore` y sin licencia) y copia su URL.
2. En la terminal, dentro de la carpeta del proyecto:
   ```bash
   git init
   git add .
   git commit -m "feat: Raiders Paradise versión 2.1 completa"
   git branch -M main
   git remote add origin https://github.com/USUARIO/NOMBRE-DEL-REPOSITORIO.git
   git push -u origin main
   git checkout -b develop
   git push -u origin develop
   ```
3. Antes del `git add .`, revisa con `git status` que **no** aparezcan `.env`, `vendor/` ni `database/database.sqlite`. El `.gitignore` ya los excluye.

### Trabajo en equipo

Ramas: `main` (estable) ← `develop` ← `feat/…` y `fix/…`. Cada cambio se hace en una rama propia y se integra con un Pull Request hacia `develop`:
```bash
git checkout develop
git pull
git checkout -b feat/nombre-del-cambio
# ...cambios...
git add .
git commit -m "feat: descripción del cambio"
git push -u origin feat/nombre-del-cambio
```

Cada integrante, después de clonar el repositorio, ejecuta `.\setup.bat` (Windows) o `./setup.sh` (Mac/Linux).

## 12. Documentación del proyecto

| Documento | Contenido |
|---|---|
| `README.md` | Este archivo: instalación, uso y pruebas |
| `PROJECT_CONTEXT.md` | Propósito, alcance, actores, arquitectura, restricciones y riesgos |
| `CORRECCIONES.md` | Errores encontrados en el proyecto original y cómo se corrigieron |
| `docs/README.md` y `docs/api/index.html` | Documentación del código fuente: clases, métodos, rutas, diccionario de datos y frontend |
