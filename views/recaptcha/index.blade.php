@extends('adminsite.layout')

@section('cabecera')
    @parent
@stop

@section('ContenidoSite-01')

<div class="content-header">
    <ul class="nav-horizontal text-center">
        <li><a href="/gestor/ver-templates"><i class="fa fa-desktop"></i> Ver templates</a></li>
        <li><a href="/gestion/logo-head"><i class="fa fa-arrow-circle-up"></i> Logo encabezado</a></li>
        <li><a href="/gestion/logo-footer"><i class="fa fa-arrow-circle-down"></i> Logo pie página</a></li>
        <li class="active"><a href="/gestion/configurar-correo"><i class="fa fa-envelope"></i> Configurar correo</a></li>
        <li><a href="/gestion/redes-sociales"><i class="hi hi-bullhorn"></i> Redes sociales</a></li>
        @if(Auth::user()->id == 1)
            <li><a href="/gestion/venta"><i class="gi gi-usd"></i> Ventas</a></li>
        @endif
    </ul>
</div>

<div class="container topper">
    @if(Session::get('status') == 'ok_update')
        <div class="alert alert-warning">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <strong>Configuración de REcaptcha actualizada correctamente</strong> CMS ...
        </div>
    @endif
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <!-- Formulario de configuración reCAPTCHA -->
            <div class="block">
                <div class="block-title">
                    <h2><strong>Configurar</strong> reCAPTCHA V3</h2>
                </div>

                {{ Form::model($settings, [
                'route' => ['recaptcha.update', $settings->id],
                'method' => 'PUT',
                'class' => 'form-horizontal',
                'id' => 'defaultForm'
                ]) }}

                    <div class="form-group">
                        <label class="col-md-3 control-label" for="publickey">Public key</label>
                        <div class="col-md-9">
                            {{ Form::text('publickey', $settings->site_key ?? '', [
                                'class' => 'form-control',
                                'placeholder' => 'Ingrese public key'
                            ]) }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label" for="privatekey">Private key</label>
                        <div class="col-md-9">
                            {{ Form::text('privatekey', $settings->secret_key ?? '', [
                                'class' => 'form-control',
                                'placeholder' => 'Ingrese private key'
                            ]) }}
                        </div>
                    </div>

                    {{ Form::hidden('id', $settings->id ?? 1) }}

                    <div class="form-group form-actions">
                        <div class="col-md-9 col-md-offset-3">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fa fa-angle-right"></i> Editar
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
