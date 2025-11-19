@extends('adminsite.layout')

@section('cabecera')
    @parent
    @push('styles')
        <link href="//cdn.datatables.net/plug-ins/be7019ee387/integration/bootstrap/3/dataTables.bootstrap.css" rel="stylesheet">
        <link href="//cdnjs.cloudflare.com/ajax/libs/jquery.bootstrapvalidator/0.5.0/css/bootstrapValidator.min.css" rel="stylesheet">
    @endpush
@stop

@section('ContenidoSite-01')
<div class="content-header">
    <ul class="nav-horizontal text-center">
        <li><a href="/gestor/ver-templates"><i class="fa fa-desktop"></i> Ver templates</a></li>
        <li><a href="/gestion/logo-head"><i class="fa fa-arrow-circle-up"></i> Logo encabezado</a></li>
        <li><a href="/gestion/logo-footer"><i class="fa fa-arrow-circle-down"></i> Logo pie página</a></li>
        <li><a href="/gestion/configurar-correo"><i class="fa fa-envelope"></i> Configurar correo</a></li>
        <li><a href="/gestion/redes-sociales"><i class="hi hi-bullhorn"></i> Redes sociales</a></li>
        @if(Auth::id() === 1)
            <li class="active"><a href="/gestion/venta"><i class="gi gi-usd"></i> Ventas</a></li>
        @endif
    </ul>
</div>

{{-- Mostrar alertas --}}
@if(session('status'))
<div class="container topper">
    <div class="alert alert-{{ session('status') === 'ok_delete' ? 'danger' : (session('status') === 'ok_update' ? 'warning' : 'success') }} alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        @switch(session('status'))
            @case('ok_create')
                <strong><i class="fa fa-check-circle"></i> Éxito!</strong> Configuración SEO guardada correctamente.
                @break
            @case('ok_update')
                <strong><i class="fa fa-exclamation-triangle"></i> Actualizado!</strong> Configuración SEO actualizada correctamente.
                @break
            @case('ok_delete')
                <strong><i class="fa fa-trash"></i> Eliminado!</strong> Configuración SEO eliminada correctamente.
                @break
        @endswitch
    </div>
</div>
@endif

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="block">
                <div class="block-title">
                    <h2><strong>Posicionamiento</strong> SEO - {{ $tenantName ?? 'Default' }}</h2>
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> 
                        <strong>Sistema multi-tenant activo:</strong> 
                        Repositorio: <code>saas/{{ $tenantName ?? 'default' }}</code>
                    </div>
                </div>

                {{ Form::open([
                    'files' => true,
                    'method' => 'POST',
                    'class' => 'form-horizontal',
                    'id' => 'seoForm',
                    'url' => 'sd/seo'
                ]) }}

                {{-- Configuración Básica SEO --}}
                <div class="seo-section">
                    <h4 class="section-title">Configuración Básica</h4>
                    
                    <div class="form-group">
                        {{ Form::label('idioma', 'Idioma', ['class' => 'col-md-3 control-label']) }}
                        <div class="col-md-9">
                            {{ Form::text('idioma', $seo->idioma, [
                                'class' => 'form-control',
                                'placeholder' => 'es, en, fr, etc.'
                            ]) }}
                        </div>
                    </div>
                    
                    <div class="form-group">
                        {{ Form::label('canonical', 'Canonical URL', ['class' => 'col-md-3 control-label']) }}
                        <div class="col-md-9">
                            {{ Form::text('canonical', $seo->canonical, [
                                'class' => 'form-control',
                                'placeholder' => 'https://misitio.com'
                            ]) }}
                        </div>
                    </div>
                    
                    <div class="form-group">
                        {{ Form::label('robot', 'Robots', ['class' => 'col-md-3 control-label']) }}
                        <div class="col-md-9">
                            {{ Form::textarea('robot', $seo->robots, [
                                'class' => 'form-control',
                                'placeholder' => 'index, follow, noindex, nofollow, etc.',
                                'rows' => 2
                            ]) }}
                        </div>
                    </div>
                </div>

                {{-- Open Graph --}}
                <div class="seo-section">
                    <h4 class="section-title">Open Graph</h4>
                    
                    <div class="form-group">
                        {{ Form::label('og_name', 'og:title', ['class' => 'col-md-3 control-label']) }}
                        <div class="col-md-9">
                            {{ Form::text('og_name', $seo->og_name, [
                                'class' => 'form-control',
                                'placeholder' => 'Título para redes sociales'
                            ]) }}
                        </div>
                    </div>
                    
                    <div class="form-group">
                        {{ Form::label('og_type', 'og:type', ['class' => 'col-md-3 control-label']) }}
                        <div class="col-md-9">
                            {{ Form::text('og_type', $seo->og_type, [
                                'class' => 'form-control',
                                'placeholder' => 'website, article, product, etc.'
                            ]) }}
                        </div>
                    </div>
                    
                    <div class="form-group">
                        {{ Form::label('og_url', 'og:url', ['class' => 'col-md-3 control-label']) }}
                        <div class="col-md-9">
                            {{ Form::text('og_url', $seo->og_url, [
                                'class' => 'form-control',
                                'placeholder' => 'URL canónica para compartir'
                            ]) }}
                        </div>
                    </div>
                    
                    {{-- OG Image --}}
                    <div class="form-group">
                        {{ Form::label('og_image', 'og:image', ['class' => 'col-md-3 control-label']) }}
                        <div class="col-md-9">
                            <div class="input-group">
                                {{ Form::text('og_image', $seo->og_image, [
                                    'class' => 'form-control',
                                    'id' => 'og_image_input',
                                    'placeholder' => 'URL de la imagen para redes sociales',
                                    'readonly' => true
                                ]) }}
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button" onclick="openFileManager('og_image_input')">
                                        <i class="fa fa-folder-open"></i> Seleccionar
                                    </button>
                                </span>
                            </div>
                            <small class="help-text">Imagen para redes sociales (1200x630px recomendado)</small>
                            
                            <div class="image-preview mt-2" id="og_image_preview" 
                                 style="{{ $seo->og_image ? '' : 'display: none;' }}">
                                @if($seo->og_image)
                                    <img src="{{ $seo->og_image }}" alt="OG Image Preview" 
                                         style="max-width: 200px; height: auto; border: 1px solid #ddd; padding: 5px; border-radius: 4px;">
                                    <div class="mt-1">
                                        <small class="text-muted">{{ $seo->og_image }}</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Twitter Card --}}
                <div class="seo-section">
                    <h4 class="section-title">Twitter Card</h4>
                    
                    <div class="form-group">
                        {{ Form::label('twitter_card', 'twitter:card', ['class' => 'col-md-3 control-label']) }}
                        <div class="col-md-9">
                            {{ Form::text('twitter_card', $seo->twitter_card, [
                                'class' => 'form-control',
                                'placeholder' => 'summary, summary_large_image, etc.'
                            ]) }}
                        </div>
                    </div>
                    
                    <div class="form-group">
                        {{ Form::label('twitter_site', 'twitter:site', ['class' => 'col-md-3 control-label']) }}
                        <div class="col-md-9">
                            {{ Form::text('twitter_site', $seo->twitter_site, [
                                'class' => 'form-control',
                                'placeholder' => '@usuariotwitter'
                            ]) }}
                        </div>
                    </div>
                    
                    <div class="form-group">
                        {{ Form::label('twitter_creator', 'twitter:creator', ['class' => 'col-md-3 control-label']) }}
                        <div class="col-md-9">
                            {{ Form::text('twitter_creator', $seo->twitter_creator, [
                                'class' => 'form-control',
                                'placeholder' => '@creadorcontenido'
                            ]) }}
                        </div>
                    </div>
                    
                    {{-- Twitter Image --}}
                    <div class="form-group">
                        {{ Form::label('twitter_image', 'twitter:image', ['class' => 'col-md-3 control-label']) }}
                        <div class="col-md-9">
                            <div class="input-group">
                                {{ Form::text('twitter_image', $seo->twitter_image, [
                                    'class' => 'form-control',
                                    'id' => 'twitter_image_input',
                                    'placeholder' => 'URL de la imagen para Twitter',
                                    'readonly' => true
                                ]) }}
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button" onclick="openFileManager('twitter_image_input')">
                                        <i class="fa fa-folder-open"></i> Seleccionar
                                    </button>
                                </span>
                            </div>
                            <small class="help-text">Imagen para Twitter (1200x600px recomendado)</small>
                            
                            <div class="image-preview mt-2" id="twitter_image_preview" 
                                 style="{{ $seo->twitter_image ? '' : 'display: none;' }}">
                                @if($seo->twitter_image)
                                    <img src="{{ $seo->twitter_image }}" alt="Twitter Image Preview" 
                                         style="max-width: 200px; height: auto; border: 1px solid #ddd; padding: 5px; border-radius: 4px;">
                                    <div class="mt-1">
                                        <small class="text-muted">{{ $seo->twitter_image }}</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Favicons --}}
                <div class="seo-section">
                    <h4 class="section-title">Favicons</h4>
                    
                    <div class="form-group">
                        {{ Form::label('ico', 'Favicon ICO', ['class' => 'col-md-3 control-label']) }}
                        <div class="col-md-9">
                            <div class="input-group">
                                {{ Form::text('ico', $seo->ico, [
                                    'class' => 'form-control',
                                    'id' => 'ico_input',
                                    'placeholder' => 'URL del favicon .ico',
                                    'readonly' => true
                                ]) }}
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button" onclick="openFileManager('ico_input')">
                                        <i class="fa fa-folder-open"></i> Seleccionar
                                    </button>
                                </span>
                            </div>
                            <small class="help-text">Favicon tradicional .ico (32x32px)</small>
                            
                            <div class="image-preview mt-2" id="ico_preview" 
                                 style="{{ $seo->ico ? '' : 'display: none;' }}">
                                @if($seo->ico)
                                    <img src="{{ $seo->ico }}" alt="Favicon Preview" 
                                         style="max-width: 32px; height: auto; border: 1px solid #ddd; padding: 5px; border-radius: 4px;">
                                    <div class="mt-1">
                                        <small class="text-muted">{{ $seo->ico }}</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        {{ Form::label('icoapple', 'Apple Touch Icon', ['class' => 'col-md-3 control-label']) }}
                        <div class="col-md-9">
                            <div class="input-group">
                                {{ Form::text('icoapple', $seo->icoapple, [
                                    'class' => 'form-control',
                                    'id' => 'ico_apple_input',
                                    'placeholder' => 'URL del icono para Apple',
                                    'readonly' => true
                                ]) }}
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button" onclick="openFileManager('ico_apple_input')">
                                        <i class="fa fa-folder-open"></i> Seleccionar
                                    </button>
                                </span>
                            </div>
                            <small class="help-text">Icono para dispositivos Apple (180x180px)</small>
                            
                            <div class="image-preview mt-2" id="ico_apple_preview" 
                                 style="{{ $seo->icoapple ? '' : 'display: none;' }}">
                                @if($seo->icoapple)
                                    <img src="{{ $seo->icoapple }}" alt="Apple Icon Preview" 
                                         style="max-width: 60px; height: auto; border: 1px solid #ddd; padding: 5px; border-radius: 4px;">
                                    <div class="mt-1">
                                        <small class="text-muted">{{ $seo->icoapple }}</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Google Analytics --}}
                <div class="seo-section">
                    <h4 class="section-title">Google Analytics</h4>
                    
                    <div class="form-group">
                        {{ Form::label('analitica', 'Código de Seguimiento', ['class' => 'col-md-3 control-label']) }}
                        <div class="col-md-9">
                            {{ Form::textarea('analitica', $seo->analitica, [
                                'class' => 'form-control',
                                'placeholder' => 'Pega aquí el código de Google Analytics',
                                'rows' => 4
                            ]) }}
                        </div>
                    </div>
                </div>

                {{-- Google Ads --}}
                <div class="seo-section">
                    <h4 class="section-title">Google Ads</h4>
                    
                    <div class="form-group">
                        {{ Form::label('ads', 'Etiqueta de Seguimiento', ['class' => 'col-md-3 control-label']) }}
                        <div class="col-md-9">
                            {{ Form::textarea('ads', $seo->ads, [
                                'class' => 'form-control',
                                'placeholder' => 'Pega aquí el código de Google Ads',
                                'rows' => 4
                            ]) }}
                        </div>
                    </div>
                </div>

                {{-- Botones de acción --}}
                <div class="form-group form-actions">
                    <div class="col-md-9 col-md-offset-3">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fa fa-save"></i> Guardar todos los cambios
                        </button>
                        <button type="reset" class="btn btn-sm btn-warning">
                            <i class="fa fa-repeat"></i> Restablecer formulario
                        </button>
                    </div>
                </div>

                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery.bootstrapvalidator/0.5.0/js/bootstrapValidator.min.js"></script>
<script src="//cdn.datatables.net/1.10.1/js/jquery.dataTables.min.js"></script>
<script src="//cdn.datatables.net/plug-ins/be7019ee387/integration/bootstrap/3/dataTables.bootstrap.js"></script>
<script src="{{ asset('Usuario/js/valida.js') }}"></script>

<script>
// Función para abrir el file manager
function openFileManager(inputId) {
    const tenant = '{{ $tenantName ?? "default" }}';
    const url = `{{ url('/file-manager') }}?tenant=${tenant}`;
    
    // Guardar referencia al input actual
    window.currentInputId = inputId;
    
    // Abrir en ventana nueva
    const windowFeatures = 'width=1200,height=700,scrollbars=yes,resizable=yes';
    window.open(url, 'filemanager', windowFeatures);
}

// Función para que el file manager llame cuando se selecciona un archivo
function setFileUrl(url) {
    console.log('URL recibida:', url);
    console.log('Input actual:', window.currentInputId);
    
    const input = document.getElementById(window.currentInputId);
    if (input) {
        input.value = url;
        
        // Actualizar preview
        const previewId = input.id + '_preview';
        const preview = document.getElementById(previewId);
        if (preview) {
            preview.innerHTML = `
                <img src="${url}?t=${new Date().getTime()}" alt="Preview" 
                     style="max-width: 200px; height: auto; border: 1px solid #ddd; padding: 5px; border-radius: 4px;"
                     onerror="this.style.display='none'">
                <div class="mt-1">
                    <small class="text-success">✓ Vista previa actualizada</small>
                    <br>
                    <small class="text-muted">${url}</small>
                </div>
            `;
            preview.style.display = 'block';
        }
        
        // Mostrar notificación
        showNotification('Imagen seleccionada correctamente', 'success');
    } else {
        console.error('No se encontró el input:', window.currentInputId);
        showNotification('Error: No se pudo actualizar el campo', 'error');
    }
}

function showNotification(message, type = 'success') {
    // Crear notificación
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
    `;
    
    const icons = {
        success: '✓',
        error: '✗'
    };
    
    notification.innerHTML = `
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong>${icons[type] || 'ℹ'}</strong> ${message}
    `;
    
    document.body.appendChild(notification);
    
    // Auto-eliminar después de 3 segundos
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
}

// Inicializar tooltips si existen
$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
@stop


@push('styles')
<style>
    .seo-section {
        margin-bottom: 2rem;
        padding: 1.5rem;
        border: 1px solid #e5e5e5;
        border-radius: 4px;
        background: #f9f9f9;
    }

    .section-title {
        color: #333;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #007bff;
        font-size: 1.1rem;
        font-weight: 600;
    }

    .form-actions {
        margin-top: 1.5rem;
        padding-top: 1rem;
        border-top: 1px solid #ddd;
        text-align: right;
    }

    .help-text {
        font-size: 0.875rem;
        color: #6c757d;
        margin-top: 0.25rem;
        display: block;
    }

    .input-group-btn .btn {
        border-left: 0;
        background: #f8f9fa;
        border: 1px solid #ced4da;
    }

    .input-group-btn .btn:hover {
        background: #e9ecef;
        border-color: #adb5bd;
    }

    .image-preview {
        transition: all 0.3s ease;
    }

    .image-preview img {
        transition: transform 0.3s ease;
        border: 2px solid #dee2e6;
    }

    .image-preview img:hover {
        transform: scale(1.05);
        border-color: #007bff;
    }
</style>
@endpush