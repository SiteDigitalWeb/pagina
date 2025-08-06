<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Vista Previa: {{ $template->name }}</title>

    {{-- Incluir estilos y scripts externos --}}
    @include($web->template . '.assets-web.assets-preview')

    {{-- Estilos personalizados inyectados desde GrapesJS --}}
      <link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"
/>
    <style>
        {!! $styles !!}

        .empty-content {
            padding: 2rem;
            text-align: center;
            color: #666;
            font-size: 1.2rem;
        }

        .editor-actions {
            text-align: center;
            margin: 2rem 0;
        }

        .editor-actions a {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background: #2196F3;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }

        

        /* Aquí se mantienen tus estilos personalizados como los del carrusel, collapses, etc. */
        /* Los puedes dejar tal como los tenías o separarlos en otro archivo si lo deseas */

        .collapse-accordion { width: 100%; font-family: Arial, sans-serif; }
        .collapse-item { margin-bottom: 8px; border: 1px solid #ddd; border-radius: 4px; overflow: hidden; }
        .collapse-toggle { padding: 12px 15px; background: #f5f5f5; cursor: pointer; display: flex; justify-content: space-between; align-items: center; font-weight: bold; }
        .collapse-content { display: none; padding: 15px; background: #fff; }
        .collapse-item.active .collapse-content { display: block; }
        .add-collapse-btn { margin-top: 10px; padding: 8px 12px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; display: none; }

        .slides-selector-container { background-color: #f8f9fa; padding: 10px; border-radius: 5px; }
        .gjs-traits label { margin-bottom: 5px; font-weight: normal; }
        .form-control-sm { height: calc(1.5em + 0.5rem + 2px); padding: 0.25rem 0.5rem; font-size: 0.875rem; margin-bottom: 8px; }
        .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.875rem; margin-bottom: 8px; }
        .delete-slide-btn { background-color: #dc3545; border-color: #dc3545; }
        .delete-slide-btn:hover { background-color: #bb2d3b; border-color: #b02a37; }

        .fa-images:before { content: "\\f302"; font-family: 'Font Awesome 5 Free'; font-weight: 900; }

        .carousel-transition-slide-over .carousel-item { transition: transform 1s ease-in-out; }
        .carousel-transition-slide-over .carousel-item-next.carousel-item-start,
        .carousel-transition-slide-over .carousel-item-prev.carousel-item-end { transform: translateX(0); }
        .carousel-transition-slide-over .carousel-item-next,
        .carousel-transition-slide-over .active.carousel-item-end { transform: translateX(100%); }
        .carousel-transition-slide-over .carousel-item-prev,
        .carousel-transition-slide-over .active.carousel-item-start { transform: translateX(-100%); }

        .carousel-transition-zoom .carousel-item { transition: transform 1s ease, opacity .5s ease; }
        .carousel-transition-zoom .carousel-item-next,
        .carousel-transition-zoom .carousel-item-prev,
        .carousel-transition-zoom .carousel-item.active { transform: scale(1); opacity: 1; }
        .carousel-transition-zoom .carousel-item-next.carousel-item-start,
        .carousel-transition-zoom .carousel-item-prev.carousel-item-end { transform: scale(0); opacity: 0; }
        .carousel-transition-zoom .carousel-item-next,
        .carousel-transition-zoom .active.carousel-item-end { transform: scale(0.8); opacity: 0; }
        .carousel-transition-zoom .carousel-item-prev,
        .carousel-transition-zoom .active.carousel-item-start { transform: scale(1.2); opacity: 0; }

        .carousel-transition-flip .carousel-inner { perspective: 1000px; }
        .carousel-transition-flip .carousel-item { transition: transform 1s ease; backface-visibility: hidden; }
        .carousel-transition-flip .carousel-item-next,
        .carousel-transition-flip .carousel-item-prev,
        .carousel-transition-flip .carousel-item.active { transform: rotateY(0deg); }
        .carousel-transition-flip .carousel-item-next.carousel-item-start,
        .carousel-transition-flip .carousel-item-prev.carousel-item-end { transform: rotateY(0deg); }
        .carousel-transition-flip .carousel-item-next,
        .carousel-transition-flip .active.carousel-item-end { transform: rotateY(180deg); }
        .carousel-transition-flip .carousel-item-prev,
        .carousel-transition-flip .active.carousel-item-start { transform: rotateY(-180deg); }
    </style>
</head>
<body>

    @if(session('success'))
  <div id="customSuccessModal" class="modern-modal-overlay">
    <div class="modern-modal">
      <div class="modern-modal-icon">
        ✅
      </div>
      <h2 class="modern-modal-title">¡Mensaje enviado!</h2>
      <p class="modern-modal-text">{{ session('success') }}</p>
      <button class="modern-modal-button" onclick="closeCustomModal()">Cerrar</button>
    </div>
  </div>

  <style>
    .modern-modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.4);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      animation: fadeIn 0.3s ease;
    }

    .modern-modal {
      background: #fff;
      border-radius: 16px;
      padding: 30px 25px;
      max-width: 400px;
      text-align: center;
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
      animation: slideIn 0.4s ease;
      position: relative;
    }

    .modern-modal-icon {
      font-size: 48px;
      margin-bottom: 10px;
      color: #2ecc71;
    }

    .modern-modal-title {
      font-size: 22px;
      margin-bottom: 10px;
      color: #333;
    }

    .modern-modal-text {
      font-size: 16px;
      color: #666;
      margin-bottom: 20px;
    }

    .modern-modal-button {
      background: #2ecc71;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 8px;
      cursor: pointer;
      transition: background 0.3s;
    }

    .modern-modal-button:hover {
      background: #27ae60;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    @keyframes slideIn {
      from {
        transform: translateY(-30px);
        opacity: 0;
      }
      to {
        transform: translateY(0);
        opacity: 1;
      }
    }
  </style>

  <script>
    function closeCustomModal() {
      const modal = document.getElementById('customSuccessModal');
      if (modal) modal.remove();
    }

    // Auto cerrar después de 4 segundos
    setTimeout(closeCustomModal, 4000);
  </script>
@endif


    {{-- Contenido generado por el editor GrapesJS --}}
    @if(!empty($content))
        {!! $content !!}
    @else
        <div class="empty-content">
            Esta plantilla no contiene ningún contenido aún.
        </div>
    @endif

    <div class="editor-actions">
        <a href="{{ route('editor') }}?load={{ $template->id }}">
            Editar esta plantilla
        </a>
    </div>

       {{-- Scripts inyectados desde GrapesJS --}}
    <script>
        {!! $scripts !!}
    </script>

    {{-- Scripts externos --}}
    @stack('scripts')

    {{-- Scripts inyectados desde GrapesJS --}}
     <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://www.google.com/recaptcha/api.js?render={{ $recaptcha->site_key }}"></script>
<script>
    grecaptcha.ready(function() {
        grecaptcha.execute('{{ $recaptcha->site_key }}', {action: 'submit'}).then(function(token) {
            document.getElementById('recaptcha_token').value = token;
        });
    });
</script>
<input type="hidden" name="recaptcha_token" id="recaptcha_token">


</body>
</html>

