{{--
    Vista: errors.403
    Página de error 403: Acceso denegado (rol sin permiso).

    Usada por:  La muestra Laravel automáticamente.
    Variables:  —
--}}
@extends('errors.layout')
@section('codigo', '403')
@section('mensaje', 'No tienes acceso a esta sección')
@section('detalle', 'Ingresa con una cuenta que tenga los permisos necesarios.')
