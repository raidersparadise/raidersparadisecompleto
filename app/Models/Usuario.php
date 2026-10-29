<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Modelo de autenticación del sistema sobre la tabla "usuario".
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.0.0
 * @since     2026-10-29
 */

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Usuario con acceso al sistema (personal o cliente).
 *
 * Reemplaza el modelo User por defecto de Laravel y es el proveedor de
 * autenticación configurado en config/auth.php. La contraseña se cifra
 * automáticamente con bcrypt mediante el cast "hashed".
 *
 * @property int $id_usuario
 * @property int $id_rol
 * @property string $nombre_usuario
 * @property string $apellido_usuario
 * @property string $email
 * @property string $password
 * @property string|null $telefono
 * @property bool $activo
 * @property-read string $nombre_completo
 * @property-read string $iniciales
 * @property-read Rol $rol
 * @property-read Cliente|null $cliente
 */
class Usuario extends Authenticatable
{
    use Notifiable;

    /**
     * Nombre de la tabla en la base de datos.
     *
     * @var string
     */
    protected $table = 'usuario';

    /**
     * Llave primaria personalizada (convención del proyecto: id_<tabla>).
     *
     * @var string
     */
    protected $primaryKey = 'id_usuario';

    /**
     * Atributos que se pueden asignar masivamente con create() o fill().
     *
     * @var list<string>
     */
    protected $fillable = [
        'id_rol', 'nombre_usuario', 'apellido_usuario', 'email', 'password', 'telefono', 'activo',
    ];

    /**
     * Atributos que nunca se incluyen al convertir el modelo a arreglo o JSON.
     *
     * @var list<string>
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Conversión de tipos: cifra la contraseña y trata "activo" como booleano.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    /**
     * Rol asignado al usuario.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Rol, $this>
     */
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    /**
     * Registro de cliente enlazado (solo para usuarios compradores).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\Cliente, $this>
     */
    public function cliente()
    {
        return $this->hasOne(Cliente::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Reportes generados por el usuario (historial del módulo de reportes).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Reporte, $this>
     */
    public function reportes()
    {
        return $this->hasMany(Reporte::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Devuelve el cliente del usuario, creándolo o enlazándolo si hace falta.
     *
     * Si ya existe un cliente con el mismo correo (por ejemplo, alguien que radicó
     * una PQR sin cuenta), se enlaza con este usuario en lugar de duplicarlo.
     *
     * @return Cliente Cliente asociado y guardado en la base de datos.
     */
    public function clienteAsociado(): Cliente
    {
        if ($this->cliente) {
            return $this->cliente;
        }
        $cliente = Cliente::firstOrNew(['email' => $this->email]);
        $cliente->fill([
            'id_usuario' => $this->id_usuario,
            'nombre_cliente' => $cliente->nombre_cliente ?: $this->nombre_usuario,
            'apellido_cliente' => $cliente->apellido_cliente ?: $this->apellido_usuario,
            'telefono' => $cliente->telefono ?: $this->telefono,
        ])->save();
        $this->setRelation('cliente', $cliente);

        return $cliente;
    }

    /**
     * Accesor $usuario->nombre_completo: nombre y apellido.
     *
     * @return string
     */
    public function getNombreCompletoAttribute(): string
    {
        return trim($this->nombre_usuario.' '.$this->apellido_usuario);
    }

    /**
     * Accesor $usuario->iniciales: iniciales en mayúscula para el avatar.
     *
     * @return string Dos letras, por ejemplo "AR".
     */
    public function getInicialesAttribute(): string
    {
        return mb_strtoupper(mb_substr($this->nombre_usuario, 0, 1).mb_substr($this->apellido_usuario, 0, 1));
    }

    /**
     * Indica si el usuario tiene alguno de los roles indicados.
     *
     * @param string ...$roles Nombres de rol (usar las constantes de {@see Rol}).
     * @return bool
     */
    public function tieneRol(string ...$roles): bool
    {
        return in_array($this->rol?->nombre_rol, $roles, true);
    }

    /**
     * Indica si el usuario es Administrador.
     *
     * @return bool
     */
    public function esAdmin(): bool
    {
        return $this->tieneRol(Rol::ADMIN);
    }

    /**
     * Indica si el usuario es personal interno (Administrador o Vendedor).
     *
     * @return bool
     */
    public function esPersonal(): bool
    {
        return $this->tieneRol(Rol::ADMIN, Rol::VENDEDOR);
    }
}
