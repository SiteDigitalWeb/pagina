@extends('adminsite.layout')

@section('cabecera')
    @parent
@stop

@section('ContenidoSite-01')
<style>
    
    .invalid-feedback {
    color: #dc3545;
    display: block;
    margin-top: 5px;
}

.is-invalid {
    border-color: #dc3545;
}
</style>
<div class="content-header">
    <ul class="nav-horizontal text-center">
        <li><a href="/sd/pages"><i class="fa fa-file-text"></i> Ver páginas</a></li>
        <li class="active"><a href="/sd/create-page"><i class="fa fa-file-o"></i> Crear página</a></li>
    </ul>
</div>

@if(Session::has('status'))
<div class="col-xs-10 col-sm-10 col-md-10 col-lg-10 col-lg-offset-1 col-md-offset-1 col-sm-offset-1 col-xs-offset-1 topper">
    @if(Session::get('status') == 'ok_create')
      <div class="alert alert-success">
       <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
       <strong>Página registrada con éxito</strong>
      </div>
    @endif

    @if(Session::get('status') == 'ok_delete')
      <div class="alert alert-danger">
       <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
       <strong>Página eliminada con éxito</strong>
      </div>
    @endif

    @if(Session::get('status') == 'ok_update')
      <div class="alert alert-warning">
       <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
       <strong>Página actualizada con éxito</strong>
      </div>
    @endif
</div>
@endif

@if($errors->any())
<div class="alert alert-danger">
    <ul>
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="block">
                <div class="block-title">
                    <h2><strong>Crear</strong> página</h2>
                </div>
                
                <form method="POST" action="{{ url('sd/create-page') }}" class="form-horizontal" id="pageForm">
    @csrf

    {{-- Nombre página --}}
    <div class="form-group">
        <label class="col-md-3 control-label">Nombre página</label>
        <div class="col-md-9">
            <input type="text" name="page" id="page"
                value="{{ old('page') }}"
                class="form-control {{ $errors->has('page') ? 'is-invalid' : '' }}"
                placeholder="Ingrese nombre de la página"
                maxlength="50" required>
            @error('page')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- URL --}}
    <div class="form-group">
        <label class="col-md-3 control-label">URL Página</label>
        <div class="col-md-9">
            <input type="text" name="slug" id="slug"
                value="{{ old('slug') }}"
                class="form-control {{ $errors->has('slug') ? 'is-invalid' : '' }}"
                placeholder="Ingrese URL de la página"
                maxlength="50" required>
            @error('slug')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Título --}}
    <div class="form-group">
        <label class="col-md-3 control-label">Título</label>
        <div class="col-md-9">
            <input type="text" name="title" id="title"
                value="{{ old('title') }}"
                class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}"
                placeholder="Ingrese título de la página"
                maxlength="55" required>
            @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Palabras clave --}}
    <div class="form-group">
        <label class="col-md-3 control-label">Palabras clave</label>
        <div class="col-md-9">
            <input type="text" name="keywords" id="keywords"
                value="{{ old('keywords') }}"
                class="form-control {{ $errors->has('keywords') ? 'is-invalid' : '' }}"
                placeholder="Ingrese palabras clave"
                maxlength="150">
            @error('keywords')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Descripción --}}
    <div class="form-group">
        <label class="col-md-3 control-label">Descripción</label>
        <div class="col-md-9">
            <textarea name="description" id="description" rows="3"
                class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}"
                placeholder="Ingrese descripción"
                maxlength="159">{{ old('description') }}</textarea>
            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Tipo menú --}}
    <div class="form-group">
        <label class="col-md-3 control-label">Tipo menú</label>
        <div class="col-md-9">
            <div class="radio">
                <label>
                    <input type="radio" name="menu_type" id="menu_type1"
                        value="1" {{ old('menu_type', '1') == '1' ? 'checked' : '' }}>
                    Menú principal
                </label>
            </div>
            <div class="radio">
                <label>
                    <input type="radio" name="menu_type" id="menu_type2"
                        value="2" {{ old('menu_type') == '2' ? 'checked' : '' }}>
                    Sub-menú
                </label>
            </div>
            @error('menu_type')
                <div class="invalid-feedback" style="display:block">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Posición --}}
    <div class="form-group">
        <label class="col-md-3 control-label">Posición</label>
        <div class="col-md-9">
            <input type="number" name="position" id="position"
                value="{{ old('position', 1) }}"
                class="form-control {{ $errors->has('position') ? 'is-invalid' : '' }}"
                min="1" required>
            @error('position')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Hidden page_id --}}
    <input type="hidden" name="page_id" id="page_id"
        value="{{ old('page_id', request()->segment(3)) }}">

    {{-- Visibilidad --}}
    <div class="form-group">
        <label class="col-md-3 control-label">Visibilidad</label>
        <div class="col-md-9">
            <select name="visibility" id="visibility"
                class="form-control {{ $errors->has('visibility') ? 'is-invalid' : '' }}">
                <option value="1" {{ old('visibility') == '1' ? 'selected' : '' }}>Visible</option>
                <option value="0" {{ old('visibility') == '0' ? 'selected' : '' }}>No visible</option>
            </select>
            @error('visibility')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Visibilidad Ecommerce --}}
    <div class="form-group">
        <label class="col-md-3 control-label">Visibilidad Ecommerce</label>
        <div class="col-md-9">
            <select name="visibility_ecommerce" id="visibility_ecommerce"
                class="form-control {{ $errors->has('visibility_ecommerce') ? 'is-invalid' : '' }}">
                <option value="1" {{ old('visibility_ecommerce') == '1' ? 'selected' : '' }}>Visible</option>
                <option value="0" {{ old('visibility_ecommerce') == '0' ? 'selected' : '' }}>No visible</option>
            </select>
            @error('visibility_ecommerce')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Visibilidad Blog --}}
    <div class="form-group">
        <label class="col-md-3 control-label">Visibilidad Blog</label>
        <div class="col-md-9">
            <select name="visibility_blog" id="visibility_blog"
                class="form-control {{ $errors->has('visibility_blog') ? 'is-invalid' : '' }}">
                <option value="1" {{ old('visibility_blog') == '1' ? 'selected' : '' }}>Visible</option>
                <option value="0" {{ old('visibility_blog') == '0' ? 'selected' : '' }}>No visible</option>
            </select>
            @error('visibility_blog')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Idioma --}}
    <div class="form-group">
        <label class="col-md-3 control-label">Idioma</label>
        <div class="col-md-9">
            <select name="language" id="language" required
                class="form-control {{ $errors->has('language') ? 'is-invalid' : '' }}">
                <option value="ne" {{ old('language') == 'ne' ? 'selected' : '' }}>Neutro</option>
                <option value="es" {{ old('language') == 'es' ? 'selected' : '' }}>Español</option>
                <option value="en" {{ old('language') == 'en' ? 'selected' : '' }}>Inglés</option>
                <option value="fr" {{ old('language') == 'fr' ? 'selected' : '' }}>Francés</option>
            </select>
            @error('language')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Google Analytics --}}
    <div class="form-group">
        <label class="col-md-3 control-label">Código Google Analytics</label>
        <div class="col-md-9">
            <textarea name="follow" id="follow" rows="3"
                class="form-control {{ $errors->has('follow') ? 'is-invalid' : '' }}"
                placeholder="Ingrese código de seguimiento">{{ old('follow') }}</textarea>
            @error('follow')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Pixel Facebook --}}
    <div class="form-group">
        <label class="col-md-3 control-label">Pixel Facebook</label>
        <div class="col-md-9">
            <textarea name="pixel" id="pixel" rows="3"
                class="form-control {{ $errors->has('pixel') ? 'is-invalid' : '' }}"
                placeholder="Ingrese código de pixel">{{ old('pixel') }}</textarea>
            @error('pixel')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Botones --}}
    <div class="form-group form-actions">
        <div class="col-md-9 col-md-offset-3">
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="fa fa-angle-right"></i> Crear
            </button>
            <button type="reset" class="btn btn-sm btn-warning">
                <i class="fa fa-repeat"></i> Cancelar
            </button>
        </div>
    </div>

</form>
            </div>
        </div>
    </div>
</div>
@stop

@section('scripts')
@parent
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
<script src="{{ asset('js/pages/validation.js') }}"></script>
<script>
    $(document).ready(function() {
        // Validación del formulario
        $('#pageForm').validate({
            rules: {
                page: { required: true, maxlength: 50 },
                slug: { 
                    required: true, 
                    maxlength: 50,
                    remote: {
                        url: '/check-slug-unique',
                        type: 'post'
                    }
                },
                title: { required: true, maxlength: 55 },
                keywords: { maxlength: 150 },
                description: { maxlength: 159 },
                position: { required: true, min: 1 }
            },
            messages: {
                page: "El nombre de la página es requerido",
                slug: {
                    required: "La URL es requerida",
                    remote: "Esta URL ya está en uso"
                },
                title: "El título es requerido",
                position: "La posición debe ser al menos 1"
            }
        });

        // Generar slug automáticamente
        $('#page').on('blur', function() {
            let slug = $(this).val()
                .toLowerCase()
                .replace(/[^\w ]+/g, '')
                .replace(/ +/g, '-');
            $('#slug').val(slug);
        });
    });
</script>
@stop