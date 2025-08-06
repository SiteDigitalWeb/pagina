@extends('adminsite.layout')
@section('ContenidoSite-01')
<div class="container">
    <h2>Crear UTM</h2>
    <form action="{{ route('utm.store') }}" method="POST">
        @csrf
        <input type="text" name="campaign_name" placeholder="Nombre de campaña" required><br>
        <input type="text" name="source" placeholder="Fuente (utm_source)" required><br>
        <input type="text" name="medium" placeholder="Medio (utm_medium)" required><br>
        <input type="text" name="term" placeholder="Término (utm_term)"><br>
        <input type="text" name="content" placeholder="Contenido (utm_content)"><br>
        <input type="url" name="final_url" placeholder="URL base" required><br>
        <button type="submit">Generar UTM</button>
    </form>
</div>
@endsection
