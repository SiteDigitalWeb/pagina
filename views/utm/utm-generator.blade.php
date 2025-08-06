@extends('adminsite.layout')
@section('ContenidoSite-01')
<div class="container">
    <h2>Generador de Enlaces UTM</h2>
    <form id="utm-form">
        <div class="form-group">
            <label>URL base *</label>
            <input type="url" class="form-control" id="base_url" placeholder="https://tusitio.com/pagina" required>
        </div>

        <div class="form-group">
            <label>UTM Source *</label>
            <input type="text" class="form-control" id="utm_source" placeholder="Ej: facebook" required>
        </div>

        <div class="form-group">
            <label>UTM Medium *</label>
            <input type="text" class="form-control" id="utm_medium" placeholder="Ej: cpc" required>
        </div>

        <div class="form-group">
            <label>UTM Campaign *</label>
            <input type="text" class="form-control" id="utm_campaign" placeholder="Ej: navidad2025" required>
        </div>

        <div class="form-group">
            <label>UTM Term</label>
            <input type="text" class="form-control" id="utm_term" placeholder="Ej: descuento zapato">
        </div>

        <div class="form-group">
            <label>UTM Content</label>
            <input type="text" class="form-control" id="utm_content" placeholder="Ej: banner rojo">
        </div>

        <button type="submit" class="btn btn-primary mt-3">Generar URL</button>
    </form>

    <div id="result" class="mt-4" style="display:none;">
        <label>URL Generada:</label>
        <div class="input-group">
            <input type="text" class="form-control" id="utm_result_url" readonly>
            <button class="btn btn-secondary" onclick="copyToClipboard()">Copiar</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('utm-form').addEventListener('submit', function (e) {
    e.preventDefault();

    const base = document.getElementById('base_url').value.trim();
    const source = document.getElementById('utm_source').value.trim();
    const medium = document.getElementById('utm_medium').value.trim();
    const campaign = document.getElementById('utm_campaign').value.trim();
    const term = document.getElementById('utm_term').value.trim();
    const content = document.getElementById('utm_content').value.trim();

    const params = new URLSearchParams({
        utm_source: source,
        utm_medium: medium,
        utm_campaign: campaign
    });

    if (term) params.append('utm_term', term);
    if (content) params.append('utm_content', content);

    const separator = base.includes('?') ? '&' : '?';
    const fullURL = base + separator + params.toString();

    document.getElementById('utm_result_url').value = fullURL;
    document.getElementById('result').style.display = 'block';
});

function copyToClipboard() {
    const input = document.getElementById('utm_result_url');
    input.select();
    input.setSelectionRange(0, 99999);
    document.execCommand("copy");
    alert("URL copiada al portapapeles");
}
</script>
@endpush