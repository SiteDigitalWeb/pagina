
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
                
                {{ Form::open([
                    'method' => 'POST',
                    'class' => 'form-horizontal',
                    'id' => 'pageForm',
                    'url' => 'sd/create-page'
                ]) }}
                
                <!-- Campos del formulario -->
                
         <div class="form-group">
    <label class="col-md-3 control-label">Nombre página</label>
    <div class="col-md-9">
        {{ Form::text('page', old('page'), [
            'class' => 'form-control' . ($errors->has('page') ? ' is-invalid' : ''),
            'placeholder' => 'Ingrese nombre de la página',
            'maxlength' => '50',
            'required',
            'id' => 'page'
        ]) }}
        @if($errors->has('page'))
            <div class="invalid-feedback">
                {{ $errors->first('page') }}
            </div>
        @endif
    </div>
</div>

<div class="form-group">
    <label class="col-md-3 control-label">URL Página</label>
    <div class="col-md-9">
        {{ Form::text('slug', old('slug'), [
            'class' => 'form-control' . ($errors->has('slug') ? ' is-invalid' : ''),
            'placeholder' => 'Ingrese URL de la página',
            'maxlength' => '50',
            'required',
            'id' => 'slug'
        ]) }}
        @if($errors->has('slug'))
            <div class="invalid-feedback">
                {{ $errors->first('slug') }}
            </div>
        @endif
    </div>
</div>

<div class="form-group">
    <label class="col-md-3 control-label">Título</label>
    <div class="col-md-9">
        {{ Form::text('title', old('title'), [
            'class' => 'form-control' . ($errors->has('title') ? ' is-invalid' : ''),
            'placeholder' => 'Ingrese título de la página',
            'maxlength' => '55',
            'required',
            'id' => 'title'
        ]) }}
        @if($errors->has('title'))
            <div class="invalid-feedback">
                {{ $errors->first('title') }}
            </div>
        @endif
    </div>
</div>

<div class="form-group">
    <label class="col-md-3 control-label">Palabras clave</label>
    <div class="col-md-9">
        {{ Form::text('keywords', old('keywords'), [
            'class' => 'form-control' . ($errors->has('keywords') ? ' is-invalid' : ''),
            'placeholder' => 'Ingrese palabras clave',
            'maxlength' => '150',
            'id' => 'keywords'
        ]) }}
        @if($errors->has('keywords'))
            <div class="invalid-feedback">
                {{ $errors->first('keywords') }}
            </div>
        @endif
    </div>
</div>

<div class="form-group">
    <label class="col-md-3 control-label">Descripción</label>
    <div class="col-md-9">
        {{ Form::textarea('description', old('description'), [
            'class' => 'form-control' . ($errors->has('description') ? ' is-invalid' : ''),
            'placeholder' => 'Ingrese descripción',
            'maxlength' => '159',
            'rows' => 3,
            'id' => 'description'
        ]) }}
        @if($errors->has('description'))
            <div class="invalid-feedback">
                {{ $errors->first('description') }}
            </div>
        @endif
    </div>
</div>

<div class="form-group">
    <label class="col-md-3 control-label">Tipo menú</label>
    <div class="col-md-9">
        <div class="radio">
            <label>
                {{ Form::radio('menu_type', '1', old('menu_type', '1') == '1', ['id' => 'menu_type1']) }}
                Menú principal
            </label>
        </div>
        <div class="radio">
            <label>
                {{ Form::radio('menu_type', '2', old('menu_type') == '2', ['id' => 'menu_type2']) }}
                Sub-menú
            </label>
        </div>
        @if($errors->has('menu_type'))
            <div class="invalid-feedback" style="display: block;">
                {{ $errors->first('menu_type') }}
            </div>
        @endif
    </div>
</div>

<div class="form-group">
    <label class="col-md-3 control-label">Posición</label>
    <div class="col-md-9">
        {{ Form::number('position', old('position', 1), [
            'class' => 'form-control' . ($errors->has('position') ? ' is-invalid' : ''),
            'min' => '1',
            'required',
            'id' => 'position'
        ]) }}
        @if($errors->has('position'))
            <div class="invalid-feedback">
                {{ $errors->first('position') }}
            </div>
        @endif
    </div>
</div>


        {{ Form::hidden('page_id', old('page_id', request()->segment(3)), [
            'class' => 'form-control' . ($errors->has('position') ? ' is-invalid' : ''),
            'min' => '1',
            'id' => 'page_id'
        ]) }}



<div class="form-group">
    <label class="col-md-3 control-label">Visibilidad</label>
    <div class="col-md-9">
        {{ Form::select('visibility', [
            '1' => 'Visible',
            '0' => 'No visible'
        ], old('visibility'), [
            'class' => 'form-control' . ($errors->has('visibility') ? ' is-invalid' : ''),
            'id' => 'visibility'
        ]) }}
        @if($errors->has('visibility'))
            <div class="invalid-feedback">
                {{ $errors->first('visibility') }}
            </div>
        @endif
    </div>
</div>

<div class="form-group">
    <label class="col-md-3 control-label">Visibilidad Ecommerce</label>
    <div class="col-md-9">
        {{ Form::select('visibility_ecommerce', [
            '1' => 'Visible',
            '0' => 'No visible'
        ], old('visibility_ecommerce'), [
            'class' => 'form-control' . ($errors->has('visibility_ecommerce') ? ' is-invalid' : ''),
            'id' => 'visibility_ecommerce'
        ]) }}
        @if($errors->has('visibility_ecommerce'))
            <div class="invalid-feedback">
                {{ $errors->first('visibility_ecommerce') }}
            </div>
        @endif
    </div>
</div>

<div class="form-group">
    <label class="col-md-3 control-label">Visibilidad Blog</label>
    <div class="col-md-9">
        {{ Form::select('visibility_blog', [
            '1' => 'Visible',
            '0' => 'No visible'
        ], old('visibility_blog'), [
            'class' => 'form-control' . ($errors->has('visibility_blog') ? ' is-invalid' : ''),
            'id' => 'visibility_blog'
        ]) }}
        @if($errors->has('visibility_blog'))
            <div class="invalid-feedback">
                {{ $errors->first('visibility_blog') }}
            </div>
        @endif
    </div>
</div>

<div class="form-group">
    <label class="col-md-3 control-label">Idioma</label>
    <div class="col-md-9">
        {{ Form::select('language', [
            'ne' => 'Neutro',
            'es' => 'Español',
            'en' => 'Inglés',
            'fr' => 'Francés'
        ], old('language'), [
            'class' => 'form-control' . ($errors->has('language') ? ' is-invalid' : ''),
            'required',
            'id' => 'language'
        ]) }}
        @if($errors->has('language'))
            <div class="invalid-feedback">
                {{ $errors->first('language') }}
            </div>
        @endif
    </div>
</div>

<div class="form-group">
    <label class="col-md-3 control-label">Código Google Analytics</label>
    <div class="col-md-9">
        {{ Form::textarea('follow', old('follow'), [
            'class' => 'form-control' . ($errors->has('follow') ? ' is-invalid' : ''),
            'placeholder' => 'Ingrese código de seguimiento',
            'rows' => 3,
            'id' => 'follow'
        ]) }}
        @if($errors->has('follow'))
            <div class="invalid-feedback">
                {{ $errors->first('follow') }}
            </div>
        @endif
    </div>
</div>

<div class="form-group">
    <label class="col-md-3 control-label">Pixel Facebook</label>
    <div class="col-md-9">
        {{ Form::textarea('pixel', old('pixel'), [
            'class' => 'form-control' . ($errors->has('pixel') ? ' is-invalid' : ''),
            'placeholder' => 'Ingrese código de pixel',
            'rows' => 3,
            'id' => 'pixel'
        ]) }}
        @if($errors->has('pixel'))
            <div class="invalid-feedback">
                {{ $errors->first('pixel') }}
            </div>
        @endif
    </div>
</div>
                
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
                {{ Form::close() }}
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