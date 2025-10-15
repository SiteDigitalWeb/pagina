@extends('adminsite.layout')

@section('ContenidoSite-01')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <!-- Block -->
            <div class="block">
                <div class="block-title">
                    <div class="block-options pull-right">
                        <a href="{{ route('utm.create') }}" class="btn btn-sm btn-success">
                            <i class="fa fa-plus"></i> Crear nueva UTM
                        </a>
                    </div>
                    <h2><strong>UTMs</strong> Generadas</h2>
                </div>

                <div class="block-content">
                    @if($utms->isEmpty())
                        <p class="text-muted">No hay UTMs generadas todavía.</p>
                    @else
                        <ul class="list-group">
                            @foreach ($utms as $utm)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $utm->campaign_name }}</strong><br>
                                        <input type="text" 
                                               id="utm-{{ $utm->id }}" 
                                               class="form-control mt-2" 
                                               value="{{ $utm->final_url }}" 
                                               readonly>
                                    </div>
                                    <div class="ms-3">
                                        <button class="btn btn-sm btn-primary" onclick="copyToClipboard('utm-{{ $utm->id }}')">
                                            <i class="fa fa-copy"></i> Copiar URL
                                        </button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
            <!-- END Block -->
        </div>
    </div>
</div>

<script>
    function copyToClipboard(id) {
        var copyText = document.getElementById(id);
        copyText.select();
        copyText.setSelectionRange(0, 99999); 
        document.execCommand("copy");
        // Notificación simple
        alert("Copiado al portapapeles: " + copyText.value);
    }
</script>
@endsection

