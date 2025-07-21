@extends('adminsite.layout')

@section('cabecera')
@endsection

@section('ContenidoSite-01')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-offset-2">
            <div class="block">
                <div class="block-title">
                    <h2><strong>Crear</strong> registro sitio</h2>
                </div>
                <div class="card">
                    <div class="card-body">
                        {{-- Mostrar errores generales si existen --}}
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(Session::get('status') == 'ok_create')
                         <div class="alert alert-success alert-dismissible">
                         <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                         <strong><i class="icon fa fa-check"></i> Tenant Creado con Éxito</strong> {{ Session::get('message') }}
                         </div>
                        @endif

                        {{ Form::open(['method' => 'POST', 'class' => 'form-horizontal', 'url' => 'sd/create']) }}

                            {{-- Nombre --}}
                            <div class="form-group row">
                                <label for="name" class="col-md-4 col-form-label text-md-right">Nombre</label>
                                <div class="col-md-8">
                                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                                        name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Email --}}
                            <div class="form-group row">
                                <label for="email" class="col-md-4 col-form-label text-md-right">E-Mail</label>
                                <div class="col-md-8">
                                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                        name="email" value="{{ old('email') }}" required>
                                    @error('email')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Hostname --}}
                            @if(!$tenantName)
                                <div class="form-group row">
                                    <label for="fqdn" class="col-md-4 col-form-label text-md-right">Hostname</label>
                                    <div class="col-md-8">
                                        <input id="fqdn" type="text" class="form-control @error('fqdn') is-invalid @enderror"
                                            name="fqdn" value="{{ old('fqdn') }}" required>
                                        @error('fqdn')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            @endif

                            {{-- Fecha --}}
                            <div class="form-group row">
                                <label for="date" class="col-md-4 col-form-label text-md-right">Fecha</label>
                                <div class="col-md-8">
                                    <input id="date" type="date" class="form-control @error('date') is-invalid @enderror"
                                        name="date" value="{{ old('date') }}" required>
                                    @error('date')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Select de Países --}}
                            <div class="form-group row">
                                <label for="pais_id" class="col-md-4 col-form-label text-md-right">País</label>
                                <div class="col-md-8">
                                    <select id="pais_id" name="pais_id" class="form-control @error('pais_id') is-invalid @enderror" required>
                                        <option value="">Seleccione un país</option>
                                        @foreach($paises as $pais)
                                            <option value="{{ $pais->id }}" {{ old('pais_id') == $pais->id ? 'selected' : '' }}>
                                                {{ $pais->pais }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('pais_id')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Contraseña --}}
                            <div class="form-group row">
                                <label for="password" class="col-md-4 col-form-label text-md-right">Contraseña</label>
                                <div class="col-md-8">
                                    <input id="password" type="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        name="password" required>
                                    @error('password')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Repetir Contraseña --}}
                            <div class="form-group row">
                                <label for="password-confirm" class="col-md-4 col-form-label text-md-right">Repetir contraseña</label>
                                <div class="col-md-8">
                                    <input id="password-confirm" type="password" class="form-control"
                                        name="password_confirmation" required>
                                </div>
                            </div>

                            {{-- Plan oculto o dinámico si lo agregas --}}
                            <input type="hidden" name="plan" value="1">

                            {{-- Botón --}}
                            <div class="form-group row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary btn-block">Registrar</button>
                                </div>
                            </div>

                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
@endsection
