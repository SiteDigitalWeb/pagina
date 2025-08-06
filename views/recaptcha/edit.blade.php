<h2>{{ isset($recaptcha) ? 'Editar' : 'Nueva' }} Configuración</h2>

<form action="{{ route('recaptcha.update', $recaptcha->id) }}" method="POST">
    @csrf
    @if(isset($recaptcha))
        @method('PUT')
    @endif

    <label>Site Key</label>
    <input type="text" name="site_key" value="{{ old('site_key', $recaptcha->site_key ?? '') }}">

    <label>Secret Key</label>
    <input type="text" name="secret_key" value="{{ old('secret_key', $recaptcha->secret_key ?? '') }}">

    <button type="submit">{{ isset($recaptcha) ? 'Actualizar' : 'Guardar' }}</button>
</form>

