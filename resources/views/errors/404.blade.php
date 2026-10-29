{{--
    Vista: errors.404
    Página de error 404: Página o producto no encontrado.

    Usada por:  La muestra Laravel automáticamente.
    Variables:  —
--}}
@extends('errors.layout')
@section('codigo', '404')
@section('mensaje', 'Esta página se salió de la ruta')
@section('detalle', 'El enlace no existe o el producto ya no está disponible.')
