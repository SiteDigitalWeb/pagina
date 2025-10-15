

@extends('adminsite.layout')

@section('cabecera')
    @parent
@stop

@section('ContenidoSite-01')

<div class="block full">
    <div class="block-title">
        <h2><strong>Editar</strong> Template</h2>
    </div>

    {{ Form::model($template, ['route' => ['templates.update', $template->id], 'files' => true, 'class' => 'form-horizontal', 'method' => 'PUT']) }}

        {{-- Nombre del template --}}
        <div class="form-group">
            <label class="col-md-3 control-label">Nombre template</label>
            <div class="col-md-9">
                {{ Form::text('template', null, [
                    'class' => 'form-control' . ($errors->has('template') ? ' is-invalid' : ''),
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
                {{ Form::text('description', null, [
                    'class' => 'form-control' . ($errors->has('description') ? ' is-invalid' : ''),
                    'required'
                ]) }}
                @if($errors->has('description'))
                    <div class="invalid-feedback">{{ $errors->first('description') }}</div>
                @endif
            </div>
        </div>

        {{-- Imagen --}}
        <div class="form-group">
            <label class="col-md-3 control-label">Imagen</label>
            <div class="col-md-9">
                <input type="file" name="image" id="image" class="form-control" accept="image/*" onchange="previewImage(event)">
                <br>
                <img id="preview" src="{{ $template->image ? asset($template->image) : '#' }}" alt="Vista previa" style="max-height:150px; {{ $template->image ? '' : 'display:none;' }}">
            </div>
        </div>

        {{-- URL --}}
        <div class="form-group">
            <label class="col-md-3 control-label">URL</label>
            <div class="col-md-9">
                {{ Form::text('url', null, ['class' => 'form-control']) }}
            </div>
        </div>

        {{-- Botones --}}
        <div class="form-group form-actions">
            <div class="col-md-9 col-md-offset-3">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fa fa-save"></i> Actualizar
                </button>
                <a href="{{ route('sd.templates') }}" class="btn btn-sm btn-warning">
                    <i class="fa fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>

    {{ Form::close() }}
</div>

<script>
function previewImage(event) {
    const input = event.target;
    const preview = document.getElementById('preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>




@stop

@section('scripts')
@parent

@stop


