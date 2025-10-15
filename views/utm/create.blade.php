@extends('adminsite.layout')
@section('ContenidoSite-01')

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <!-- UTM Form Block -->
            <div class="block">
                <!-- Block Title -->
                <div class="block-title">
                    <div class="block-options pull-right">
                        <a href="javascript:void(0)" 
                           class="btn btn-alt btn-sm btn-default toggle-bordered enable-tooltip" 
                           data-toggle="button" 
                           title="Toggles .form-bordered class">
                            No Borders
                        </a>
                    </div>
                    <h2><strong>Crear</strong> UTM</h2>
                </div>
                <!-- END Block Title -->

                <!-- Block Content -->
                <form action="{{ route('utm.store') }}" method="POST" class="form-horizontal" id="utmForm">
                    @csrf

                    <div class="form-group">
                        <label class="col-md-3 control-label">Nombre de campaña</label>
                        <div class="col-md-9">
                            <input type="text" name="campaign_name" class="form-control" placeholder="Ej: Promoción Navidad" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Fuente (utm_source)</label>
                        <div class="col-md-9">
                            <input type="text" name="source" class="form-control" placeholder="Ej: facebook" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Medio (utm_medium)</label>
                        <div class="col-md-9">
                            <input type="text" name="medium" class="form-control" placeholder="Ej: cpc" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Término (utm_term)</label>
                        <div class="col-md-9">
                            <input type="text" name="term" class="form-control" placeholder="Ej: descuento zapato">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Contenido (utm_content)</label>
                        <div class="col-md-9">
                            <input type="text" name="content" class="form-control" placeholder="Ej: banner rojo">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">URL base</label>
                        <div class="col-md-9">
                            <input type="url" name="final_url" class="form-control" placeholder="https://tusitio.com/pagina" required>
                        </div>
                    </div>

                    <div class="form-group form-actions">
                        <div class="col-md-9 col-md-offset-3">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fa fa-link"></i> Generar UTM
                            </button>
                            <button type="reset" class="btn btn-sm btn-warning">
                                <i class="fa fa-repeat"></i> Reset
                            </button>
                        </div>
                    </div>
                </form>
                <!-- END Block Content -->
            </div>
            <!-- END UTM Form Block -->
        </div>
    </div>
</div>

@endsection
