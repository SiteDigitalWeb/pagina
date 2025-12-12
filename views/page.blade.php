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
    
    {{-- Meta Keywords (opcionales) --}}
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

    <!-- OPEN GRAPH -->
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

    <!-- PRECONNECT -->
    <link rel="preconnect" href="https://www.google.com">
    <link rel="preconnect" href="https://www.gstatic.com" crossorigin>

    <!-- PRELOAD CSS PRINCIPAL -->
    <link rel="preload" href="/partials/estilos/estiloscomponentes.css" as="style">

    <!-- ESTILOS -->
    @include($web->template.'.assets-web.assets-preview')
    <link rel="stylesheet" href="/partials/estilos/estiloscomponentes.css"/>

    <style>
    {!! $styles !!}
    /* Estilos para campanita y toasts */
    .notif-bell {
        position: fixed;
        top: 12px;
        right: 18px;
        z-index: 1200;
        cursor: pointer;
        display:flex;
        align-items:center;
        gap:8px;
    }
    .notif-bell .badge-count {
        background:#dc3545;
        color:white;
        font-weight:600;
        border-radius:50%;
        padding:3px 7px;
        font-size:12px;
        line-height:1;
    }
    .toast-container {
        position: fixed;
        top: 72px;
        right: 18px;
        z-index: 1300;
        display: flex;
        flex-direction: column;
        gap:10px;
    }
    .toast-item {
        min-width: 280px;
        max-width: 360px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 6px 18px rgba(0,0,0,0.12);
        padding: 12px 14px;
        font-family: Arial, sans-serif;
        cursor: pointer;
    }
    .toast-item h5 { margin:0 0 6px 0; font-size:14px; }
    .toast-item p { margin:0; font-size:13px; color:#333; }
    /* panel de notificaciones (historial) */
    #notificaciones-panel {
        position: fixed;
        top: 56px;
        right: 72px;
        width: 360px;
        max-height: 70vh;
        overflow:auto;
        z-index: 1250;
        background: #fff;
        border: 1px solid #e6e6e6;
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        border-radius: 8px;
        display:none;
    }
    #notificaciones-panel .panel-header {
        padding:10px 12px; border-bottom:1px solid #f0f0f0; font-weight:600;
    }
    #notificaciones-panel .panel-body { padding:10px; }
    #notificaciones-panel .notif-row { padding:8px; border-bottom:1px dashed #eee; cursor:pointer; }
    </style>

    <!-- DATOS ESTRUCTURADOS -->
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

    <!-- CSRF meta ya incluido -->
</head>

    @include($web->template.'.pages.menu')

<body>

<!-- CAMPANITA (OPCION 1: arriba derecha) -->
<div class="notif-bell" id="notifBell" title="Notificaciones">
    <button id="bellBtn" class="btn btn-light" style="border-radius:50%; padding:8px;">
        <i class="fas fa-bell"></i>
    </button>
    <span id="bellCount" class="badge-count" style="display:none">0</span>
</div>

<!-- Panel historial -->
<div id="notificaciones-panel" aria-hidden="true">
    <div class="panel-header">
        🔔 Notificaciones
        <button id="clearNotifs" class="btn btn-sm btn-link" style="float:right; font-size:13px;">Limpiar</button>
    </div>
    <div class="panel-body" id="panelBody">
        <!-- filas de notificaciones -->
    </div>
</div>

<!-- Contenedor de toasts -->
<div class="toast-container" id="toastContainer" aria-live="polite" aria-atomic="true"></div>

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
<script src="/partials/scripts/slider.js"></script>
<script defer src="/partials/scripts/whatsapp-multi.js"></script>
<script defer src="/partials/scripts/modal.js"></script>
<script defer src="/partials/scripts/whatsapp.js"></script>

<!-- SCRIPTS DE TERCEROS -->
<script defer src="https://www.google.com/recaptcha/api.js?render={{ $recaptcha->site_key }}"></script>
<script>
 grecaptcha.ready(function() {
 grecaptcha.execute('{{ $recaptcha->site_key }}', {action: 'submit'}).then(function(token) {
  document.getElementById('recaptcha_token').value = token;
 });
});
</script>

<!-- ---------- PUSH + UI SCRIPT (registrar SW, suscribir, mostrar UI) ---------- -->
<script>
(function(){
    // CONFIG
    const VAPID_PUBLIC_KEY = "BEbMwz2XorkUlj2xWgKhpeQDwcS9r2i6VdAN4cmwqO0olqp2YxJqE2_wxhEMCgPSxgcKslqv6PpXd96fyCN5zOY";
    const swPath = '/service-worker.js'; // ajusta si tu SW está en otra ruta (antes usabas /service-worker.js)

    // DOM
    const bellBtn = document.getElementById('bellBtn');
    const bellCount = document.getElementById('bellCount');
    const notifBell = document.getElementById('notifBell');
    const toastContainer = document.getElementById('toastContainer');
    const panel = document.getElementById('notificaciones-panel');
    const panelBody = document.getElementById('panelBody');
    const clearNotifsBtn = document.getElementById('clearNotifs');

    // IndexedDB (guardar historial)
    let db;
    const req = indexedDB.open('notificacionesDB', 1);
    req.onupgradeneeded = (e) => {
        db = e.target.result;
        if (!db.objectStoreNames.contains('notifs')) {
            db.createObjectStore('notifs', { keyPath: 'id', autoIncrement: true });
        }
    };
    req.onsuccess = (e) => {
        db = e.target.result;
        renderPanel();
        updateBellCount();
    };
    req.onerror = (e) => console.error('IndexedDB error', e);

    function saveNotifToDB(n) {
        if (!db) return;
        const tx = db.transaction(['notifs'], 'readwrite');
        tx.objectStore('notifs').add(n);
        tx.oncomplete = () => { renderPanel(); updateBellCount(); };
    }
    function getAllNotifs(cb) {
        if (!db) return cb([]);
        const tx = db.transaction(['notifs'], 'readonly');
        const store = tx.objectStore('notifs');
        const items = [];
        store.openCursor().onsuccess = (e) => {
            const cursor = e.target.result;
            if (cursor) {
                items.push(cursor.value);
                cursor.continue();
            } else {
                cb(items.reverse()); // más reciente primero
            }
        };
    }
    function clearAllNotifs() {
        if (!db) return;
        const tx = db.transaction(['notifs'], 'readwrite');
        tx.objectStore('notifs').clear();
        tx.oncomplete = () => { renderPanel(); updateBellCount(); };
    }

    function renderPanel() {
        getAllNotifs((items) => {
            panelBody.innerHTML = '';
            if (!items.length) {
                panelBody.innerHTML = '<div class="text-muted">No hay notificaciones</div>';
                return;
            }
            items.forEach(item => {
                const row = document.createElement('div');
                row.className = 'notif-row';
                row.innerHTML = `<div><strong>${escapeHtml(item.title)}</strong></div>
                                 <div style="font-size:13px;color:#666">${escapeHtml(item.body)}</div>
                                 <div style="font-size:11px;color:#999;margin-top:6px">${new Date(item.timestamp).toLocaleString()}</div>`;
                row.addEventListener('click', () => { if (item.url) window.location.href = item.url; });
                panelBody.appendChild(row);
            });
        });
    }

    function updateBellCount() {
        getAllNotifs((items) => {
            const count = items.length;
            if (count > 0) {
                bellCount.style.display = 'inline-block';
                bellCount.textContent = count;
            } else {
                bellCount.style.display = 'none';
            }
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>"']/g, function(m) {
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m];
        });
    }

    // Mostrar toast breve
    function showToast(notificationData) {
        const item = document.createElement('div');
        item.className = 'toast-item';
        item.innerHTML = `<h5>${escapeHtml(notificationData.title)}</h5><p>${escapeHtml(notificationData.body)}</p>`;
        item.addEventListener('click', () => {
            if (notificationData.url) window.location.href = notificationData.url;
        });
        toastContainer.prepend(item);
        // auto eliminar
        setTimeout(() => {
            item.remove();
        }, 8000);
    }

    // Registrar service worker
    async function registerSW() {
        if (!('serviceWorker' in navigator)) return null;
        try {
            const reg = await navigator.serviceWorker.register(swPath);
            console.log('SW registrado:', reg.scope);
            return reg;
        } catch (err) {
            console.error('Error registrando SW:', err);
            return null;
        }
    }

    // Convert VAPID key
    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        return Uint8Array.from([...rawData].map(c => c.charCodeAt(0)));
    }

    // Subscribe push and send to server
    async function subscribePush() {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            alert('Push no es compatible en este navegador.');
            return;
        }

        const reg = await navigator.serviceWorker.ready;
        let sub = await reg.pushManager.getSubscription();
        if (!sub) {
            const permission = await Notification.requestPermission();
            if (permission !== 'granted') {
                alert('Permisos de notificación no concedidos.');
                return;
            }
            try {
                sub = await reg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY)
                });
            } catch (err) {
                console.error('Error subcribiendo:', err);
                alert('No se pudo suscribir al push.');
                return;
            }
        }

        // Enviar al servidor
        const rawKey = sub.getKey ? sub.getKey('p256dh') : null;
        const rawAuth = sub.getKey ? sub.getKey('auth') : null;
        const key = rawKey ? btoa(String.fromCharCode.apply(null, new Uint8Array(rawKey))) : null;
        const auth = rawAuth ? btoa(String.fromCharCode.apply(null, new Uint8Array(rawAuth))) : null;

        const payload = {
            endpoint: sub.endpoint,
            keys: {
                p256dh: key,
                auth: auth
            }
        };

        try {
            const resp = await fetch('/push-subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            });
            if (!resp.ok) {
                const t = await resp.text();
                console.error('Error guardando suscripción:', resp.status, t);
                alert('Error guardando suscripción en servidor. Revisa logs.');
                return;
            }
            const json = await resp.json();
            console.log('Suscripción guardada:', json);
            // Optionally show UI feedback
            showToast({ title: 'Suscripción activa', body: 'Recibirás notificaciones' });
        } catch (err) {
            console.error('Error guardando suscripción:', err);
        }
    }

    // Toggle panel
    notifBell.addEventListener('click', (e) => {
        e.stopPropagation();
        panel.style.display = (panel.style.display === 'none' || panel.style.display === '') ? 'block' : 'none';
    });
    // Close panel clicking outside
    document.addEventListener('click', (e) => {
        if (!panel.contains(e.target) && !notifBell.contains(e.target)) {
            panel.style.display = 'none';
        }
    });

    clearNotifsBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        clearAllNotifs();
    });

    // Listen messages from SW
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.addEventListener('message', (event) => {
            const payload = event.data || {};
            // Expected shapes:
            // { type: 'NEW_PUSH', data: {title, body, url, timestamp} }
            // { type: 'NOTIFICATION_OPENED', data: {...} }
            if (payload.type === 'NEW_PUSH' && payload.data) {
                saveNotifToDB(payload.data);
                showToast(payload.data);
            } else if (payload.type === 'NOTIFICATION_OPENED' && payload.data) {
                // push opened - maybe focus UI or mark read
                saveNotifToDB(Object.assign({}, payload.data, { opened: true }));
            } else if (payload.title && payload.body) {
                // fallback if SW posts raw notification object
                const d = { title: payload.title, body: payload.body, url: payload.url || '/', timestamp: Date.now() };
                saveNotifToDB(d);
                showToast(d);
            }
        });
    }

    // initialize
    (async function init(){
        await registerSW();
        // ensure we update UI from DB
        renderPanel();
        updateBellCount();
        // Optionally auto-subscribe if you want (commented)
        //document.getElementById('bellBtn').addEventListener('click', subscribePush);
        // Provide global function
        window.subscribePush = subscribePush;
    })();

    // Expose helper to send test push via fetch (optional)
    window.sendTestPush = async function() {
        try {
            await fetch('/test-push', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }});
            alert('Solicitud de push enviada al servidor (revisa logs).');
        } catch(e){ console.error(e); alert('Error enviando test push'); }
    };
})();
</script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Seleccionar TODOS los formularios de la página
    const forms = document.querySelectorAll('form');
   
    forms.forEach((form, index) => {
        // Crear input oculto para CSRF Token
        const csrfTokenInput = document.createElement('input');
        csrfTokenInput.type = 'hidden';
        csrfTokenInput.name = '_token';
        csrfTokenInput.value = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        form.appendChild(csrfTokenInput);

        // Crear input oculto para reCAPTCHA Token con ID único
        const recaptchaTokenInput = document.createElement('input');
        recaptchaTokenInput.type = 'hidden';
        recaptchaTokenInput.name = 'recaptcha_token';
        recaptchaTokenInput.id = 'recaptcha_token_' + index + '_' + Date.now();
        form.appendChild(recaptchaTokenInput);

        // Generar token reCAPTCHA al enviar el formulario
        form.addEventListener('submit', function(e) {
            e.preventDefault();
           
            if (typeof grecaptcha === 'undefined') {
                console.error('reCAPTCHA no está cargado');
                return;
            }
           
            grecaptcha.ready(function() {
                grecaptcha.execute('{{ $recaptcha->site_key }}', {action: 'submit'}).then(function(token) {
                    document.getElementById(recaptchaTokenInput.id).value = token;
                    form.submit();
                }).catch(function(error) {
                    console.error('Error con reCAPTCHA en formulario ' + index + ':', error);
                });
            });
        });
    });
});
</script>

<script>
    // 🔥 Redirigir a /hola SOLO si está en modo PWA instalada
function isInStandaloneMode() {
    return (window.matchMedia('(display-mode: standalone)').matches ||
            window.navigator.standalone || 
            document.referrer.includes('android-app://'));
}

if (isInStandaloneMode()) {
    if (location.pathname === '/') {
        location.replace('/hola');
    }
}
</script>
@stack('scripts')

</body>
</html>
