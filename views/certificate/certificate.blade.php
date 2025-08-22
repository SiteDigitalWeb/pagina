@extends('adminsite.layout')

@section('cabecera')
    @parent
    <!-- CSS adicional si es necesario -->
@stop

@section('ContenidoSite-01')
    <h2>Crear Nuevo Tenant</h2>

    <!-- Mensajes de estado -->
    @if(session('success'))
        <div style="padding:10px; background:#d4edda; color:#155724; border:1px solid #c3e6cb; border-radius:5px; margin-bottom:15px;">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="padding:10px; background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; border-radius:5px; margin-bottom:15px;">
            ❌ {{ session('error') }}
        </div>
    @endif

    <!-- Errores de validación -->
    @if($errors->any())
        <div style="padding:10px; background:#fff3cd; color:#856404; border:1px solid #ffeeba; border-radius:5px; margin-bottom:15px;">
            <ul style="margin:0; padding-left:20px;">
                @foreach($errors->all() as $error)
                    <li>⚠️ {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Formulario -->
    <form action="{{ route('tenants.store') }}" method="POST">
        @csrf
        <div style="margin-bottom:10px;">
            <label for="domain">Dominio del Tenant:</label><br>
            <input type="text" id="domain" name="domain"
                   value="{{ old('domain') }}"
                   placeholder="ej: juanchaproducciones.com.co"
                   required
                   style="padding:8px; width:100%; max-width:400px; border:1px solid #ccc; border-radius:5px;">
        </div>

        <button type="submit"
                style="padding:10px 20px; background:#007bff; color:white; border:none; border-radius:5px; cursor:pointer;">
            Crear Tenant
        </button>
    </form>
@stop