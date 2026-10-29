# Documentación del código fuente — Raiders Paradise

Esta carpeta contiene la documentación técnica generada a partir de los comentarios del código.

## Ver la documentación

Abre `docs/api/index.html` en el navegador (doble clic). No necesita servidor.

| Página | Contenido |
|---|---|
| `api/index.html` | Arquitectura, estadísticas y clases por capa |
| `api/clase-*.html` | Una por clase: atributos, constantes, propiedades y métodos con `@param`, `@return` y `@throws` |
| `api/rutas.html` | Las 53 rutas con su controlador y middleware |
| `api/base-de-datos.html` | Diccionario de datos: columnas, tipos, nulos, índices y llaves foráneas |
| `api/frontend.html` | Vistas Blade, atributos `data-*` y funciones JavaScript |

## Regenerarla

Después de cambiar el código o sus comentarios:

```bash
php tools/generar-documentacion.php
```

Para el diccionario de datos la base debe estar disponible (SQLite siempre; MySQL encendido).

Alternativa con la herramienta oficial: descarga `phpDocumentor.phar` desde https://phpdoc.org y ejecuta `php phpDocumentor.phar` en la raíz (usa `phpdoc.dist.xml`; salida en `docs/phpdoc`).

## Estándares que sigue el código

| Lenguaje | Formato de comentarios |
|---|---|
| PHP | PHPDoc (PSR-5 / catálogo de etiquetas PSR-19): encabezado de archivo, clase, propiedades y métodos |
| JavaScript | JSDoc en `public/js/raiders.js` |
| Blade | Comentario `{{-- --}}` al inicio de cada vista: propósito, controlador, variables |
| CSS | Encabezado con colores de marca e índice de secciones en `public/css/raiders.css` |

Estilo de código: PSR-12, verificado con Laravel Pint:

```bash
vendor/bin/pint --test --preset=psr12 app database routes tests tools
```

### Plantilla para un archivo PHP nuevo

```php
<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * <Qué contiene este archivo, en una línea>.
 *
 * @package   RaidersParadise
 * @author    <Tu nombre> (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.0.0
 * @since     <AAAA-MM-DD>
 */

namespace App\...;

/**
 * <Resumen de la clase en una línea>.
 *
 * <Descripción opcional: reglas, relaciones, dónde se usa>.
 */
class MiClase
{
    /**
     * <Qué hace el método, en una línea>.
     *
     * @param Tipo $nombre <Descripción>.
     * @return Tipo <Qué devuelve>.
     * @throws Excepcion <Cuándo ocurre>.
     */
    public function miMetodo(Tipo $nombre): Tipo
    {
        // ...
    }
}
```
