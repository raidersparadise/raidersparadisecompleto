{{--
    Vista: errors.500
    Página de error 500: Error interno del servidor.

    Usada por:  La muestra Laravel automáticamente.
    Variables:  —
--}}
@extends('errors.layout')
@section('codigo', '500')
@section('mensaje', 'Algo falló en el taller')
@section('detalle', 'Ya estamos revisando. Intenta de nuevo en unos minutos.')
