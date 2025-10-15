
@extends('adminsite.layout')

@section('cabecera')
    @parent
@stop

@section('ContenidoSite-01')


<div class="block full">
    <div class="block-title">
        <h2><strong>Crear</strong> Template</h2>
    </div>

    {{ Form::open(['route' => 'templates.stores', 'files' => true, 'class' => 'form-horizontal']) }}

                    {{-- Nombre del template --}}
                    <div class="form-group">
                        <label class="col-md-3 control-label">Nombre template</label>
                        <div class="col-md-9">
                            {{ Form::text('template', old('template'), [
                                'class' => 'form-control' . ($errors->has('template') ? ' is-invalid' : ''),
                                'placeholder' => 'Ingrese nombre del template',
                                'required'
                            ]) }}
                            @if($errors->has('template'))
                                <div class="invalid-feedback">{{ $errors->first('template') }}</div>
                            @endif
                        </div>
                    </div>

                    {{-- Descripción --}}
                    <div class="form-group">
                        <label class="col-md-3 control-label">Descripción</label>
                        <div class="col-md-9">
                            {{ Form::text('description', old('description'), [
                                'class' => 'form-control' . ($errors->has('description') ? ' is-invalid' : ''),
                                'placeholder' => 'Ingrese una descripción',
                                'required'
                            ]) }}
                            @if($errors->has('description'))
                                <div class="invalid-feedback">{{ $errors->first('description') }}</div>
                            @endif
                        </div>
                    </div>

                    {{-- Imagen con previsualización --}}
<div class="form-group">
    <label class="col-md-3 control-label">Imagen</label>
    <div class="col-md-9">
        <input type="file" name="image" id="image" 
               class="form-control {{ $errors->has('image') ? 'is-invalid' : '' }}"
               accept="image/*" onchange="previewImage(event)">
        <img id="imagePreview" src="#" alt="Vista previa" style="display:none; margin-top:10px; max-height:150px;">
        @if($errors->has('image'))
            <div class="invalid-feedback">{{ $errors->first('image') }}</div>
        @endif
    </div>
</div>

                    {{-- URL --}}
                    <div class="form-group">
                        <label class="col-md-3 control-label">URL</label>
                        <div class="col-md-9">
                            {{ Form::text('url', old('url'), [
                                'class' => 'form-control' . ($errors->has('url') ? ' is-invalid' : ''),
                                'placeholder' => 'Ingrese URL',
                            ]) }}
                            @if($errors->has('url'))
                                <div class="invalid-feedback">{{ $errors->first('url') }}</div>
                            @endif
                        </div>
                    </div>

                    {{-- Botones --}}
                    <div class="form-group form-actions">
                        <div class="col-md-9 col-md-offset-3">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fa fa-save"></i> Guardar
                            </button>
                            <button type="reset" class="btn btn-sm btn-warning">
                                <i class="fa fa-repeat"></i> Cancelar
                            </button>
                        </div>
                    </div>

                    {{ Form::close() }}
</div>

<script>
function previewImage(event) {
    const input = event.target;
    const preview = document.getElementById('imagePreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.src = "#";
        preview.style.display = 'none';
    }
}

function clearPreview() {
    const preview = document.getElementById('imagePreview');
    preview.src = "#";
    preview.style.display = 'none';
}
</script>





@stop

@section('scripts')
@parent

@stop