{{--
    Vista: errors.419
    Página de error 419: Sesión o token CSRF vencido.

    Usada por:  La muestra Laravel automáticamente.
    Variables:  —
--}}
@extends('errors.layout')
@section('codigo', '419')
@section('mensaje', 'La sesión expiró')
@section('detalle', 'Recarga la página e inténtalo de nuevo.')
