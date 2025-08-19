@extends('adminsite.layout')

@section('cabecera')
    @parent
    <!-- CSS adicional si es necesario -->
@stop

@section('ContenidoSite-01')
<form action="{{ route('tenants.store') }}" method="POST">
    @csrf
    <label>Dominio del Tenant:</label>
    <input type="text" name="domain" placeholder="ej: juanchaproducciones.com.co" required>
    <button type="submit">Crear Tenant</button>
</form>
@stop
