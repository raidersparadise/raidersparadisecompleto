<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Generador de la documentación HTML del código fuente (sin dependencias externas).
 *
 * Lee los comentarios PHPDoc de las clases con la API de reflexión de PHP y produce
 * un sitio estático en docs/api con:
 *   - índice general y una página por clase (propiedades, constantes y métodos),
 *   - tabla de rutas con su controlador y middleware,
 *   - diccionario de datos leído de la base de datos configurada en .env,
 *   - catálogo de vistas Blade (encabezados {{-- --}}) y funciones JavaScript (JSDoc).
 *
 * Uso (desde la raíz del proyecto):
 *   php tools/generar-documentacion.php
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.0.0
 * @since     2026-10-29
 */

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

$raiz = dirname(__DIR__);
require $raiz.'/vendor/autoload.php';
$app = require $raiz.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$salida = $raiz.'/docs/api';
if (! is_dir($salida)) {
    mkdir($salida, 0775, true);
}

/** Grupos del índice: etiqueta => prefijo de espacio de nombres o carpeta. */
$grupos = [
    'Modelos (app/Models)' => 'App\\Models\\',
    'Servicios de negocio (app/Services)' => 'App\\Services\\',
    'Controladores de la tienda (app/Http/Controllers)' => 'App\\Http\\Controllers\\',
    'Controladores del panel (app/Http/Controllers/Admin)' => 'App\\Http\\Controllers\\Admin\\',
    'Middleware (app/Http/Middleware)' => 'App\\Http\\Middleware\\',
    'Soporte y proveedores' => ['App\\Support\\', 'App\\Providers\\'],
    'Seeders (database/seeders)' => 'Database\\Seeders\\',
];

// ---------------------------------------------------------------------------
// 1. Descubrir clases a partir de los archivos .php
// ---------------------------------------------------------------------------

/**
 * Obtiene el nombre completo de la clase declarada en un archivo.
 *
 * @param string $archivo Ruta del archivo PHP.
 * @return string|null Nombre con espacio de nombres, o null si no declara una clase con nombre.
 */
function claseDeArchivo(string $archivo): ?string
{
    $codigo = file_get_contents($archivo);
    if (! preg_match('/^namespace\s+([^;]+);/m', $codigo, $ns)) {
        return null;
    }
    if (! preg_match('/^(?:abstract\s+|final\s+)?class\s+(\w+)/m', $codigo, $cl)) {
        return null;
    }

    return trim($ns[1]).'\\'.$cl[1];
}

$clases = [];
foreach (['app', 'database/seeders'] as $dir) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($raiz.'/'.$dir));
    foreach ($it as $f) {
        if ($f->getExtension() === 'php' && ($c = claseDeArchivo($f->getPathname()))) {
            $clases[$c] = str_replace($raiz.'/', '', $f->getPathname());
        }
    }
}
ksort($clases);

// ---------------------------------------------------------------------------
// 2. Utilidades de PHPDoc y HTML
// ---------------------------------------------------------------------------

/**
 * Escapa texto para HTML.
 *
 * @param string|null $t
 * @return string
 */
function e(?string $t): string
{
    return htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8');
}

/**
 * Convierte un docblock en resumen, descripción y etiquetas.
 *
 * @param string|false $doc Comentario /** ... *\/ o false.
 * @return array{resumen: string, descripcion: string, tags: list<array{0: string, 1: string}>}
 */
function parsearDoc($doc): array
{
    $r = ['resumen' => '', 'descripcion' => '', 'tags' => []];
    if (! $doc) {
        return $r;
    }
    $lineas = preg_split('/\R/', $doc);
    $texto = [];
    foreach ($lineas as $l) {
        $l = preg_replace('#^\s*/\*\*\s?|^\s*\*/\s*$|^\s*\*\s?#', '', $l);
        $texto[] = rtrim($l);
    }
    $cuerpo = [];
    $tagActual = null;
    foreach ($texto as $l) {
        if (preg_match('/^@(\S+)\s*(.*)$/', trim($l), $m)) {
            $r['tags'][] = [$m[1], $m[2]];
            $tagActual = count($r['tags']) - 1;
        } elseif ($tagActual !== null && trim($l) !== '') {
            $r['tags'][$tagActual][1] .= ' '.trim($l);
        } elseif ($tagActual === null) {
            $cuerpo[] = $l;
        }
    }
    $cuerpo = trim(implode("\n", $cuerpo));
    $partes = preg_split('/\n\s*\n/', $cuerpo, 2);
    $r['resumen'] = trim(str_replace("\n", ' ', $partes[0] ?? ''));
    $r['descripcion'] = trim($partes[1] ?? '');

    return $r;
}

/**
 * Da formato HTML a una descripción: párrafos, viñetas y {@see ...} en línea.
 *
 * @param string $t Texto plano.
 * @return string HTML.
 */
function formatoTexto(string $t): string
{
    $t = e($t);
    $t = preg_replace('/\{@see\s+([^}]+)\}/', '<code>$1</code>', $t);
    $html = '';
    foreach (preg_split('/\n\s*\n/', $t) as $bloque) {
        $lineas = explode("\n", $bloque);
        if (count(array_filter($lineas, fn ($l) => str_starts_with(trim($l), '- '))) === count($lineas)) {
            $html .= '<ul>'.implode('', array_map(fn ($l) => '<li>'.substr(trim($l), 2).'</li>', $lineas)).'</ul>';
        } else {
            $html .= '<p>'.implode('<br>', array_map('trim', $lineas)).'</p>';
        }
    }

    return $html;
}

/**
 * Nombre de archivo HTML para una clase.
 *
 * @param string $clase
 * @return string
 */
function paginaClase(string $clase): string
{
    return 'clase-'.strtolower(str_replace('\\', '-', $clase)).'.html';
}

/**
 * Representación legible del tipo de un parámetro o retorno.
 *
 * @param ReflectionType|null $t
 * @return string
 */
function tipo(?ReflectionType $t): string
{
    return $t ? str_replace(['App\\Models\\', 'Illuminate\\Http\\', 'App\\Services\\'], '', (string) $t) : '';
}

/**
 * Firma de un método en una línea, por ejemplo: public function agregar(Request $request, Producto $producto)
 *
 * @param ReflectionMethod $m
 * @return string
 */
function firma(ReflectionMethod $m): string
{
    $mods = implode(' ', Reflection::getModifierNames($m->getModifiers()));
    $ps = [];
    foreach ($m->getParameters() as $p) {
        $s = trim(tipo($p->getType()).' '.($p->isVariadic() ? '...' : '').'$'.$p->getName());
        if ($p->isDefaultValueAvailable()) {
            $s .= ' = '.var_export($p->getDefaultValue(), true);
        }
        $ps[] = str_replace('NULL', 'null', $s);
    }
    $ret = $m->getReturnType() ? ': '.tipo($m->getReturnType()) : '';

    return "{$mods} function {$m->getName()}(".implode(', ', $ps).")".$ret;
}

/**
 * Tabla HTML de etiquetas PHPDoc (@param, @return, @throws, @see...).
 *
 * @param list<array{0: string, 1: string}> $tags
 * @param list<string> $excluir Etiquetas que no se muestran.
 * @return string
 */
function tablaTags(array $tags, array $excluir = []): string
{
    $tags = array_values(array_filter($tags, fn ($t) => ! in_array($t[0], $excluir, true)));
    if (! $tags) {
        return '';
    }
    $h = '<table class="tags">';
    foreach ($tags as [$n, $v]) {
        $h .= '<tr><th>@'.e($n).'</th><td>'.preg_replace('/\{@see\s+([^}]+)\}/', '<code>$1</code>', e($v)).'</td></tr>';
    }

    return $h.'</table>';
}

/**
 * Plantilla HTML común de todas las páginas.
 *
 * @param string $titulo
 * @param string $contenido HTML del cuerpo.
 * @param string $nav HTML del menú lateral.
 * @return string
 */
function pagina(string $titulo, string $contenido, string $nav): string
{
    $fecha = date('d/m/Y H:i');

    return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$titulo} | Raiders Paradise — Documentación del código</title>
<link rel="stylesheet" href="estilo.css">
</head>
<body>
<header><a href="index.html" class="marca"><img src="logo.png" alt="">Raiders Paradise <span>Documentación del código fuente · v2.0.0</span></a>
<input type="search" id="filtro" placeholder="Filtrar clases…" aria-label="Filtrar clases"></header>
<div class="layout"><nav id="nav">{$nav}</nav><main>{$contenido}<footer>Generado el {$fecha} con tools/generar-documentacion.php a partir de los comentarios PHPDoc.</footer></main></div>
<script>document.getElementById('filtro').addEventListener('input',function(e){var q=e.target.value.toLowerCase();document.querySelectorAll('#nav li').forEach(function(li){li.style.display=li.textContent.toLowerCase().indexOf(q)>-1?'':'none'})});</script>
</body>
</html>
HTML;
}

// ---------------------------------------------------------------------------
// 3. Navegación
// ---------------------------------------------------------------------------

/**
 * Indica si una clase pertenece a un grupo del índice (coincidencia exacta de espacio de nombres).
 *
 * @param string $clase
 * @param string|list<string> $prefijos
 * @return bool
 */
function enGrupo(string $clase, $prefijos): bool
{
    foreach ((array) $prefijos as $p) {
        $resto = substr($clase, strlen($p));
        if (str_starts_with($clase, $p) && ! str_contains($resto, '\\')) {
            return true;
        }
    }

    return false;
}

$nav = '<h4>General</h4><ul><li><a href="index.html">Inicio</a></li><li><a href="rutas.html">Rutas</a></li><li><a href="base-de-datos.html">Base de datos</a></li><li><a href="frontend.html">Vistas y JavaScript</a></li></ul>';
foreach ($grupos as $etq => $pref) {
    $items = array_filter(array_keys($clases), fn ($c) => enGrupo($c, $pref));
    if (! $items) {
        continue;
    }
    $nav .= '<h4>'.e(preg_replace('/\s*\(.*\)/', '', $etq)).'</h4><ul>';
    foreach ($items as $c) {
        $nav .= '<li><a href="'.paginaClase($c).'">'.e(class_basename($c)).'</a></li>';
    }
    $nav .= '</ul>';
}

// ---------------------------------------------------------------------------
// 4. Páginas de clases
// ---------------------------------------------------------------------------

$totalMetodos = 0;
$indiceGrupos = '';
foreach ($grupos as $etq => $pref) {
    $items = array_filter(array_keys($clases), fn ($c) => enGrupo($c, $pref));
    if (! $items) {
        continue;
    }
    $indiceGrupos .= '<h2>'.e($etq).'</h2><table class="lista"><tr><th>Clase</th><th>Descripción</th><th>Métodos</th></tr>';

    foreach ($items as $clase) {
        $rc = new ReflectionClass($clase);
        $doc = parsearDoc($rc->getDocComment());
        $padre = $rc->getParentClass() ? $rc->getParentClass()->getName() : null;

        // Solo métodos escritos en el archivo de la clase (excluye los que vienen de traits de Laravel)
        $metodos = array_filter($rc->getMethods(), fn ($m) => $m->class === $clase && $m->getFileName() === $rc->getFileName());
        usort($metodos, fn ($a, $b) => $a->getStartLine() <=> $b->getStartLine());
        $totalMetodos += count($metodos);

        $h = '<p class="ruta">'.e($clases[$clase]).'</p><h1>'.e(class_basename($clase)).'</h1>';
        $h .= '<p class="ns">'.e($clase).($padre ? ' <span>extends '.e($padre).'</span>' : '').'</p>';
        $h .= '<div class="resumen">'.formatoTexto($doc['resumen']).'</div>'.formatoTexto($doc['descripcion']);

        $props = array_values(array_filter($doc['tags'], fn ($t) => str_starts_with($t[0], 'property')));
        if ($props) {
            $h .= '<h2>Atributos del modelo</h2><table class="lista"><tr><th>Atributo</th><th>Tipo</th><th>Nota</th></tr>';
            foreach ($props as [$n, $v]) {
                preg_match('/^(\S+)\s+\$(\w+)\s*(.*)$/', $v, $m);
                $h .= '<tr><td><code>$'.e($m[2] ?? '').'</code></td><td><code>'.e($m[1] ?? '').'</code></td><td>'.($n === 'property-read' ? 'Calculado (solo lectura). ' : '').e($m[3] ?? '').'</td></tr>';
            }
            $h .= '</table>';
        }
        $h .= tablaTags($doc['tags'], ['property', 'property-read', 'package', 'author', 'copyright', 'license', 'version', 'since']);

        $constantes = array_filter($rc->getReflectionConstants(), fn ($k) => $k->getDeclaringClass()->getName() === $clase);
        if ($constantes) {
            $h .= '<h2>Constantes</h2>';
            foreach ($constantes as $k) {
                $d = parsearDoc($k->getDocComment());
                $valor = var_export($k->getValue(), true);
                $valor = strlen($valor) > 400 ? substr($valor, 0, 400).' …' : $valor;
                $h .= '<div class="miembro"><h3><code>'.e($k->getName()).'</code></h3>'.formatoTexto($d['resumen']).'<pre>'.e($valor).'</pre></div>';
            }
        }

        $propsReales = array_filter($rc->getProperties(), fn ($p) => $p->class === $clase);
        if ($propsReales) {
            $h .= '<h2>Propiedades</h2>';
            foreach ($propsReales as $p) {
                $d = parsearDoc($p->getDocComment());
                $mods = implode(' ', Reflection::getModifierNames($p->getModifiers()));
                $h .= '<div class="miembro"><h3><code>'.e($mods).' $'.e($p->getName()).'</code></h3>'.formatoTexto($d['resumen']).tablaTags($d['tags']).'</div>';
            }
        }

        if ($metodos) {
            $h .= '<h2>Métodos</h2><ul class="toc">';
            foreach ($metodos as $m) {
                $h .= '<li><a href="#'.e($m->getName()).'">'.e($m->getName()).'()</a></li>';
            }
            $h .= '</ul>';
            foreach ($metodos as $m) {
                $d = parsearDoc($m->getDocComment());
                $vis = $m->isPublic() ? 'publico' : ($m->isProtected() ? 'protegido' : 'privado');
                $h .= '<div class="miembro" id="'.e($m->getName()).'"><h3>'.e($m->getName()).'() <span class="vis '.$vis.'">'.$vis.'</span></h3>';
                $h .= '<pre class="firma">'.e(firma($m)).'</pre>'.formatoTexto($d['resumen']).formatoTexto($d['descripcion']).tablaTags($d['tags']);
                $h .= '<p class="linea">Líneas '.$m->getStartLine().'–'.$m->getEndLine().'</p></div>';
            }
        }

        file_put_contents($salida.'/'.paginaClase($clase), pagina(class_basename($clase), $h, $nav));
        $indiceGrupos .= '<tr><td><a href="'.paginaClase($clase).'">'.e(class_basename($clase)).'</a></td><td>'.e($doc['resumen']).'</td><td class="num">'.count($metodos).'</td></tr>';
    }
    $indiceGrupos .= '</table>';
}

// ---------------------------------------------------------------------------
// 5. Rutas
// ---------------------------------------------------------------------------

$filasRutas = '';
$totalRutas = 0;
foreach (Route::getRoutes() as $r) {
    $accion = $r->getActionName();
    if (! str_starts_with($accion, 'App\\')) {
        continue; // rutas internas de Laravel
    }
    $totalRutas++;
    [$ctrl, $met] = array_pad(explode('@', $accion), 2, '');
    $mw = implode(', ', array_filter($r->gatherMiddleware(), fn ($x) => $x !== 'web'));
    $filasRutas .= '<tr><td><span class="verbo">'.e(implode('|', array_diff($r->methods(), ['HEAD']))).'</span></td><td><code>/'.e(ltrim($r->uri(), '/')).'</code></td><td>'.e($r->getName()).'</td>'
        .'<td><a href="'.paginaClase($ctrl).'#'.e($met).'">'.e(class_basename($ctrl)).'@'.e($met).'</a></td><td>'.e($mw ?: 'pública').'</td></tr>';
}
$hR = '<h1>Rutas</h1><p>Definidas en <code>routes/web.php</code>. Middleware: <code>auth</code> exige sesión, <code>guest</code> solo invitados, <code>rol:...</code> limita por rol (<code>App\Http\Middleware\EnsureRol</code>) y <code>throttle:n,1</code> limita intentos por minuto.</p>'
    .'<table class="lista"><tr><th>Método</th><th>URL</th><th>Nombre</th><th>Acción</th><th>Middleware</th></tr>'.$filasRutas.'</table>';
file_put_contents($salida.'/rutas.html', pagina('Rutas', $hR, $nav));

/**
 * Extrae el tipo declarado de cada columna en el código de una migración.
 *
 * Permite mostrar el tipo exacto (varchar(40), decimal(12,2), enum(...)) aunque la
 * base de datos sea SQLite, que reporta tipos genéricos.
 *
 * @param string $codigo Código fuente de la migración.
 * @return array<string, array<string, string>> [tabla => [columna => tipo]]
 */
function tiposDeMigracion(string $codigo): array
{
    $res = [];
    preg_match_all("/Schema::create\('([a-z_]+)',\s*function\s*\(Blueprint \\\$table\)\s*\{(.*?)\n\s*\}\);/s", $codigo, $bloques, PREG_SET_ORDER);
    foreach ($bloques as [$_, $tabla, $cuerpo]) {
        preg_match_all("/\\\$table->(\w+)\(\s*'(\w+)'\s*(?:,\s*([^)]*?))?\)((?:->\w+\([^)]*\))*)/", $cuerpo, $cols, PREG_SET_ORDER);
        foreach ($cols as $c) {
            [$_, $metodo, $col, $args] = $c + [3 => ''];
            $args = trim($args ?? '');
            $tipo = match ($metodo) {
                'id' => 'bigint unsigned, autoincremental',
                'string' => 'varchar('.($args !== '' ? $args : '255').')',
                'char' => 'char('.$args.')',
                'text', 'mediumText', 'longText' => strtolower($metodo),
                'integer' => 'int',
                'unsignedInteger' => 'int unsigned',
                'unsignedBigInteger' => 'bigint unsigned',
                'unsignedTinyInteger' => 'tinyint unsigned',
                'decimal' => 'decimal('.str_replace(' ', '', $args).')',
                'boolean' => 'boolean (tinyint(1))',
                'date' => 'date',
                'dateTime' => 'datetime',
                'timestamp' => 'timestamp',
                'enum' => 'enum('.trim(preg_replace('/[\[\]\s]+/', ' ', str_replace("'", '', $args))).')',
                default => null,
            };
            if ($tipo) {
                $res[$tabla][$col] = trim($tipo);
            }
        }
        if (str_contains($cuerpo, 'timestamps()')) {
            $res[$tabla]['created_at'] = $res[$tabla]['updated_at'] = 'timestamp';
        }
        if (str_contains($cuerpo, 'rememberToken()')) {
            $res[$tabla]['remember_token'] = 'varchar(100)';
        }
    }

    return $res;
}

// ---------------------------------------------------------------------------
// 6. Base de datos (diccionario de datos)
// ---------------------------------------------------------------------------

$hD = '<h1>Base de datos</h1>';
$migraciones = glob($raiz.'/database/migrations/*.php');
sort($migraciones);
try {
    $conexion = DB::connection()->getDriverName().' — '.basename((string) DB::connection()->getDatabaseName());
    DB::connection()->getPdo();
    $conectado = true;
} catch (Throwable $ex) {
    $conexion = 'sin conexión ('.$ex->getMessage().')';
    $conectado = false;
}
$hD .= '<p>Estructura definida por las migraciones de <code>database/migrations</code>. Columnas leídas de la conexión configurada en <code>.env</code>: <strong>'.e($conexion).'</strong>. '
    .'Convenciones: tablas y columnas en español y en singular; llave primaria <code>id_&lt;tabla&gt;</code>; llaves foráneas con el mismo nombre que la llave primaria referenciada.</p><ul class="toc">';
$secciones = '';
$totalTablas = 0;
foreach ($migraciones as $f) {
    $codigo = file_get_contents($f);
    preg_match_all("/Schema::create\('([a-z_]+)'/", $codigo, $tabs);
    preg_match('#/\*\*(?:(?!\*/).)*?\*/\s*return new class#s', $codigo, $bloque);
    $doc = parsearDoc(isset($bloque[0]) ? preg_replace('/\s*return new class$/', '', $bloque[0]) : false);
    $tiposMig = tiposDeMigracion($codigo);
    $esBase = str_contains(basename($f), '00_00');
    foreach ($tabs[1] as $t) {
        if ($esBase) {
            continue;
        }
        $totalTablas++;
        $hD .= '<li><a href="#'.$t.'">'.$t.'</a></li>';
        $secciones .= '<div class="miembro" id="'.$t.'"><h3><code>'.$t.'</code> <span class="ruta">'.e(basename($f)).'</span></h3>'.formatoTexto($doc['resumen']);
        if ($conectado && Schema::hasTable($t)) {
            $fks = [];
            foreach (Schema::getForeignKeys($t) as $fk) {
                $fks[$fk['columns'][0]] = $fk['foreign_table'].'.'.$fk['foreign_columns'][0];
            }
            $idx = [];
            foreach (Schema::getIndexes($t) as $ix) {
                foreach ($ix['columns'] as $c) {
                    $idx[$c][] = $ix['primary'] ? 'PK' : ($ix['unique'] ? 'único' : 'índice');
                }
            }
            $secciones .= '<table class="lista"><tr><th>Columna</th><th>Tipo</th><th>Nulo</th><th>Por defecto</th><th>Clave</th></tr>';
            foreach (Schema::getColumns($t) as $c) {
                $clave = implode(', ', array_unique($idx[$c['name']] ?? []));
                if (isset($fks[$c['name']])) {
                    $clave .= ($clave ? ', ' : '').'FK → '.$fks[$c['name']];
                }
                $tipoCol = $tiposMig[$t][$c['name']] ?? $c['type'];
                $secciones .= '<tr><td><code>'.e($c['name']).'</code></td><td>'.e($tipoCol).'</td><td>'.($c['nullable'] ? 'sí' : 'no').'</td><td>'.e((string) $c['default']).'</td><td>'.e($clave).'</td></tr>';
            }
            $secciones .= '</table>';
        } else {
            $secciones .= formatoTexto($doc['descripcion']);
        }
        $secciones .= '</div>';
    }
}
$hD .= '</ul>'.$secciones.'<h2>Tablas internas de Laravel</h2><p><code>password_reset_tokens</code>, <code>sessions</code>, <code>cache</code>, <code>cache_locks</code>, <code>jobs</code>, <code>job_batches</code>, <code>failed_jobs</code> y <code>migrations</code> (creadas por <code>00_00_laravel_base.php</code> y por Laravel).</p>';
file_put_contents($salida.'/base-de-datos.html', pagina('Base de datos', $hD, $nav));

// ---------------------------------------------------------------------------
// 7. Vistas Blade y JavaScript
// ---------------------------------------------------------------------------

$hF = '<h1>Vistas y JavaScript</h1><p>Las vistas Blade están en <code>resources/views</code>; cada una inicia con un comentario <code>{{-- --}}</code> que indica su propósito, el controlador que la usa y las variables que recibe. Los estilos están en <code>public/css/raiders.css</code> y las interacciones en <code>public/js/raiders.js</code>.</p>';
$hF .= '<h2>Vistas Blade</h2><table class="lista"><tr><th>Vista</th><th>Descripción</th><th>Usada por</th><th>Variables</th></tr>';
$vistas = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($raiz.'/resources/views'));
$filasV = [];
$totalVistas = 0;
foreach ($vistas as $f) {
    if (! str_ends_with($f->getFilename(), '.blade.php')) {
        continue;
    }
    $totalVistas++;
    $c = file_get_contents($f->getPathname());
    preg_match('/\{\{--(.*?)--\}\}/s', $c, $m);
    $lin = array_values(array_filter(array_map('trim', explode("\n", $m[1] ?? ''))));
    $nombre = str_replace(['/', '.blade.php'], ['.', ''], substr($f->getPathname(), strlen($raiz.'/resources/views/')));
    $desc = $lin[1] ?? '';
    $usada = preg_replace('/^Usada por:\s*/', '', current(array_filter($lin, fn ($l) => str_starts_with($l, 'Usada por'))) ?: '');
    $vars = preg_replace('/^Variables:\s*/', '', current(array_filter($lin, fn ($l) => str_starts_with($l, 'Variables'))) ?: '');
    $filasV[$nombre] = '<tr><td><code>'.e($nombre).'</code></td><td>'.e($desc).'</td><td>'.e($usada).'</td><td>'.e($vars).'</td></tr>';
}
ksort($filasV);
$hF .= implode('', $filasV).'</table>';

$js = file_get_contents($raiz.'/public/js/raiders.js');
preg_match('#^/\*\*(.*?)\*/#s', $js, $cab);
$hF .= '<h2>public/js/raiders.js</h2>'.formatoTexto(parsearDoc('/**'.($cab[1] ?? '').'*/')['resumen']);
preg_match('/\| Atributo.*?\n((?:\s*\*\s*\|.*\n)+)/', $js, $tabla);
if ($tabla) {
    $hF .= '<table class="lista"><tr><th>Atributo HTML</th><th>Efecto</th></tr>';
    foreach (explode("\n", trim($tabla[1])) as $fila) {
        $cols = array_map('trim', explode('|', trim($fila, " *|\t")));
        if (count($cols) >= 2 && ! str_starts_with($cols[0], '-')) {
            $hF .= '<tr><td><code>'.e($cols[0]).'</code></td><td>'.e($cols[1]).'</td></tr>';
        }
    }
    $hF .= '</table>';
}
preg_match_all('#/\*\*((?:(?!\*/).)*?)\*/\s*\n\s*(?:function\s+(\w+)|const\s+(\w+))#s', $js, $fns, PREG_SET_ORDER);
$hF .= '<h3>Funciones</h3><table class="lista"><tr><th>Función</th><th>Descripción</th><th>Parámetros y retorno</th></tr>';
foreach ($fns as $fn) {
    $d = parsearDoc('/**'.$fn[1].'*/');
    $nombre = $fn[2] ?: ($fn[3] ?? '');
    $resumen = $d['resumen'] ?: trim(preg_replace('/@\w+.*$/', '', trim($fn[1], " *\n")));
    $tags = implode('<br>', array_map(fn ($t) => '@'.e($t[0]).' '.e($t[1]), $d['tags']));
    $hF .= '<tr><td><code>'.e($nombre).'</code></td><td>'.e($resumen).'</td><td class="small">'.$tags.'</td></tr>';
}
$hF .= '</table>';
file_put_contents($salida.'/frontend.html', pagina('Vistas y JavaScript', $hF, $nav));

// ---------------------------------------------------------------------------
// 8. Índice, estilos y logo
// ---------------------------------------------------------------------------

$hI = '<h1>Documentación del código fuente</h1>'
    .'<p class="resumen">Raiders Paradise es una tienda en línea de accesorios premium para motociclistas con panel administrativo y reportes gerenciales, construida con Laravel 12 (PHP 8.2+), vistas Blade, CSS y JavaScript propios.</p>'
    .'<div class="kpis"><div><b>'.count($clases).'</b>clases documentadas</div><div><b>'.$totalMetodos.'</b>métodos</div><div><b>'.$totalRutas.'</b>rutas</div><div><b>'.$totalTablas.'</b>tablas del negocio</div><div><b>'.$totalVistas.'</b>vistas Blade</div></div>'
    .'<h2>Arquitectura</h2><pre class="diagrama">Navegador ──► routes/web.php ──► Middleware (auth, rol, throttle, CSRF)
                                   │
                                   ▼
                       Controladores (app/Http/Controllers)  ── validan y autorizan
                                   │
                                   ▼
                       Servicios (app/Services)              ── reglas de negocio y transacciones
                                   │
                                   ▼
                       Modelos Eloquent (app/Models)         ── tablas y relaciones
                                   │
                                   ▼
                       Base de datos (SQLite / MySQL)

Respuesta: vistas Blade (resources/views) + public/css/raiders.css + public/js/raiders.js</pre>'
    .$indiceGrupos;
file_put_contents($salida.'/index.html', pagina('Inicio', $hI, $nav));

copy($raiz.'/public/images/logo-blanco.png', $salida.'/logo.png');
file_put_contents($salida.'/estilo.css', <<<'CSS'
:root{--indigo:#363088;--brasa:#E84615;--noche:#1E2247;--cuero:#391C1D;--papel:#f3f2f7;--linea:#dcdbe8;--texto:#3b3f5c;--suave:#6b6f8a}
*{box-sizing:border-box}body{margin:0;font:15px/1.55 "Segoe UI",system-ui,sans-serif;color:var(--texto);background:var(--papel)}
header{position:sticky;top:0;z-index:5;display:flex;gap:16px;align-items:center;justify-content:space-between;padding:12px 24px;background:var(--noche);color:#fff}
header .marca{display:flex;gap:10px;align-items:center;color:#fff;text-decoration:none;font-weight:700;font-size:1.15rem}
header .marca img{width:32px}header .marca span{font-weight:400;font-size:.8rem;color:#b8bbe0;margin-left:6px}
header input{padding:8px 12px;border-radius:999px;border:0;min-width:220px}
.layout{display:grid;grid-template-columns:260px 1fr;min-height:calc(100vh - 56px)}
nav{background:#fff;border-right:1px solid var(--linea);padding:16px;overflow-y:auto;position:sticky;top:56px;height:calc(100vh - 56px)}
nav h4{margin:14px 0 6px;font-size:.78rem;color:var(--suave);font-weight:600}nav ul{list-style:none;margin:0;padding:0}
nav a{display:block;padding:4px 8px;border-radius:6px;color:var(--noche);text-decoration:none;font-size:.92rem}nav a:hover{background:var(--papel);color:var(--indigo)}
main{padding:28px 36px;max-width:1100px;min-width:0}h1{color:var(--noche);margin:.2em 0}h2{color:var(--noche);border-bottom:2px solid var(--linea);padding-bottom:6px;margin-top:2em}
h3{color:var(--noche);margin:0 0 6px}a{color:var(--indigo)}code{background:#ecebf5;padding:1px 5px;border-radius:4px;font-size:.88em}
pre{background:var(--noche);color:#e9e8f7;padding:12px 14px;border-radius:8px;overflow-x:auto;font-size:.85rem}pre.firma{background:#ecebf5;color:var(--noche);border-left:4px solid var(--indigo)}
pre.diagrama{background:#fff;color:var(--noche);border:1px solid var(--linea)}
.ruta{color:var(--suave);font-size:.85rem;margin:0}.ns{color:var(--suave);margin-top:0}.ns span{color:var(--indigo)}.resumen{font-size:1.05rem;color:var(--noche)}
.miembro{background:#fff;border:1px solid var(--linea);border-radius:10px;padding:16px 18px;margin:12px 0}
.vis{font-size:.72rem;padding:2px 8px;border-radius:999px;font-weight:600;vertical-align:middle}.publico{background:#e2f4ec;color:#1f8a5b}.protegido{background:#fbf0dc;color:#b7791f}.privado{background:#fbe4e4;color:#c62828}
table{border-collapse:collapse;width:100%;background:#fff;margin:10px 0}th,td{text-align:left;padding:7px 10px;border-bottom:1px solid #eeedf5;vertical-align:top}th{background:#fafafd;color:var(--suave);font-weight:600;font-size:.85rem}
table.tags th{width:110px;color:var(--brasa);background:#fff;font-family:monospace}.num{text-align:right}.linea{color:var(--suave);font-size:.8rem;margin:6px 0 0}.small{font-size:.85rem}
.verbo{font-family:monospace;font-weight:700;color:var(--brasa)}.toc{columns:3;font-size:.9rem}
.kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin:18px 0}.kpis div{background:#fff;border:1px solid var(--linea);border-radius:10px;padding:14px;color:var(--suave)}
.kpis b{display:block;font-size:1.8rem;color:var(--indigo)}footer{margin-top:40px;padding-top:12px;border-top:1px solid var(--linea);color:var(--suave);font-size:.8rem}
@media(max-width:800px){.layout{grid-template-columns:1fr}nav{position:static;height:auto}.toc{columns:1}main{padding:20px 16px}}
CSS);

echo 'Documentación generada en docs/api: '.count($clases).' clases, '.$totalMetodos.' métodos, '.$totalRutas.' rutas, '.$totalTablas.' tablas, '.$totalVistas." vistas.\n";
echo "Abre docs/api/index.html en el navegador.\n";
