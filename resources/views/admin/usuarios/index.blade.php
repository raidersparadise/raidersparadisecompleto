{{--
    Vista: admin.usuarios.index
    Listado de usuarios por rol con acciones de editar y desactivar.

    Usada por:  Admin\UsuarioController@index  (GET /admin/usuarios)
    Variables:  $usuarios (paginados), $roles
--}}
@extends('layouts.admin')
@section('titulo', 'Usuarios')

@section('contenido')
<div class="admin-cabeza">
    <div><h1>Usuarios</h1><p>Cuentas de acceso del personal y de los clientes.</p></div>
    <a class="btn btn-brasa" href="{{ route('admin.usuarios.create') }}"><x-icono n="mas" />Nuevo usuario</a>
</div>
<div class="tabs">
    <a href="{{ route('admin.usuarios.index') }}" @class(['activo' => !request('rol')])>Todos</a>
    @foreach ($roles as $r)<a href="{{ route('admin.usuarios.index', ['rol' => $r->id_rol]) }}" @class(['activo' => request('rol') == $r->id_rol])>{{ $r->nombre_rol }}</a>@endforeach
</div>
<div class="tabla-wrap">
    <table class="tabla">
        <thead><tr><th>Usuario</th><th>Correo</th><th>Rol</th><th>Estado</th><th>Creado</th><th></th></tr></thead>
        <tbody>
        @foreach ($usuarios as $u)
            <tr>
                <td><div class="prod"><span class="avatar">{{ $u->iniciales }}</span><strong>{{ $u->nombre_completo }}</strong></div></td>
                <td>{{ $u->email }}</td>
                <td>{{ $u->rol->nombre_rol }}</td>
                <td><span class="badge badge-{{ $u->activo ? 'activo' : 'inactivo' }}">{{ $u->activo ? 'Activo' : 'Inactivo' }}</span></td>
                <td>{{ $u->created_at?->format('d/m/Y') }}</td>
                <td>
                    <div class="acciones">
                        <a class="btn btn-fantasma btn-sm" href="{{ route('admin.usuarios.edit', $u) }}"><x-icono n="editar" size="16" />Editar</a>
                        @if ($u->activo && !$u->is(auth()->user()))
                            <form method="POST" action="{{ route('admin.usuarios.destroy', $u) }}" data-confirmar="¿Desactivar la cuenta de {{ $u->nombre_completo }}? No podrá ingresar.">
                                @csrf @method('DELETE')
                                <button class="btn btn-peligro btn-sm" type="submit">Desactivar</button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{ $usuarios->links() }}
@endsection
