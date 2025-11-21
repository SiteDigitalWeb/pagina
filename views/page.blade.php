<!DOCTYPE html>
<html lang="{{ $seo_web->idioma ?? 'es' }}">
<head>
    <!-- CONFIGURACIÓN BÁSICA -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- IDIOMA -->
    <meta http-equiv="content-language" content="{{ $seo_web->idioma ?? 'es' }}">

    <!-- SEGURIDAD -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- COLOR NAVEGADOR PARA MÓVILES -->
    <meta name="theme-color" content="#ffffff">

    <!-- SEO PRINCIPAL -->
    <title>{{ $seo['title'] ?? $template->title ?? 'Sitio Web' }}</title>

    <meta name="description" content="{{ $seo['description'] ?? $template->description }}">
    
    {{-- Meta Keywords (opcionales, Google no los usa, pero no afectan) --}}
    @if(!empty($seo['keywords']) || !empty($template->keywords))
        <meta name="keywords" content="{{ $seo['keywords'] ?? $template->keywords }}">
    @endif

    <meta name="robots" content="{{ $seo_web->robots ?? 'index, follow' }}">
    <meta name="author" content="{{ $seo_web->og_name ?? 'Renault Plan Rombo' }}">
    <meta name="generator" content="SiteCms">

    <!-- CANONICAL -->
    <link rel="canonical" href="{{ rtrim($seo_web->canonical ?? ($seo['url'] ?? url()->current()), '/') }}">

    <!-- FAVICONS -->
    @if($seo_web->ico)
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset($seo_web->ico) }}">
    @else
        <link rel="icon" type="image/png" sizes="32x32" href="/saas/eY48VJ1Dkx/ico_rnr.png">
    @endif

    @if($seo_web->icoapple)
        <link rel="apple-touch-icon" href="{{ asset($seo_web->icoapple) }}">
    @endif

    <!-- OPEN GRAPH (FACEBOOK / WHATSAPP) -->
    <meta property="og:type" content="{{ $seo_web->og_type ?? 'website' }}">
    <meta property="og:url" content="{{ $seo_web->og_url ?? ($seo['url'] ?? url()->current()) }}">
    <meta property="og:title" content="{{ $seo_web->og_title ?? ($seo['title'] ?? $template->title) }}">
    <meta property="og:description" content="{{ $seo_web->og_description ?? ($seo['description'] ?? $template->description) }}">
    
    <meta property="og:locale" content="{{ ($seo_web->idioma ?? 'es') === 'es' ? 'es_ES' : 'es_LA' }}">

    @if($seo_web->og_image)
        <meta property="og:image" content="{{ asset($seo_web->og_image) }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
    @endif

    @if($seo_web->og_name)
        <meta property="og:site_name" content="{{ $seo_web->og_name }}">
    @endif

    <!-- TWITTER CARDS -->
    <meta name="twitter:card" content="{{ $seo_web->twitter_card ?? 'summary_large_image' }}">
    @if($seo_web->twitter_site)
        <meta name="twitter:site" content="{{ $seo_web->twitter_site }}">
    @endif
    @if($seo_web->twitter_creator)
        <meta name="twitter:creator" content="{{ $seo_web->twitter_creator }}">
    @endif
    <meta name="twitter:title" content="{{ $seo_web->twitter_title ?? ($seo['title'] ?? $template->title) }}">
    <meta name="twitter:description" content="{{ $seo_web->twitter_description ?? ($seo['description'] ?? $template->description) }}">
    @if($seo_web->twitter_image)
        <meta name="twitter:image" content="{{ asset($seo_web->twitter_image) }}">
    @endif

    <!-- PRECONNECT para rendimiento -->
    <link rel="preconnect" href="https://www.google.com">
    <link rel="preconnect" href="https://www.gstatic.com" crossorigin>

    <!-- PRELOAD CSS PRINCIPAL -->
    <link rel="preload" href="/partials/estilos/estiloscomponentes.css" as="style">

    <!-- ESTILOS -->
    @include($web->template.'.assets-web.assets-preview')
    
    <link rel="stylesheet" href="/partials/estilos/estiloscomponentes.css"/>

    <style>{!! $styles !!}</style>

    <!-- DATOS ESTRUCTURADOS (SCHEMA ORG) -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebSite",
      "name": "{{ $seo_web->og_name ?? ($seo['title'] ?? $template->title) }}",
      "url": "{{ $seo_web->og_url ?? url()->current() }}"
    }
    </script>

     @include('genericos.estadistica')
     @include('genericos.mensaje')

</head>

    @include($web->template.'.pages.menu')

<body>

<!-- CONTENIDO -->
@if(!empty($content))
    {!! $content !!}
@else
    <div class="empty-content">
        Esta plantilla no contiene ningún contenido aún.
    </div>
@endif

<!-- SCRIPTS -->
<script defer>{!! $scripts !!}</script>

<script defer src="/partials/scripts/testimonios.js"></script>
<script defer src="/partials/scripts/tabs.js"></script>
<script defer src="/partials/scripts/acordeon.js"></script>
<script defer src="/partials/scripts/slider.js"></script>
<script defer src="/partials/scripts/whatsapp-multi.js"></script>
<script defer src="/partials/scripts/modal.js"></script>
<script defer src="/partials/scripts/whatsapp.js"></script>

<!-- SCRIPTS DE TERCEROS (CON defer PARA SEO + RENDIMIENTO) -->
<script defer src="https://www.google.com/recaptcha/api.js?render={{ $recaptcha->site_key }}"></script>
<script>
 grecaptcha.ready(function() {
 grecaptcha.execute('{{ $recaptcha->site_key }}', {action: 'submit'}).then(function(token) {
  document.getElementById('recaptcha_token').value = token;
 });
});
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById('contact_form');

    // Crear input oculto para CSRF Token
    const csrfTokenInput = document.createElement('input');
    csrfTokenInput.type = 'hidden';
    csrfTokenInput.name = '_token';
    csrfTokenInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    form.appendChild(csrfTokenInput);

    // Crear input oculto para reCAPTCHA Token
    const recaptchaTokenInput = document.createElement('input');
    recaptchaTokenInput.type = 'hidden';
    recaptchaTokenInput.name = 'recaptcha_token';
    recaptchaTokenInput.id = 'recaptcha_token';
    form.appendChild(recaptchaTokenInput);

    // Generar token reCAPTCHA al enviar el formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Evita envío inmediato
        grecaptcha.ready(function() {
            grecaptcha.execute('{{ $recaptcha->site_key }}', {action: 'submit'}).then(function(token) {
                document.getElementById('recaptcha_token').value = token;
                form.submit(); // Envía el formulario después de insertar el token
            });
        });
    });
});
</script>

@stack('scripts')

</body>
</html>