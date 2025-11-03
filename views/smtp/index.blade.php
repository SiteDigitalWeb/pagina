@extends ('adminsite.layout')
 @section('ContenidoSite-01')
 <div class="container">
    <div class="row">
        <div class="col-md-12">
            <!-- Basic Form Elements Block -->
            <div class="block">
                <!-- Basic Form Elements Title -->
                <div class="block-title">
                    <div class="block-options pull-right"></div>
                    <h2><strong>Configuración</strong> Mailgun</h2>
                </div>
                <!-- END Form Elements Title -->
                
                <!-- Basic Form Elements Content -->
              @if(session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        {{ session('success') }}
    </div>
@endif

<!-- Mostrar errores generales -->
@if($errors->any())
    <div class="alert alert-danger alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        <strong>Por favor corrige los siguientes errores:</strong>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

                <!-- Mostrar errores de conexión SMTP específicos -->
                @error('smtp_connection')
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <strong>Error de conexión:</strong> {{ $message }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @enderror

                <form action="{{ route('smtp.store') }}" method="POST" class="form-horizontal">
                    @csrf
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label" for="mail_host">Mail Host *</label>
                        <div class="col-md-9">
                            <input type="text" name="mail_host" id="mail_host" 
                                   class="form-control @error('mail_host') is-invalid @enderror" 
                                   value="{{ old('mail_host', $config->mail_host ?? 'smtp.mailgun.org') }}"
                                   placeholder="smtp.mailgun.org">
                            @error('mail_host')
                                <div class="invalid-feedback d-block text-danger">
                                    <small><i class="fa fa-exclamation-circle"></i> {{ $message }}</small>
                                </div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label" for="mail_port">Mail Port *</label>
                        <div class="col-md-9">
                            <input type="text" name="mail_port" id="mail_port" 
                                   class="form-control @error('mail_port') is-invalid @enderror" 
                                   value="{{ old('mail_port', $config->mail_port ?? '587') }}"
                                   placeholder="587">
                            @error('mail_port')
                                <div class="invalid-feedback d-block text-danger">
                                    <small><i class="fa fa-exclamation-circle"></i> {{ $message }}</small>
                                </div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label" for="mail_encryption">Encryption</label>
                        <div class="col-md-9">
                            <select name="mail_encryption" id="mail_encryption" 
                                    class="form-control @error('mail_encryption') is-invalid @enderror">
                                <option value="">Seleccionar...</option>
                                <option value="tls" {{ old('mail_encryption', $config->mail_encryption ?? '') == 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ old('mail_encryption', $config->mail_encryption ?? '') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="starttls" {{ old('mail_encryption', $config->mail_encryption ?? '') == 'starttls' ? 'selected' : '' }}>STARTTLS</option>
                            </select>
                            @error('mail_encryption')
                                <div class="invalid-feedback d-block text-danger">
                                    <small><i class="fa fa-exclamation-circle"></i> {{ $message }}</small>
                                </div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label" for="mail_username">Mail Username *</label>
                        <div class="col-md-9">
                            <input type="email" name="mail_username" id="mail_username" 
                                   class="form-control @error('mail_username') is-invalid @enderror" 
                                   value="{{ old('mail_username', $config->mail_username ?? '') }}"
                                   placeholder="usuario@dominio.com">
                            @error('mail_username')
                                <div class="invalid-feedback d-block text-danger">
                                    <small><i class="fa fa-exclamation-circle"></i> {{ $message }}</small>
                                </div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label" for="mail_password">Mail Password *</label>
                        <div class="col-md-9">
                            <input type="password" name="mail_password" id="mail_password" 
                                   class="form-control @error('mail_password') is-invalid @enderror" 
                                   value="{{ old('mail_password', $config->mail_password ?? '') }}"
                                   placeholder="Ingresa tu contraseña">
                            @error('mail_password')
                                <div class="invalid-feedback d-block text-danger">
                                    <small><i class="fa fa-exclamation-circle"></i> {{ $message }}</small>
                                </div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label" for="mail_from_address">From Address *</label>
                        <div class="col-md-9">
                            <input type="email" name="mail_from_address" id="mail_from_address" 
                                   class="form-control @error('mail_from_address') is-invalid @enderror" 
                                   value="{{ old('mail_from_address', $config->mail_from_address ?? '') }}"
                                   placeholder="remitente@dominio.com">
                            @error('mail_from_address')
                                <div class="invalid-feedback d-block text-danger">
                                    <small><i class="fa fa-exclamation-circle"></i> {{ $message }}</small>
                                </div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label" for="mail_from_name">From Name *</label>
                        <div class="col-md-9">
                            <input type="text" name="mail_from_name" id="mail_from_name" 
                                   class="form-control @error('mail_from_name') is-invalid @enderror" 
                                   value="{{ old('mail_from_name', $config->mail_from_name ?? '') }}"
                                   placeholder="Nombre del remitente">
                            @error('mail_from_name')
                                <div class="invalid-feedback d-block text-danger">
                                    <small><i class="fa fa-exclamation-circle"></i> {{ $message }}</small>
                                </div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label" for="mailgun_domain">Mailgun Domain *</label>
                        <div class="col-md-9">
                            <input type="text" name="mailgun_domain" id="mailgun_domain" 
                                   class="form-control @error('mailgun_domain') is-invalid @enderror" 
                                   value="{{ old('mailgun_domain', $config->mailgun_domain ?? '') }}"
                                   placeholder="mg.dominio.com">
                            @error('mailgun_domain')
                                <div class="invalid-feedback d-block text-danger">
                                    <small><i class="fa fa-exclamation-circle"></i> {{ $message }}</small>
                                </div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label" for="mailgun_secret">Mailgun Secret *</label>
                        <div class="col-md-9">
                            <input type="text" name="mailgun_secret" id="mailgun_secret" 
                                   class="form-control @error('mailgun_secret') is-invalid @enderror" 
                                   value="{{ old('mailgun_secret', $config->mailgun_secret ?? '') }}"
                                   placeholder="key-xxxxxxxxxxxxxxxx">
                            @error('mailgun_secret')
                                <div class="invalid-feedback d-block text-danger">
                                    <small><i class="fa fa-exclamation-circle"></i> {{ $message }}</small>
                                </div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-group form-actions">
                        <div class="col-md-9 col-md-offset-3">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fa fa-save"></i> Guardar Configuración
                            </button>
                            <button type="reset" class="btn btn-sm btn-warning">
                                <i class="fa fa-repeat"></i> Cancelar
                            </button>
                        </div>
                    </div>
                </form>
                
                <!-- Test Email Form -->
                <div class="block-title">
                    <h2><strong>Prueba de</strong> Configuración</h2>
                </div>
                
                <!-- Alerts para pruebas Bootstrap 3 -->
@error('email_test')
    <div class="alert alert-warning alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        <strong>Error en prueba:</strong> {{ $message }}
    </div>
@enderror

@if(session('test_success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        {{ session('test_success') }}
    </div>
@endif

@if(session('test_error'))
    <div class="alert alert-danger alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        {{ session('test_error') }}
    </div>
@endif
                <form action="{{ route('smtp.test') }}" method="POST" class="form-horizontal">
                    @csrf
                    <div class="form-group">
                        <label class="col-md-3 control-label" for="email_test">Correo de Prueba</label>
                        <div class="col-md-9">
                            <div class="input-group">
                                <input type="email" name="email_test" id="email_test" 
                                       class="form-control @error('email_test') is-invalid @enderror" 
                                       value="{{ old('email_test') }}"
                                       placeholder="correo@destino.com">
                                <button class="btn btn-success" type="submit">
                                    <i class="fa fa-paper-plane"></i> Enviar correo de prueba
                                </button>
                            </div>
                            @error('email_test')
                                <div class="invalid-feedback d-block text-danger mt-2">
                                    <small><i class="fa fa-exclamation-circle"></i> {{ $message }}</small>
                                </div>
                            @enderror
                        </div>
                    </div>
                </form>
            </div>
            <!-- END Basic Form Elements Block -->
        </div>
    </div>
</div>
@endsection
