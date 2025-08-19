@extends('adminsite.layout')

@section('cabecera')
    @parent
    <!-- CSS adicional si es necesario -->
@stop

@section('ContenidoSite-01')
<form method="POST" action="{{ route('generate.ssl') }}">
    @csrf
    <input type="text" name="domain" class="form-control" placeholder="Escribe el dominio">
    <button type="submit">Generar SSL</button>
</form>
@stop
