@extends('adminsite.layout')

@section('ContenidoSite-01')
<style>
    .invalid-feedback { color: #dc3545; display: block; margin-top: 5px; }
    .is-invalid { border-color: #dc3545; }
</style>

<div class="content-header">
    <ul class="nav-horizontal text-center">
        <li><a href="/sd/pages"><i class="fa fa-file-text"></i> Ver páginas</a></li>
        <li><a href="/sd/create-page"><i class="fa fa-file-o"></i> Crear página</a></li>
        <li class="active"><i class="fa fa-edit"></i> Editar página</li>
    </ul>
</div>

@if(session('status'))
<div class="col-xs-10 col-sm-10 col-md-10 col-lg-10 col-lg-offset-1 col-md-offset-1 topper">
    @if(session('status') == 'ok_create')
        <div class="alert alert-success">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <strong>{{ session('message') }}</strong>
        </div>
    @endif
    @if(session('status') == 'ok_update')
        <div class="alert alert-info">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <strong>{{ session('message') }}</strong>
        </div>
    @endif
    @if(session('status') == 'ok_delete')
        <div class="alert alert-warning">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <strong>{{ session('message') }}</strong>
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
                    <h2><strong>Editar</strong> página</h2>
                </div>

                <form method="POST"
                      action="{{ url('sd/pages/' . $page->id) }}"
                      class="form-horizontal"
                      id="editPageForm">
                    @csrf
                    @method('PUT')

                    {{-- Nombre página --}}
                    <div class="form-group">
                        <label class="col-md-3 control-label">Nombre página</label>
                        <div class="col-md-9">
                            <input type="text" name="page" id="page"
                                value="{{ old('page', $page->page) }}"
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
                                value="{{ old('slug', $page->slugcon ?? $page->slug) }}"
                                class="form-control {{ $errors->has('slug') ? 'is-invalid' : '' }}"
                                placeholder="Ingrese URL de la página (ej: mi-pagina)"
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
                                value="{{ old('title', $page->title) }}"
                                class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}"
                                placeholder="Título que aparecerá en el navegador"
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
                                value="{{ old('keywords', $page->keywords) }}"
                                class="form-control {{ $errors->has('keywords') ? 'is-invalid' : '' }}"
                                placeholder="Palabras clave separadas por comas"
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
                                placeholder="Descripción para motores de búsqueda"
                                maxlength="159">{{ old('description', $page->description) }}</textarea>
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
                                    <input type="radio" name="menu_type" id="menu_type1" value="1"
                                        {{ old('menu_type', $page->menu_type) == '1' ? 'checked' : '' }}>
                                    Menú principal
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="menu_type" id="menu_type2" value="2"
                                        {{ old('menu_type', $page->menu_type) == '2' ? 'checked' : '' }}>
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
                                value="{{ old('position', $page->position) }}"
                                class="form-control {{ $errors->has('position') ? 'is-invalid' : '' }}"
                                min="1" required>
                            @error('position')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Visibilidad --}}
                    <div class="form-group">
                        <label class="col-md-3 control-label">Visibilidad</label>
                        <div class="col-md-9">
                            <select name="visibility" id="visibility"
                                class="form-control {{ $errors->has('visibility') ? 'is-invalid' : '' }}">
                                <option value="1" {{ old('visibility', $page->visibility) == '1' ? 'selected' : '' }}>Publicado</option>
                                <option value="0" {{ old('visibility', $page->visibility) == '0' ? 'selected' : '' }}>No publicado</option>
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
                                <option value="1" {{ old('visibility_ecommerce', $page->visibility_ecommerce) == '1' ? 'selected' : '' }}>Visible</option>
                                <option value="0" {{ old('visibility_ecommerce', $page->visibility_ecommerce) == '0' ? 'selected' : '' }}>No visible</option>
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
                                <option value="1" {{ old('visibility_blog', $page->visibility_blog) == '1' ? 'selected' : '' }}>Visible</option>
                                <option value="0" {{ old('visibility_blog', $page->visibility_blog) == '0' ? 'selected' : '' }}>No visible</option>
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
                                <option value="ne" {{ old('language', $page->language) == 'ne' ? 'selected' : '' }}>Neutro</option>
                                <option value="es" {{ old('language', $page->language) == 'es' ? 'selected' : '' }}>Español</option>
                                <option value="en" {{ old('language', $page->language) == 'en' ? 'selected' : '' }}>Inglés</option>
                                <option value="fr" {{ old('language', $page->language) == 'fr' ? 'selected' : '' }}>Francés</option>
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
                                placeholder="Código de seguimiento (ej: UA-XXXXX-Y)">{{ old('follow', $page->follow) }}</textarea>
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
                                placeholder="Código del pixel de Facebook">{{ old('pixel', $page->pixel) }}</textarea>
                            @error('pixel')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Hidden page_id --}}
                    @if($page->page_id)
                        <input type="hidden" name="page_id" value="{{ $page->page_id }}">
                    @endif

                    {{-- Botones --}}
                    <div class="form-group form-actions">
                        <div class="col-md-9 col-md-offset-3">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fa fa-save"></i> Actualizar
                            </button>
                            <a href="/sd/pages" class="btn btn-sm btn-default">
                                <i class="fa fa-arrow-left"></i> Volver
                            </a>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection

