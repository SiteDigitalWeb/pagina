@extends('adminsite.layout')
@section('ContenidoSite-01')
<div class="container">
    <h2>UTMs Generadas</h2>
    <a href="{{ route('utm.create') }}">Crear nueva UTM</a>
    <ul>
        @foreach ($utms as $utm)
            <li>
                <strong>{{ $utm->campaign_name }}</strong><br>
                <input type="text" id="utm-{{ $utm->id }}" value="{{ $utm->final_url }}" readonly>
                <button onclick="copyToClipboard('utm-{{ $utm->id }}')">Copiar</button>
            </li>
        @endforeach
    </ul>
</div>

<script>
    function copyToClipboard(id) {
        var copyText = document.getElementById(id);
        copyText.select();
        copyText.setSelectionRange(0, 99999); 
        document.execCommand("copy");
        alert("Copiado: " + copyText.value);
    }
</script>
@endsection
