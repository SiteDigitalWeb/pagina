@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3>Configuración SMTP (Tenant: {{ $tenant->name ?? 'Sin tenant' }})</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('tenant.mail.update') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Host</label>
            <input name="mail_host" class="form-control" value="{{ old('mail_host', $tenant->mail_host) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Puerto</label>
            <input name="mail_port" class="form-control" value="{{ old('mail_port', $tenant->mail_port) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Usuario SMTP (mail_username)</label>
            <input name="mail_username" class="form-control" value="{{ old('mail_username', $tenant->mail_username) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Contraseña SMTP (si la deja vacía no la cambia)</label>
            <input name="mail_password" class="form-control" value="">
        </div>

        <div class="mb-3">
            <label class="form-label">Encriptación</label>
            <input name="mail_encryption" class="form-control" value="{{ old('mail_encryption', $tenant->mail_encryption) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">From address</label>
            <input name="mail_from_address" class="form-control" value="{{ old('mail_from_address', $tenant->mail_from_address) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">From name</label>
            <input name="mail_from_name" class="form-control" value="{{ old('mail_from_name', $tenant->mail_from_name) }}">
        </div>

        <button class="btn btn-primary" type="submit">Guardar configuración</button>
    </form>

    <hr>

    <h4>Enviar correo de prueba</h4>
    <form action="{{ route('tenant.mail.test') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label">Enviar a</label>
            <input name="to" class="form-control" placeholder="tu@correo.com">
        </div>
        <button class="btn btn-success" type="submit">Enviar prueba</button>
    </form>
</div>
@endsection
