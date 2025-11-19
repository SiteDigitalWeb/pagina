<?php
namespace Sitedigitalweb\Pagina\Http;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Sitedigitalweb\Pagina\Page;
use Sitedigitalweb\Pagina\Cms_Stadistics;
use Sitedigitalweb\Pagina\Cms_Recaptcha;
use Sitedigitalweb\Pagina\Cms_Template;
use Illuminate\Support\Facades\Schema;
use Sitedigitalweb\Pagina\Cms_theme;
use Sitedigitalweb\Pagina\Cms_variable;
use DB;
use File;


class TemplateController extends Controller
{
 
public function editor(Request $request)
{
    $templateId = $request->query('load');

    // Detectar si estamos en un tenant
    $website = app(\Hyn\Tenancy\Environment::class)->website();

    // Si estamos en tenant, simplemente usamos la base del tenant
    if ($website) {
        $web = \Sitedigitalweb\Pagina\Tenant\Cms_Template::first(); // Aquí no hay website_id
    } else {
        // En la base central, también usamos sin filtro
        $web = Cms_Template::first(); // O con filtro si tú creaste website_id
    }

    return view('pagina::pages.editor', compact('templateId', 'web'));
}



public function preview($id)
{
    if ($website = app(\Hyn\Tenancy\Environment::class)->website()) {
        // Entorno tenant específico
        $template = \Sitedigitalweb\Pagina\Tenant\Page::findOrFail($id);
        $recaptcha = \Sitedigitalweb\Pagina\Tenant\Cms_Recaptcha::first();
        $web = \Sitedigitalweb\Pagina\Tenant\Cms_Template::first();
        $menuPages = \Sitedigitalweb\Pagina\Tenant\Page::whereNull('page_id')
        ->where('visibility', 1)
        ->orderBy('position', 'asc')
        ->with(['children' => function ($query) {
            $query->where('visibility', 1)->orderBy('position', 'asc');
        }])
        ->get();
    } else {
        // Entorno central
        $template = Page::findOrFail($id);
        $recaptcha = Cms_Recaptcha::first();
        $web = Cms_Template::first();
        $menuPages = Page::whereNull('page_id')
        ->where('visibility', 1)
        ->orderBy('position', 'asc')
        ->with(['children' => function ($query) {
            $query->where('visibility', 1)->orderBy('position', 'asc');
        }])
        ->get();
    }

    $structure = is_string($template->content) ? json_decode($template->content, true) : $template->content;

    $content = $this->renderComponent($structure ?? []);
    $styles = $this->renderStyles($template->styles);
    $scripts = $this->renderScripts($template->scripts);

    $tenantData = [
        'is_tenant' => isset($website),
        'tenant_id' => $website->id ?? null,
        'tenant_name' => $website->name ?? null
    ];

    $seo = [
        'title'       => $template->title ?? $template->name,
        'description' => $template->description ?? Str::limit(strip_tags($template->content), 160),
        'keywords'    => $template->keywords ?? '',
        'url'         => url()->current(),
    ];

    // Aquí definimos la carpeta de plantilla desde BD
    $templateFolder = $web->template ?? 'default';

    // Render dinámico de la vista
    return view($templateFolder . '.pages.page', compact('template', 'content', 'styles', 'scripts', 'tenantData', 'recaptcha', 'web', 'menuPages','seo'));
}



public function page()
{
    if ($website = app(\Hyn\Tenancy\Environment::class)->website()) {
        // Entorno tenant específico, sin usar website_id
        $template = \Sitedigitalweb\Pagina\Tenant\Page::where('slug', '/')->firstOrFail();
        $recaptcha = \Sitedigitalweb\Pagina\Tenant\Cms_Recaptcha::first();
        $web = \Sitedigitalweb\Pagina\Tenant\Cms_Template::first();
        $menuPages = \Sitedigitalweb\Pagina\Tenant\Page::whereNull('page_id')
        ->where('visibility', 1)
        ->orderBy('position', 'asc')
        ->with(['children' => function ($query) {
            $query->where('visibility', 1)->orderBy('position', 'asc');
        }])
        ->get();
        
    } else {
        // Entorno central (host)
        $template = Page::where('slug', '/')->firstOrFail();
        $recaptcha = Cms_Recaptcha::first();
        $web = Cms_Template::first();
        $menuPages = Page::whereNull('page_id')
        ->where('visibility', 1)
        ->orderBy('position', 'asc')
        ->with(['children' => function ($query) {
            $query->where('visibility', 1)->orderBy('position', 'asc');
        }])
        ->get();
    }

    $structure = is_string($template->content) ? json_decode($template->content, true) : $template->content;

    $content = $this->renderComponent($structure ?? []);
    $styles = $this->renderStyles($template->styles);
    $scripts = $this->renderScripts($template->scripts);

    $tenantData = [
        'is_tenant' => isset($website),
        'tenant_id' => $website->id ?? null,
        'tenant_name' => $website->name ?? null
    ];

    $seo = [
        'title'       => $template->title ?? $template->name,
        'description' => $template->description ?? Str::limit(strip_tags($template->content), 160),
        'keywords'    => $template->keywords ?? '',
        'url'         => url()->current(),
    ];

    // Suponiendo que en cms_template tienes una columna 'template_name'
    $templateFolder = $web->template ?? 'default';

    return view($templateFolder . '.pages.page', compact('template', 'content', 'styles', 'scripts', 'tenantData', 'recaptcha', 'web', 'menuPages', 'seo'));

}


public function menu()
    {
        // Traemos páginas raíz con sus hijos
        $menuPages = \Sitedigitalweb\Pagina\Tenant\Page::whereNull('page_id')
            ->where('visibility', 1)
            ->orderBy('position', 'asc')
            ->with(['children' => function ($query) {
                $query->where('visibility', 1)->orderBy('position', 'asc');
            }])
            ->get();

        // Formateamos recursivamente
        $data = $menuPages->map(fn($page) => $this->transformPage($page));

        return response()->json($data);
    }

    private function transformPage($page)
    {
        return [
            'title' => $page->page,   // nombre de la página
            'url' => url($page->slug), // slug convertido en URL
            'children' => $page->children->map(fn($child) => $this->transformPage($child))->toArray()
        ];
    }

public function pages($page)
{
    if ($website = app(\Hyn\Tenancy\Environment::class)->website()) {
        // Entorno tenant específico, sin usar website_id
        $template = \Sitedigitalweb\Pagina\Tenant\Page::where('slug', $page)->firstOrFail();
        $recaptcha = \Sitedigitalweb\Pagina\Tenant\Cms_Recaptcha::first();
        $web = \Sitedigitalweb\Pagina\Tenant\Cms_Template::first();
        $menuPages = \Sitedigitalweb\Pagina\Tenant\Page::whereNull('page_id')
        ->where('visibility', 1)
        ->orderBy('position', 'asc')
        ->with(['children' => function ($query) {
            $query->where('visibility', 1)->orderBy('position', 'asc');
        }])
        ->get();
    } else {
        // Entorno central (host)
        $template = Page::where('slug', $page)->firstOrFail();
        $recaptcha = Cms_Recaptcha::first();
        $web = Cms_Template::first();
        $menuPages = Page::whereNull('page_id')
        ->where('visibility', 1)
        ->orderBy('position', 'asc')
        ->with(['children' => function ($query) {
            $query->where('visibility', 1)->orderBy('position', 'asc');
        }])
        ->get();
    }

    $structure = is_string($template->content) ? json_decode($template->content, true) : $template->content;

    $content = $this->renderComponent($structure ?? []);
    $styles = $this->renderStyles($template->styles);
    $scripts = $this->renderScripts($template->scripts);

    $tenantData = [
        'is_tenant' => isset($website),
        'tenant_id' => $website->id ?? null,
        'tenant_name' => $website->name ?? null
    ];

    $seo = [
        'title'       => $template->title ?? $template->name,
        'description' => $template->description ?? Str::limit(strip_tags($template->content), 160),
        'keywords'    => $template->keywords ?? '',
        'url'         => url()->current(),
    ];

    $templateFolder = $web->template ?? 'default';
    
      return view($templateFolder . '.pages.page', compact('template', 'content', 'styles', 'scripts', 'tenantData', 'recaptcha', 'web', 'menuPages','seo'));

}

private function renderScripts(array $scriptsArray): string
{
    $js = '';

    foreach ($scriptsArray as $scriptBlock) {
        if (!isset($scriptBlock['type'], $scriptBlock['componentType'], $scriptBlock['content'])) {
            continue; // Saltamos si faltan claves necesarias
        }

        // Podemos agregar comentarios para identificar el tipo de componente
        $js .= "/* {$scriptBlock['type']} - {$scriptBlock['componentType']} */\n";
        
        // Limpiamos y formateamos el contenido del script
        $content = trim($scriptBlock['content']);
        $content = preg_replace('/^\s*function\s*\(\s*\)\s*{/', '(function(){', $content);
        $content = preg_replace('/}\s*$/', '})', $content);
        
        // Aseguramos que termine con punto y coma
        if (!str_ends_with($content, ';')) {
            $content .= ';';
        }
        
        $js .= "{$content}\n\n";
    }

    return $js;
}

private function renderStyles(array $stylesArray): string
{
    $css = '';

    foreach ($stylesArray as $styleBlock) {
        if (
            !isset($styleBlock['selectors'], $styleBlock['style']) ||
            empty($styleBlock['selectors']) ||
            !is_array($styleBlock['style']) ||
            empty($styleBlock['style'])
        ) {
            continue;
        }

        $selectors = $styleBlock['selectors'];

        // Si el bloque tiene background-image y solo son clases → combinamos
        if (isset($styleBlock['style']['background-image']) && !empty($selectors)) {
            $allClassesArePlain = true;
            foreach ($selectors as $sel) {
                if (strpos($sel, '#') === 0) {
                    $allClassesArePlain = false;
                    break;
                }
            }
            if ($allClassesArePlain) {
                // Limpieza de slashes
                $styleBlock['style']['background-image'] = str_replace('\/', '/', $styleBlock['style']['background-image']);
                // Crear selector único con todas las clases juntas
                $selectors = ['.' . implode('.', $selectors)];
            }
        }

        $selectors = implode(', ', $selectors);

        $styleRules = '';
        foreach ($styleBlock['style'] as $property => $value) {
            if (stripos($property, 'background-image') !== false) {
                $value = str_replace('\/', '/', $value);
            }
            $styleRules .= "$property: $value; ";
        }

        $css .= "$selectors { $styleRules }\n";
    }

    return $css;
}





private function renderComponent($component)
{
    // Si es un array indexado, renderizar todos los componentes hijos
    if (is_array($component) && array_keys($component) === range(0, count($component) - 1)) {
        $html = '';
        foreach ($component as $child) {
            $html .= $this->renderComponent($child);
        }
        return $html;
    }

    // Ignorar nodos de tipo comentario o contenido que sea un comentario HTML
    if (
        ($component['type'] ?? '') === 'comment' ||
        str_starts_with(trim($component['content'] ?? ''), '<!--')
    ) {
        return ''; // No renderizar
    }

    // Si es un nodo de texto simple
    if (($component['type'] ?? '') === 'textnode') {
        return $component['content'] ?? '';
    }

    // Manejo específico para el brand-logo-slider
    if (($component['type'] ?? '') === 'brand-logo-slider') {
        return $this->renderBrandLogoSlider($component);
    }

    // Manejo específico para el css-slider (nuevo componente)
    if (($component['type'] ?? '') === 'css-slider') {
        return $this->renderCssSlider($component);
    }

    // Parámetros básicos
    $tag = $component['tagName'] ?? 'div';
    $content = $component['content'] ?? '';
    $attributes = $component['attributes'] ?? [];
    $components = $component['components'] ?? [];

    // Manejo específico para iframes (ej: Google Maps)
    if (
        ($component['type'] ?? '') === 'iframe' ||
        ($component['tagName'] ?? '') === 'iframe' ||
        ($attributes['data-gjs-type'] ?? '') === 'iframe'
    ) {
        $attrString = $this->buildAttributesString($attributes);
        return "<iframe {$attrString}></iframe>";
    }

    // Manejo específico para enlaces
    if (
        ($component['type'] ?? '') === 'link' ||
        ($component['tagName'] ?? '') === 'a'
    ) {
        $tag = 'a';
    }

    // Detectar si es una imagen
    $isImage = $this->isImageComponent($component, $attributes);

    if ($isImage) {
        $tag = 'img';
        $content = '';
        $components = [];
    }

    // Procesar clases personalizadas
    $processedClasses = $this->processComponentClasses($component, $attributes);

    // Manejo específico para carrusel Bootstrap
    if ($tag === 'div' && isset($attributes['data-bs-ride']) && $attributes['data-bs-ride'] === 'carousel') {
        $baseClasses = 'carousel';
        $transitionClass = '';

        switch ($attributes['transition'] ?? 'slide') {
            case 'fade':
                $transitionClass = 'carousel-fade';
                break;
            case 'slide-over':
                $transitionClass = 'carousel-transition-slide-over';
                break;
            case 'zoom':
                $transitionClass = 'carousel-transition-zoom';
                break;
            case 'flip':
                $transitionClass = 'carousel-transition-flip';
                break;
            default:
                $transitionClass = 'slide';
        }

        $finalClasses = trim("$baseClasses $transitionClass $processedClasses");
        $attributes['class'] = $finalClasses;

        $innerHtml = '';
        foreach ($components as $child) {
            $innerHtml .= $this->renderComponent($child);
        }

        return $this->buildFinalHtml($tag, $this->buildAttributesString($attributes), $innerHtml, $isImage);
    }

    // Asignar clases procesadas si no están definidas
    if (!empty($processedClasses)) {
        $attributes['class'] = isset($attributes['class'])
            ? trim($attributes['class'] . ' ' . $processedClasses)
            : $processedClasses;
    }

    // Construir string de atributos
    $attrString = $this->buildAttributesString($attributes);

    // Renderizar hijos
    $innerHtml = $content;
    if (!empty($components) && !$isImage) {
        foreach ($components as $child) {
            if (in_array($child['type'] ?? '', [
                'text-input', 'email-input', 'textarea-input',
                'select-input', 'number-input', 'submit-button', 'date-input'
            ])) {
                $innerHtml .= $this->renderInputComponent($child);
            } else {
                $innerHtml .= $this->renderComponent($child);
            }
        }
    }

    if (($component['type'] ?? '') === 'dynamic-form') {
        $tag = 'form';

        // Forzar method y action si no están definidos
        if (empty($attributes['method'])) {
            $attributes['method'] = 'post';
        }
        if (empty($attributes['action'])) {
            $attributes['action'] = '/cms/registro';
        }

        // Insertar el input hidden para funel_id
        $funelId = $component['funel_id'] ?? ($attributes['funel_id'] ?? null);
        if (!empty($funelId)) {
            $innerHtml = '<input type="hidden" name="funel_id" value="' . htmlspecialchars($funelId) . '">' . $innerHtml;
        }

        $emailDestino = $component['email_destino'] ?? ($attributes['email_destino'] ?? null);
        if (!empty($emailDestino)) {
            $innerHtml = '<input type="hidden" name="email_destino" value="' . htmlspecialchars($emailDestino) . '">' . $innerHtml;
        }

        $sujeto = $component['sujeto'] ?? ($attributes['sujeto'] ?? null);
        if (!empty($sujeto)) {
            $innerHtml = '<input type="hidden" name="sujeto" value="' . htmlspecialchars($sujeto) . '">' . $innerHtml;
        }
    }

    // Construir y retornar HTML final
    return $this->buildFinalHtml($tag, $attrString, $innerHtml, $isImage);
}

/**
 * Renderiza el componente css-slider con soporte para imágenes y videos
 */
/**
 * Renderiza el componente css-slider con soporte para imágenes y videos
 */
private function renderCssSlider($component)
{
    $attributes = $component['attributes'] ?? [];
    $components = $component['components'] ?? [];
    
    // Obtener datos de los slides desde las propiedades del componente
    $slidesData = [];
    if (isset($component['slidesData']) && is_string($component['slidesData'])) {
        try {
            $slidesData = json_decode($component['slidesData'], true);
        } catch (\Exception $e) {
            // En caso de error, usar datos por defecto
            $slidesData = [
                [
                    'id' => 'slide1',
                    'image' => 'https://picsum.photos/id/1005/1920/1080',
                    'video' => '',
                    'title' => 'Bienvenido a nuestro mundo visual',
                    'description' => 'Inspiración, creatividad y tecnología al servicio de tu marca.',
                    'buttonText' => 'Explorar más',
                    'buttonLink' => '#',
                    'mediaType' => 'image',
                    'videoAutoplay' => true,
                    'videoMuted' => true,
                    'videoLoop' => true
                ]
            ];
        }
    }

    // Obtener configuración del slider
    $animation = $component['animation'] ?? 'slide';
    $slideTiming = $component['slideTiming'] ?? '5000';
    $contentEnabled = $component['contentEnabled'] ?? true;

    // Construir atributos del slider principal
    $attributes['class'] = isset($attributes['class']) 
        ? $attributes['class'] . ' slider' 
        : 'slider';
    $attributes['data-animation'] = $animation;
    $attributes['data-timing'] = $slideTiming;
    $attributes['data-content-enabled'] = $contentEnabled ? 'true' : 'false';

    $attrString = $this->buildAttributesString($attributes);

    // Generar HTML de los slides
    $slidesHtml = '<div class="slides">';
    
    foreach ($slidesData as $index => $slide) {
        // Asegurar que todos los campos tengan valores por defecto
        $slide = $this->ensureSlideDefaults($slide);
        
        $isActive = $index === 0;
        $slideClass = $isActive ? 'slide active' : 'slide';
        
        // Determinar el estilo según la animación
        $slideStyle = '';
        if ($animation !== 'slide') {
            $slideStyle = 'position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: ' . 
                         ($isActive ? '1' : '0') . '; z-index: ' . ($isActive ? '2' : '1') . ';';
        } else {
            $slideStyle = 'position: relative; opacity: 1;';
        }

        // Contenido multimedia
        $mediaContent = '';
        if ($slide['mediaType'] === 'video' && !empty($slide['video'])) {
            // Verificar si es una URL de YouTube
            if ($this->isYouTubeUrl($slide['video'])) {
                $youtubeEmbedUrl = $this->convertToYouTubeEmbedUrl($slide['video']);
                $mediaContent = '
                    <div class="video-container">
                        <iframe 
                            class="youtube-video"
                            src="' . htmlspecialchars($youtubeEmbedUrl) . '"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                            loading="lazy"
                        ></iframe>
                        <div class="video-overlay"></div>
                    </div>
                ';
            } else {
                // Video normal (MP4, WebM, etc.)
                $mediaContent = '
                    <video 
                        class="slide-video" 
                        ' . ($slide['videoAutoplay'] ? 'autoplay' : '') . ' 
                        ' . ($slide['videoMuted'] ? 'muted' : '') . ' 
                        ' . ($slide['videoLoop'] ? 'loop' : '') . '
                        playsinline
                        preload="metadata"
                    >
                        <source src="' . htmlspecialchars($slide['video']) . '" type="video/mp4">
                        Tu navegador no soporta el elemento video.
                    </video>
                    <div class="video-overlay"></div>
                ';
            }
        } else {
            $mediaContent = '<img src="' . htmlspecialchars($slide['image']) . '" alt="' . htmlspecialchars($slide['title']) . '" class="slide-image">';
        }

        // Contenido del slide
        $slideContent = '';
        if ($contentEnabled) {
            $slideContent = '
                <div class="slide-content">
                    <h2>' . htmlspecialchars($slide['title']) . '</h2>
                    <p>' . htmlspecialchars($slide['description']) . '</p>
                    <a href="' . htmlspecialchars($slide['buttonLink']) . '">' . htmlspecialchars($slide['buttonText']) . '</a>
                </div>
            ';
        }

        $slidesHtml .= '
            <div class="' . $slideClass . '" data-slide-id="' . htmlspecialchars($slide['id']) . '" data-media-type="' . htmlspecialchars($slide['mediaType']) . '" style="' . $slideStyle . '">
                ' . $mediaContent . '
                ' . $slideContent . '
            </div>
        ';
    }
    
    $slidesHtml .= '</div>';

    // Controles de navegación
    $controlsHtml = '
        <button class="nav-arrow prev">❮</button>
        <button class="nav-arrow next">❯</button>
        <div class="dots">
    ';
    
    foreach ($slidesData as $index => $slide) {
        $controlsHtml .= '<span class="dot' . ($index === 0 ? ' active' : '') . '"></span>';
    }
    
    $controlsHtml .= '</div>';

    // CSS del slider
    $cssStyles = $this->getCssSliderStyles();

    // JavaScript del slider
    $jsScript = $this->getCssSliderScript();

    // HTML completo del slider
    $fullHtml = $cssStyles . $slidesHtml . $controlsHtml . $jsScript;

    return '<div ' . $attrString . '>' . $fullHtml . '</div>';
}

/**
 * Verifica si una URL es de YouTube
 */
private function isYouTubeUrl($url)
{
    $pattern = '/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.?be)\/.+$/';
    return preg_match($pattern, $url) === 1;
}

/**
 * Convierte una URL de YouTube a URL de embed
 */
private function convertToYouTubeEmbedUrl($url)
{
    // Extraer el ID del video de YouTube
    $videoId = '';
    
    // Patrón para URLs regulares de YouTube
    if (preg_match('/^(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([^&?\/\s]+)/', $url, $matches)) {
        $videoId = $matches[1];
    }
    
    // Si no se pudo extraer el ID, retornar URL original
    if (empty($videoId)) {
        return $url;
    }
    
    // Construir URL de embed con parámetros para autoplay silenciado
    $embedUrl = "https://www.youtube.com/embed/{$videoId}";
    $params = [
        'autoplay=1',
        'mute=1',
        'loop=1',
        'playlist=' . $videoId, // Necesario para loop
        'controls=0', // Ocultar controles
        'modestbranding=1', // Menos branding
        'rel=0', // No mostrar videos relacionados al final
        'enablejsapi=1' // Habilitar API de JavaScript
    ];
    
    return $embedUrl . '?' . implode('&', $params);
}

/**
 * Asegura que todos los campos del slide tengan valores por defecto
 */
private function ensureSlideDefaults($slide)
{
    return [
        'id' => $slide['id'] ?? 'slide1',
        'image' => $slide['image'] ?? 'https://picsum.photos/id/1005/1920/1080',
        'video' => $slide['video'] ?? '',
        'title' => $slide['title'] ?? 'Título del Slide',
        'description' => $slide['description'] ?? 'Descripción del slide...',
        'buttonText' => $slide['buttonText'] ?? 'Explorar más',
        'buttonLink' => $slide['buttonLink'] ?? '#',
        'mediaType' => $slide['mediaType'] ?? 'image',
        'videoAutoplay' => $slide['videoAutoplay'] ?? true,
        'videoMuted' => $slide['videoMuted'] ?? true,
        'videoLoop' => $slide['videoLoop'] ?? true
    ];
}


/**
 * Retorna los estilos CSS para el css-slider
 */
/**
 * Retorna los estilos CSS para el css-slider
 */
private function getCssSliderStyles()
{
    return '
    <style>
        .slider {
            position: relative;
            width: 100%;
            height: 100vh;
            overflow: hidden;
            font-family: system-ui, sans-serif;
        }

        .slides {
            display: flex;
            height: 100%;
            width: 100%;
            position: relative;
        }

        .slide {
            min-width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff;
            transition: all 0.8s ease-in-out;
        }

        .slide-image {
            position: absolute;
            width: 100%;
            height: 100%;
            object-fit: cover;
            top: 0;
            left: 0;
            z-index: 0;
        }

        .slide-video {
            position: absolute;
            width: 100%;
            height: 100%;
            object-fit: cover;
            top: 0;
            left: 0;
            z-index: 0;
        }

        .video-container {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 0;
            overflow: hidden;
        }

        .youtube-video {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border: none;
            z-index: 0;
        }

        /* Ajustar relación de aspecto para YouTube */
        @media (min-aspect-ratio: 16/9) {
            .youtube-video {
                width: 100%;
                height: auto;
                min-height: 100%;
            }
        }

        @media (max-aspect-ratio: 16/9) {
            .youtube-video {
                width: auto;
                height: 100%;
                min-width: 100%;
            }
        }

        .video-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3);
            z-index: 1;
        }

        .slide-content {
            position: relative;
            z-index: 2;
            background: rgba(0, 0, 0, 0.45);
            padding: 25px 40px;
            border-radius: 8px;
            text-align: center;
            max-width: 80%;
        }

        .slide-content h2 {
            margin: 0 0 10px;
            font-size: 2.5rem;
            font-weight: 700;
        }

        .slide-content p {
            font-size: 1.2rem;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .slide-content a {
            display: inline-block;
            padding: 10px 20px;
            background: #fff;
            color: #000;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.3s;
        }

        .slide-content a:hover {
            background: #e9e9e9;
        }

        .nav-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0,0,0,0.4);
            color: #fff;
            border: none;
            font-size: 2rem;
            cursor: pointer;
            padding: 10px 15px;
            border-radius: 50%;
            z-index: 10;
            transition: background 0.3s;
            font-family: inherit;
        }

        .nav-arrow:hover {
            background: rgba(0,0,0,0.6);
        }

        .prev { left: 25px; }
        .next { right: 25px; }

        .dots {
            position: absolute;
            bottom: 25px;
            width: 100%;
            text-align: center;
            z-index: 10;
        }

        .dot {
            display: inline-block;
            width: 14px;
            height: 14px;
            margin: 0 6px;
            background: rgba(255,255,255,0.6);
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s;
        }

        .dot.active {
            background: #fff;
            transform: scale(1.2);
        }

        /* ANIMACIONES */
        .slider[data-animation="slide"] .slides {
            transition: transform 0.8s ease-in-out;
            transform: translateX(0%);
        }

        .slider[data-animation="slide"] .slide {
            position: relative !important;
            opacity: 1 !important;
        }

        .slider[data-animation="fade"] .slides {
            position: relative;
            transform: none !important;
            transition: none !important;
        }
        
        .slider[data-animation="fade"] .slide {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            opacity: 0;
        }
        
        .slider[data-animation="fade"] .slide.active {
            opacity: 1 !important;
            z-index: 2 !important;
        }

        .slider[data-animation="zoom"] .slides {
            position: relative;
            transform: none !important;
            transition: none !important;
        }
        
        .slider[data-animation="zoom"] .slide {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            transform: scale(0.8) !important;
            opacity: 0;
        }
        
        .slider[data-animation="zoom"] .slide.active {
            transform: scale(1) !important;
            opacity: 1 !important;
            z-index: 2 !important;
        }

        .slider[data-animation="flip"] .slides {
            perspective: 1000px;
            transform: none !important;
            transition: none !important;
        }
        
        .slider[data-animation="flip"] .slide {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            transform: rotateY(180deg) !important;
            opacity: 0;
        }
        
        .slider[data-animation="flip"] .slide.active {
            transform: rotateY(0deg) !important;
            opacity: 1 !important;
            z-index: 2 !important;
        }

        .slider[data-animation="cube"] .slides {
            perspective: 1200px;
            transform-style: preserve-3d;
            transform: none !important;
            transition: none !important;
        }
        
        .slider[data-animation="cube"] .slide {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            transform: rotateY(90deg) translateZ(300px) !important;
            opacity: 0;
        }
        
        .slider[data-animation="cube"] .slide.active {
            transform: rotateY(0deg) translateZ(0) !important;
            opacity: 1 !important;
            z-index: 2 !important;
        }

        .slider[data-animation="coverflow"] .slides {
            perspective: 1000px;
            transform-style: preserve-3d;
            transform: none !important;
            transition: none !important;
        }
        
        .slider[data-animation="coverflow"] .slide {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            transform: rotateY(45deg) scale(0.8) !important;
            opacity: 0.5;
        }
        
        .slider[data-animation="coverflow"] .slide.active {
            transform: rotateY(0deg) scale(1) !important;
            opacity: 1 !important;
            z-index: 2 !important;
        }

        @media (max-width: 768px) {
            .slider {
                height: 70vh;
            }
            
            .slide-content {
                padding: 15px 25px;
                max-width: 90%;
            }
            
            .slide-content h2 {
                font-size: 1.6rem;
            }
            
            .slide-content p {
                font-size: 1rem;
            }
            
            .nav-arrow {
                font-size: 1.5rem;
                padding: 8px 12px;
            }
            
            .prev { left: 15px; }
            .next { right: 15px; }
            
            .dots {
                bottom: 15px;
            }

            .slider[data-animation] .slide {
                transform: none !important;
                opacity: 1 !important;
            }
            
            .slider[data-animation] .slide:not(.active) {
                display: none !important;
            }

            /* En móviles, desactivar autoplay de YouTube */
            .youtube-video {
                pointer-events: none;
            }
        }
    </style>
    ';
}

/**
 * Retorna el script JavaScript para el css-slider
 */
private function getCssSliderScript()
{
    return '
    <script>
        (function() {
            function initSlider() {
                const sliders = document.querySelectorAll(\'.slider\');
                
                sliders.forEach(function(slider) {
                    const slidesContainer = slider.querySelector(\'.slides\');
                    const slides = slider.querySelectorAll(\'.slide\');
                    const dots = slider.querySelectorAll(\'.dot\');
                    const prev = slider.querySelector(\'.prev\');
                    const next = slider.querySelector(\'.next\');
                    const animation = slider.getAttribute(\'data-animation\') || \'slide\';
                    
                    let slideTiming = parseInt(slider.getAttribute(\'data-timing\'));
                    if (isNaN(slideTiming) || slideTiming < 0) {
                        slideTiming = 5000;
                    }
                    
                    if (!slidesContainer || slides.length === 0) {
                        return;
                    }
                    
                    let currentIndex = 0;
                    let autoPlayInterval = null;
                    let lastSlideChange = Date.now();

                    function handleVideoPlayback(slide, shouldPlay) {
                        const video = slide.querySelector(\'.slide-video\');
                        if (!video) return;

                        try {
                            if (shouldPlay) {
                                const mediaType = slide.getAttribute(\'data-media-type\');
                                if (mediaType === \'video\') {
                                    video.currentTime = 0;
                                    const playPromise = video.play();
                                    if (playPromise !== undefined) {
                                        playPromise.catch(error => {
                                            console.log(\'Auto-play de video prevenido:\', error);
                                        });
                                    }
                                }
                            } else {
                                video.pause();
                                video.currentTime = 0;
                            }
                        } catch (error) {
                            console.log(\'Error controlando video:\', error);
                        }
                    }

                    function initializeAnimation() {
                        if (animation === \'slide\') {
                            slidesContainer.style.transform = \'translateX(0%)\';
                            slidesContainer.style.transition = \'transform 0.8s ease-in-out\';
                            slides.forEach((slide, i) => {
                                slide.style.position = \'relative\';
                                slide.style.opacity = \'1\';
                                slide.style.transform = \'none\';
                                slide.style.zIndex = \'\';
                                slide.classList.toggle(\'active\', i === 0);
                                handleVideoPlayback(slide, i === 0);
                            });
                        } else {
                            slidesContainer.style.transform = \'none\';
                            slidesContainer.style.transition = \'none\';
                            slides.forEach((slide, i) => {
                                slide.style.position = \'absolute\';
                                slide.style.top = \'0\';
                                slide.style.left = \'0\';
                                slide.style.width = \'100%\';
                                slide.style.height = \'100%\';
                                slide.style.zIndex = i === 0 ? \'2\' : \'1\';
                                slide.style.opacity = i === 0 ? \'1\' : \'0\';
                                slide.classList.toggle(\'active\', i === 0);
                                handleVideoPlayback(slide, i === 0);
                                
                                switch(animation) {
                                    case \'zoom\':
                                        slide.style.transform = i === 0 ? \'scale(1)\' : \'scale(0.8)\';
                                        break;
                                    case \'flip\':
                                        slide.style.transform = i === 0 ? \'rotateY(0deg)\' : \'rotateY(180deg)\';
                                        break;
                                    case \'cube\':
                                        slide.style.transform = i === 0 ? \'rotateY(0deg) translateZ(0)\' : \'rotateY(90deg) translateZ(300px)\';
                                        break;
                                    case \'coverflow\':
                                        slide.style.transform = i === 0 ? \'rotateY(0deg) scale(1)\' : \'rotateY(45deg) scale(0.8)\';
                                        break;
                                    default:
                                        slide.style.transform = \'none\';
                                }
                            });
                        }
                    }

                    function updateSlider(index) {
                        const currentSlide = slides[currentIndex];
                        if (currentSlide) {
                            handleVideoPlayback(currentSlide, false);
                        }
                        
                        if (animation === \'slide\') {
                            slidesContainer.style.transform = \`translateX(-\${index * 100}%)\`;
                            slides.forEach((slide, i) => {
                                slide.classList.toggle(\'active\', i === index);
                            });
                        } else {
                            slides.forEach((slide, i) => {
                                if (i === index) {
                                    slide.classList.add(\'active\');
                                    slide.style.opacity = \'1\';
                                    slide.style.zIndex = \'2\';
                                    
                                    switch(animation) {
                                        case \'zoom\':
                                            slide.style.transform = \'scale(1)\';
                                            break;
                                        case \'flip\':
                                            slide.style.transform = \'rotateY(0deg)\';
                                            break;
                                        case \'cube\':
                                            slide.style.transform = \'rotateY(0deg) translateZ(0)\';
                                            break;
                                        case \'coverflow\':
                                            slide.style.transform = \'rotateY(0deg) scale(1)\';
                                            break;
                                        default:
                                            slide.style.transform = \'none\';
                                    }
                                } else {
                                    slide.classList.remove(\'active\');
                                    slide.style.opacity = \'0\';
                                    slide.style.zIndex = \'1\';
                                    
                                    switch(animation) {
                                        case \'zoom\':
                                            slide.style.transform = \'scale(0.8)\';
                                            break;
                                        case \'flip\':
                                            slide.style.transform = \'rotateY(180deg)\';
                                            break;
                                        case \'cube\':
                                            slide.style.transform = \'rotateY(90deg) translateZ(300px)\';
                                            break;
                                        case \'coverflow\':
                                            slide.style.transform = \'rotateY(45deg) scale(0.8)\';
                                            break;
                                        default:
                                            slide.style.transform = \'none\';
                                    }
                                }
                            });
                        }
                        
                        const newSlide = slides[index];
                        if (newSlide) {
                            handleVideoPlayback(newSlide, true);
                        }
                        
                        dots.forEach((dot, i) => dot.classList.toggle(\'active\', i === index));
                        currentIndex = index;
                        lastSlideChange = Date.now();
                    }

                    function nextSlide() {
                        const newIndex = (currentIndex + 1) % slides.length;
                        updateSlider(newIndex);
                    }

                    function prevSlide() {
                        const newIndex = (currentIndex - 1 + slides.length) % slides.length;
                        updateSlider(newIndex);
                    }

                    function setupAutoPlay() {
                        if (autoPlayInterval) {
                            clearInterval(autoPlayInterval);
                            autoPlayInterval = null;
                        }
                        
                        if (slides.length > 1 && slideTiming > 0) {
                            autoPlayInterval = setInterval(() => {
                                const now = Date.now();
                                const timeSinceLastChange = now - lastSlideChange;
                                
                                if (timeSinceLastChange >= slideTiming) {
                                    nextSlide();
                                }
                            }, 1000);
                        }
                    }

                    function setupEventListeners() {
                        if (next) {
                            next.onclick = function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                nextSlide();
                            };
                        }

                        if (prev) {
                            prev.onclick = function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                prevSlide();
                            };
                        }

                        dots.forEach((dot, i) => {
                            dot.onclick = function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                updateSlider(i);
                            };
                        });
                    }

                    function initializeSlider() {
                        setupEventListeners();
                        initializeAnimation();
                        updateSlider(0);
                        setupAutoPlay();
                    }

                    initializeSlider();
                });
            }

            function startInitialization() {
                if (document.readyState === \'loading\') {
                    document.addEventListener(\'DOMContentLoaded\', initSlider);
                } else {
                    initSlider();
                }

                setTimeout(initSlider, 100);
            }

            startInitialization();
        })();
    </script>
    ';
}



private function renderBrandLogoSlider($component)
{
    // Obtener valores con prioridad: attributes > propiedades directas > valores por defecto
    $attributes = $component['attributes'] ?? [];
    
    // Procesar valores numéricos
    $slidesToShow = intval($attributes['data-slides-to-show'] ?? $component['slidesToShow'] ?? 6);
    $slidesToScroll = intval($attributes['data-slides-to-scroll'] ?? $component['slidesToScroll'] ?? 1);
    $spaceBetween = intval($attributes['data-space-between'] ?? $component['spaceBetween'] ?? 20);
    $autoplaySpeed = intval($attributes['data-autoplay-speed'] ?? $component['autoplaySpeed'] ?? 2000);

    // Procesar valores booleanos (convertir string "true"/"false" a boolean)
    $autoplay = filter_var($attributes['data-autoplay'] ?? $component['autoplay'] ?? false, FILTER_VALIDATE_BOOLEAN);
    $arrows = filter_var($attributes['data-arrows'] ?? $component['arrows'] ?? false, FILTER_VALIDATE_BOOLEAN);
    $loop = filter_var($attributes['data-loop'] ?? $component['loop'] ?? false, FILTER_VALIDATE_BOOLEAN);

    // Procesar imágenes
    $imagesData = [];
    if (!empty($attributes['data-images-data'])) {
        try {
            $imagesData = json_decode($attributes['data-images-data'], true);
            if (!is_array($imagesData)) {
                $imagesData = [];
            }
        } catch (\Exception $e) {
            $imagesData = [];
        }
    }

    // Construir HTML
    $html = '<div class="gjs-swiper-logo-slider swiper"';
    $html .= ' data-slides-to-show="'.$slidesToShow.'"';
    $html .= ' data-slides-to-scroll="'.$slidesToScroll.'"';
    $html .= ' data-space-between="'.$spaceBetween.'"';
    $html .= ' data-autoplay="'.($autoplay ? 'true' : 'false').'"';
    $html .= ' data-autoplay-speed="'.$autoplaySpeed.'"';
    $html .= ' data-arrows="'.($arrows ? 'true' : 'false').'"';
    $html .= ' data-loop="'.($loop ? 'true' : 'false').'">';
    
    $html .= '<div class="swiper-wrapper">';
    
    foreach ($imagesData as $image) {
        $src = htmlspecialchars($image['src'] ?? '');
        $alt = htmlspecialchars($image['alt'] ?? '');
        
        $html .= '<div class="swiper-slide ltn__brand-logo-item">';
        $html .= '<img src="'.$src.'" alt="'.$alt.'" style="max-width:100%;height:auto;object-fit:contain">';
        $html .= '</div>';
    }
    
    $html .= '</div>';
    
    if ($arrows) {
        $html .= '<div class="swiper-button-prev"></div>';
        $html .= '<div class="swiper-button-next"></div>';
    }
    
    $html .= '</div>';
    
    // Script de inicialización con valores dinámicos
    $html .= '<script>
    document.addEventListener("DOMContentLoaded", function() {
        const sliderEl = document.querySelector(".gjs-swiper-logo-slider");
        if (sliderEl && typeof Swiper !== "undefined") {
            new Swiper(sliderEl, {
                slidesPerView: '.$slidesToShow.',
                spaceBetween: '.$spaceBetween.',
                loop: '.($loop ? 'true' : 'false').',
                autoplay: '.($autoplay ? '{
                    delay: '.$autoplaySpeed.',
                    disableOnInteraction: false
                }' : 'false').',
                slidesPerGroup: '.$slidesToScroll.',
                navigation: '.($arrows ? '{
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev"
                }' : 'false').',
                breakpoints: {
                    0: { slidesPerView: 2, spaceBetween: 10 },
                    576: { slidesPerView: 3, spaceBetween: 15 },
                    768: { slidesPerView: 4, spaceBetween: '.$spaceBetween.' },
                    992: { slidesPerView: '.min($slidesToShow, 5).', spaceBetween: '.$spaceBetween.' },
                    1200: { slidesPerView: '.$slidesToShow.', spaceBetween: '.$spaceBetween.' }
                }
            });
        }
    });
    </script>';
    
    return $html;
}


private function renderInputComponent($component)
{
    $tag = 'input';  // Default tag
    $attributes = $component['attributes'] ?? [];
    $content = $component['content'] ?? '';
    $type = $component['type'] ?? '';
    $label = $component['label'] ?? '';
    $value = $component['value'] ?? ''; // Obtener la propiedad value

    // Determinar el tag correcto basado en el tipo
    switch ($type) {
        case 'textarea-input':
            $tag = 'textarea';
            break;
        case 'select-input':
            $tag = 'select';
            break;
        case 'submit-button':
            $tag = 'button';
            $attributes['type'] = 'submit';
            $content = !empty($value) ? $value : $label; // Usar value si no está vacío, sino usar label
            break;
        default:
            // Para inputs normales, establecer el type correcto
            if (strpos($type, '-input') !== false) {
                $inputType = str_replace('-input', '', $type);
                $attributes['type'] = $inputType;
            }
    }

    // Procesar clases
    $processedClasses = $this->processComponentClasses($component, $attributes);
    if (!empty($processedClasses)) {
        $attributes['class'] = isset($attributes['class'])
            ? trim($attributes['class'] . ' ' . $processedClasses)
            : $processedClasses;
    }

    // Manejar atributos especiales para formularios
    if (isset($component['name'])) {
        $attributes['name'] = $component['name'];
    }
    if (isset($component['required']) && $component['required']) {
        $attributes['required'] = 'required';
    }

    $attrString = $this->buildAttributesString($attributes);

    // Construir el HTML según el tipo de elemento
    switch ($tag) {
        case 'textarea':
            return "<{$tag} {$attrString}>{$content}</{$tag}>";

        case 'select':
            $optionsHtml = '';
            if (isset($component['components'])) {
                foreach ($component['components'] as $option) {
                    if ($option['tagName'] === 'option') {
                        $optionValue = $option['attributes']['value'] ?? '';
                        $optionText = $option['content'] ?? '';
                        $isSelected = isset($component['selectedValue']) && $component['selectedValue'] == $optionValue ? 'selected' : '';
                        $optionsHtml .= "<option value=\"{$optionValue}\" {$isSelected}>{$optionText}</option>";
                    }
                }
            }
            return "<{$tag} {$attrString}>{$optionsHtml}</{$tag}>";

        case 'button':
            return "<{$tag} {$attrString}>{$content}</{$tag}>";

        default: // input y otros
            return "<{$tag} {$attrString} />";
    }
}

/**
 * Determina si un componente debe ser renderizado como imagen
 */
private function isImageComponent($component, $attributes): bool
{
    // 1. Por tipo explícito
    if (($component['type'] ?? '') === 'image') {
        return true;
    }

    // 2. Por presencia de atributos específicos de imagen
    $imgAttributes = ['src', 'srcset', 'sizes'];
    foreach ($imgAttributes as $attr) {
        if (isset($attributes[$attr])) {
            return true;
        }
    }

    // 3. Por clases comunes de imágenes
    $imgClasses = ['img', 'image', 'slide-image', 'card-img-top', 'img-fluid', 'thumbnail'];
    $currentClasses = explode(' ', $attributes['class'] ?? '');
    
    foreach ($imgClasses as $imgClass) {
        if (in_array($imgClass, $currentClasses)) {
            return true;
        }
    }

    // 4. Por nombre de componente personalizado (ej: 'gallery-image')
    $componentName = $component['componentName'] ?? '';
    if (stripos($componentName, 'image') !== false || stripos($componentName, 'img') !== false) {
        return true;
    }

    return false;
}

/**
 * Procesa las clases del componente
 */
private function processComponentClasses($component, $attributes): string
{
    if (!isset($component['classes'])) {
        return '';
    }

    $classNames = array_map(function ($c) {
        return is_array($c) ? ($c['name'] ?? '') : (string) $c;
    }, $component['classes']);

    return implode(' ', array_filter($classNames));
}

/**
 * Construye el string de atributos HTML
 */
private function buildAttributesString($attributes): string
{
    $attrString = '';
    foreach ($attributes as $key => $value) {
        if ($value !== null && $value !== '') {
            $attrString .= " {$key}=\"" . htmlspecialchars($value, ENT_QUOTES) . "\"";
        }
    }
    return $attrString;
}

/**
 * Construye el HTML final según el tipo de elemento
 */
private function buildFinalHtml(string $tag, string $attrString, string $innerHtml, bool $isImage): string
{
    if ($isImage) {
        // Elementos img son auto-cerrados
        return "<{$tag}{$attrString} />";
    }

    // Elementos normales con cierre
    return "<{$tag}{$attrString}>{$innerHtml}</{$tag}>";
}


private function prepareTemplateContent($content)
{
    // Si es array, extraer el contenido HTML
    if (is_array($content)) {
        return $this->extractHtmlFromComponents($content);
    }
    
    // Si es string JSON, decodificar primero
    if (is_string($content) && $this->isJson($content)) {
        $decoded = json_decode($content, true);
        return $this->extractHtmlFromComponents($decoded);
    }
    
    // Si es string HTML directo
    if (is_string($content)) {
        return $content;
    }
    
    return '<div class="empty-content">Contenido no disponible</div>';
}

private function prepareTemplateStyles($styles)
{
    // Si es array, extraer los estilos CSS
    if (is_array($styles)) {
        return implode("\n", array_filter($styles, 'is_string'));
    }
    
    // Si es string JSON, decodificar primero
    if (is_string($styles) && $this->isJson($styles)) {
        $decoded = json_decode($styles, true);
        return implode("\n", array_filter($decoded, 'is_string'));
    }
    
    // Si es string CSS directo
    if (is_string($styles)) {
        return $styles;
    }
    
    return '';
}

private function extractHtmlFromComponents($components)
{
    if (!is_array($components)) return '';
    
    $html = '';
    foreach ($components as $component) {
        // Saltar el contenedor principal si existe
        if (isset($component['attributes']['class']) && 
            strpos($component['attributes']['class'], 'container') !== false) {
            if (isset($component['components'])) {
                foreach ($component['components'] as $innerComponent) {
                    $html .= $this->extractComponentHtml($innerComponent);
                }
            }
            continue;
        }
        
        $html .= $this->extractComponentHtml($component);
    }
    
    return $html;
}


public function showForm()
{

    $website = app(\Hyn\Tenancy\Environment::class)->website();

    if ($website) {
        // Si quieres asegurarte de obtener el theme específico del tenant
        $theme = \Sitedigitalweb\Pagina\Tenant\Cms_theme::first();
    } else {
        // En sistema central, podrías querer filtrar por un campo específico
        $theme = Cms_theme::first();
    }

    return view('pagina::pages.editor', compact('theme'));
}



public function theme(Request $request)
{
    $validated = $request->validate([
        'template_id' => 'nullable|integer|max:100',
        'theme'       => 'nullable|string|max:150',
        'color_1' => 'nullable|string|max:7',
        'color_2' => 'nullable|string|max:7',
        'color_3' => 'nullable|string|max:7',
        'color_4' => 'nullable|string|max:7',
        'font_h1' => 'nullable|string|max:50',
        'font_h2' => 'nullable|string|max:50',
        'font_h3' => 'nullable|string|max:50',
        'font_h4' => 'nullable|string|max:50',
        'font_h5' => 'nullable|string|max:50',
        'size_h1' => 'nullable|integer|min:10|max:100',
        'size_h2' => 'nullable|integer|min:10|max:100',
        'size_h3' => 'nullable|integer|min:10|max:100',
        'size_h4' => 'nullable|integer|min:10|max:100',
        'size_h5' => 'nullable|integer|min:10|max:100',
    ]);

    try {
        // Detectar si estamos en un tenant
        $website = app(\Hyn\Tenancy\Environment::class)->website();
        
        if ($website) {
            // Usar modelos del tenant
            $themeModel = \Sitedigitalweb\Pagina\Tenant\Cms_theme::class;
            $variableModel = \Sitedigitalweb\Pagina\Tenant\Cms_variable::class;
            $templateModel = \Sitedigitalweb\Pagina\Tenant\Cms_Template::class;
            $tenantId = $website->id;
            $tenantUuid = $website->uuid;
        } else {
            // Usar modelos del sistema central
            $themeModel = Cms_theme::class;
            $variableModel = Cms_variable::class;
            $templateModel = Cms_Template::class;
            $tenantId = null;
            $tenantUuid = 'central';
        }

        // Si existe cms_theme actualiza
        $theme = $themeModel::first();

        if (!$theme) {
            // Si no hay cms_theme, crea uno nuevo con los datos de cms_variable
            $variable = $variableModel::first();
            $themeData = array_merge(
                (array) $variable ?? [],
                $validated
            );
            
            // Si el modelo tenant necesita website_id, agregarlo
            if ($website && !isset($themeData['website_id'])) {
                $themeData['website_id'] = $website->id;
            }
            
            $theme = $themeModel::create($themeData);
        } else {
            $theme->update($validated);
        }

        // Obtener el template desde la base de datos
        $template = $templateModel::first();
        $templateName = 'default';
        
        if ($template && !empty($template->template)) {
            $templateName = $template->template;
        }

        // Limpiar el nombre del template
        $templateName = preg_replace('/[^a-zA-Z0-9_-]/', '', $templateName);
        if (empty($templateName)) {
            $templateName = 'default';
        }

        // ======================
        // GENERAR CSS PARA EL TENANT
        // ======================
        $css = "/* CSS para Tenant: {$tenantUuid} - Template: {$templateName} */\n";
        $css .= ":root {\n";
        
        // Colores específicos del tenant
        for ($i = 1; $i <= 4; $i++) {
            $color = $theme->{'color_'.$i} ?? '#000000';
            $css .= "    --color-{$i}: {$color};\n";
        }
        
        // ✅ AGREGAR COLOR PRIMARY Y SECONDARY
        $colorPrimary = $theme->color_1 ?? '#000000';
        $colorSecondary = $theme->color_2 ?? '#000000';
        $css .= "    --color-primary: {$colorPrimary};\n";
        $css .= "    --color-secondary: {$colorSecondary};\n";
        
        // Fuentes específicas del tenant
        for ($i = 1; $i <= 5; $i++) {
            $font = $theme->{'font_h'.$i} ?? 'Roboto';
            $size = $theme->{'size_h'.$i} ?? 16;
            $css .= "    --font-h{$i}: '{$font}', sans-serif;\n";
            $css .= "    --size-h{$i}: {$size}px;\n";
        }
        $css .= "}\n\n";

        // Clases de color específicas del tenant
        for ($i = 1; $i <= 4; $i++) {
            $colorClass = $theme->{'var_color_'.$i} ?? null;
            if ($colorClass) {
                $css .= ".{$colorClass} { background: var(--color-{$i}) !important; }\n";
            }
        }

        // ✅ AGREGAR CLASES PARA PRIMARY Y SECONDARY
        $css .= ".color-primary { background: var(--color-primary) !important; }\n";
        $css .= ".color-secondary { background: var(--color-secondary) !important; }\n";
        $css .= ".text-primary { color: var(--color-primary) !important; }\n";
        $css .= ".text-secondary { color: var(--color-secondary) !important; }\n";
        $css .= ".border-primary { border-color: var(--color-primary) !important; }\n";
        $css .= ".border-secondary { border-color: var(--color-secondary) !important; }\n";
        $css .= ".bg-primary { background-color: var(--color-primary) !important; }\n";
        $css .= ".bg-secondary { background-color: var(--color-secondary) !important; }\n";

        $css .= "\n";

        // Tipografías y tamaños específicos del tenant
        for ($i = 1; $i <= 5; $i++) {
            $fontClass = $theme->{'var_font_h'.$i} ?? 'h'.$i;
            $css .= "{$fontClass} {\n";
            $css .= "    font-family: var(--font-h{$i}) !important;\n";
            $css .= "    font-size: var(--size-h{$i}) !important;\n";
            $css .= "}\n";
        }

        // ======================
        // GUARDAR CSS POR TENANT
        // ======================
        if ($website) {
            // Para TENANTS: guardar en public/tenants/{uuid}/theme.css
            $cssDirectory = public_path("tenants/{$tenantUuid}");
            $cssPath = "{$cssDirectory}/theme.css";
            $cssUrl = url("tenants/{$tenantUuid}/theme.css");
        } else {
            // Para SISTEMA CENTRAL: guardar en public/central/theme.css
            $cssDirectory = public_path('central');
            $cssPath = "{$cssDirectory}/theme.css";
            $cssUrl = url('central/theme.css');
        }

        // Asegurar que el directorio existe
        if (!File::exists($cssDirectory)) {
            File::makeDirectory($cssDirectory, 0755, true);
        }

        // Guardar el archivo CSS
        File::put($cssPath, $css);

        // También guardar en storage como backup
        $storagePath = "tenants/{$tenantUuid}/theme.css";
        Storage::disk('public')->put($storagePath, $css);

        return response()->json([
            'status'  => 'success',
            'message' => 'Estilos del tenant guardados correctamente ✅',
            'data'    => $theme,
            'css_url' => $cssUrl,
            'css_path' => $cssPath,
            'tenant'   => $tenantUuid,
            'template' => $templateName
        ], 200);

    } catch (\Exception $e) {
        \Log::error('Error al guardar theme CSS para tenant: ' . $e->getMessage());
        
        return response()->json([
            'status'  => 'error',
            'message' => 'Hubo un problema al guardar los estilos del tenant',
            'error'   => $e->getMessage()
        ], 500);
    }
}

public function getTenantCss()
{
    try {
        $website = app(\Hyn\Tenancy\Environment::class)->website();
        
        if ($website) {
            // Tenant: cargar CSS específico
            $tenantUuid = $website->uuid;
            $cssPath = public_path("tenants/{$tenantUuid}/theme.css");
            
            if (File::exists($cssPath)) {
                $css = File::get($cssPath);
            } else {
                // Si no existe, usar CSS por defecto
                $css = "/* CSS por defecto - Tenant: {$tenantUuid} */\n:root { --color-1: #000000; --color-2: #ffffff; }";
            }
        } else {
            // Sistema central: cargar CSS central
            $cssPath = public_path('theme-central.css');
            
            if (File::exists($cssPath)) {
                $css = File::get($cssPath);
            } else {
                $css = "/* CSS del sistema central */\n:root { --color-1: #000000; --color-2: #ffffff; }";
            }
        }

        return response($css, 200)
            ->header('Content-Type', 'text/css')
            ->header('X-Tenant', $website ? $website->uuid : 'central');

    } catch (\Exception $e) {
        // En caso de error, devolver CSS vacío
        return response("/* Error cargando CSS: {$e->getMessage()} */", 200)
            ->header('Content-Type', 'text/css');
    }
}

public function getThemeData()
{

    $website = app(\Hyn\Tenancy\Environment::class)->website();
    
    if ($website) {
        $templateModel = \Sitedigitalweb\Pagina\Tenant\Cms_Template::class;
        $themeModel = \Sitedigitalweb\Pagina\Tenant\Cms_theme::class;
        $variableModel = \Sitedigitalweb\Pagina\Tenant\Cms_variable::class;
        
        // Para tenant, puedes filtrar por website_id si es necesario
        $template = $templateModel::first();
        $theme = $themeModel::first();
    } else {
        $templateModel = Cms_Template::class;
        $themeModel = Cms_theme::class;
        $variableModel = Cms_variable::class;
        
        $template = $templateModel::first();
        $theme = $themeModel::first();
    }

    // El resto de la lógica permanece igual...
    if (!$theme && $template) {
        $theme = $variableModel::where('template_id', $template->id)->first();
    }

    // Si tampoco hay variables, crear valores por defecto
    if (!$theme) {
        $theme = (object)[
            // Colores
            'color_1' => '#ffffff',
            'color_2' => '#ffffff',
            'color_3' => '#ffffff',
            'color_4' => '#ffffff',
            'var_color_1' => '--color-1',
            'var_color_2' => '--color-2',
            'var_color_3' => '--color-3',
            'var_color_4' => '--color-4',
            // Tipografía
            'font_h1' => 'Roboto',
            'font_h2' => 'Roboto',
            'font_h3' => 'Roboto',
            'font_h4' => 'Roboto',
            'font_h5' => 'Roboto',
            'var_font_h1' => '--font-h1',
            'var_font_h2' => '--font-h2',
            'var_font_h3' => '--font-h3',
            'var_font_h4' => '--font-h4',
            'var_font_h5' => '--font-h5',
            // Tamaños
            'size_h1' => 24,
            'size_h2' => 22,
            'size_h3' => 20,
            'size_h4' => 18,
            'size_h5' => 16,
            'var_size_h1' => '--size-h1',
            'var_size_h2' => '--size-h2',
            'var_size_h3' => '--size-h3',
            'var_size_h4' => '--size-h4',
            'var_size_h5' => '--size-h5',
        ];
    } else {
        // Si existe theme o variable, asegurarse de que todos los var_* existan
        for ($i = 1; $i <= 4; $i++) {
            $theme->{'var_color_'.$i} = $theme->{'var_color_'.$i} ?? '--color-'.$i;
        }
        for ($i = 1; $i <= 5; $i++) {
            $theme->{'var_font_h'.$i} = $theme->{'var_font_h'.$i} ?? '--font-h'.$i;
            $theme->{'var_size_h'.$i} = $theme->{'var_size_h'.$i} ?? '--size-h'.$i;
        }
    }

    return response()->json($theme);
}



public function themeCss()
{
    $website = app(\Hyn\Tenancy\Environment::class)->website();
    
    if ($website) {
        $themeModel = \Sitedigitalweb\Pagina\Tenant\Cms_theme::class;
        $variableModel = \Sitedigitalweb\Pagina\Tenant\Cms_variable::class;
        
        // Si los modelos tenant necesitan filtrar por website_id
        $theme = $themeModel::where('website_id', $website->id)->first() 
               ?? $variableModel::where('website_id', $website->id)->first();
    } else {
        $themeModel = Cms_theme::class;
        $variableModel = Cms_variable::class;
        
        $theme = $themeModel::first() ?? $variableModel::first();
    }

    if (!$theme) {
        abort(404, 'Tema no configurado');
    }

    // Resto del código para generar CSS permanece igual...
    $css = "";
    
    // ======================
    // DEFINIR VARIABLES EN :root
    // ======================
    $css .= ":root {\n";
    for ($i = 1; $i <= 4; $i++) {
        $colorValue = $theme->{'color_'.$i} ?? null;
        if ($colorValue) {
            $css .= "    --color-{$i}: {$colorValue};\n";
        }
    }
    for ($i = 1; $i <= 5; $i++) {
        $sizeValue = $theme->{'size_h'.$i} ?? 16;
        $fontValue = $theme->{'font_h'.$i} ?? 'Roboto';
        $css .= "    --font-h{$i}: '{$fontValue}', sans-serif;\n";
        $css .= "    --size-h{$i}: {$sizeValue}px;\n";
    }
    $css .= "}\n\n";

    // ======================
    // CLASES DE COLORES
    // ======================
    for ($i = 1; $i <= 4; $i++) {
        $colorClass = $theme->{'var_color_'.$i} ?? null;
        if ($colorClass) {
            $css .= ".{$colorClass} { background: var(--color-{$i}) !important; }\n";
        }
    }

    $css .= "\n";

    // ======================
    // TIPOGRAFÍAS Y TAMAÑOS
    // ======================
    for ($i = 1; $i <= 5; $i++) {
        $fontClass = $theme->{'var_font_h'.$i} ?? 'h'.$i;

        $css .= "{$fontClass} {\n";
        $css .= "    font-family: var(--font-h{$i}) !important;\n";
        $css .= "    font-size: var(--size-h{$i}) !important;\n";
        $css .= "}\n";
    }

    return response($css, 200)->header('Content-Type', 'text/css');
}

public function generateThemeCss()
{
    // Detectar si estamos en un tenant
    $website = app(\Hyn\Tenancy\Environment::class)->website();
    
    if ($website) {
        $themeModel = \Sitedigitalweb\Pagina\Tenant\Cms_theme::class;
        $variableModel = \Sitedigitalweb\Pagina\Tenant\Cms_variable::class;
        $templateModel = \Sitedigitalweb\Pagina\Tenant\Cms_Template::class;
    } else {
        $themeModel = Cms_theme::class;
        $variableModel = Cms_variable::class;
        $templateModel = Cms_Template::class;
    }

    $theme = $themeModel::first() ?? $variableModel::first();

    if (!$theme) {
        abort(404, 'Tema no configurado');
    }

    // Obtener el template desde la base de datos
    $template = $templateModel::first();
    
    if (!$template) {
        abort(404, 'Template no configurado');
    }

    $templateName = $template->template ?? 'default';
    
    // Limpiar el nombre del template para evitar problemas con rutas
    $templateName = preg_replace('/[^a-zA-Z0-9_-]/', '', $templateName);
    
    if (empty($templateName)) {
        $templateName = 'default';
    }

    // Generar el CSS
    $css = ":root {\n";
    for ($i = 1; $i <= 4; $i++) {
        $colorValue = $theme->{'color_'.$i} ?? '#000';
        $css .= "    --color-{$i}: {$colorValue};\n";
    }
    
    // ✅ AGREGAR COLOR PRIMARY Y SECONDARY
    $colorPrimary = $theme->color_1 ?? '#000';
    $colorSecondary = $theme->color_2 ?? '#000';
    $css .= "    --color-primary: {$colorPrimary};\n";
    $css .= "    --color-secondary: {$colorSecondary};\n";
    
    for ($i = 1; $i <= 5; $i++) {
        $sizeValue = $theme->{'size_h'.$i} ?? 16;
        $fontValue = $theme->{'font_h'.$i} ?? 'Roboto';
        $css .= "    --font-h{$i}: '{$fontValue}', sans-serif;\n";
        $css .= "    --size-h{$i}: {$sizeValue}px;\n";
    }
    $css .= "}\n\n";

    // Colores
    for ($i = 1; $i <= 4; $i++) {
        $colorClass = $theme->{'var_color_'.$i} ?? null;
        if ($colorClass) {
            $css .= ".{$colorClass} { background: var(--color-{$i}) !important; }\n";
        }
    }
    
    // ✅ AGREGAR CLASES PARA PRIMARY Y SECONDARY
    $css .= ".color-primary { background: var(--color-primary) !important; }\n";
    $css .= ".color-secondary { background: var(--color-secondary) !important; }\n";
    $css .= ".text-primary { color: var(--color-primary) !important; }\n";
    $css .= ".text-secondary { color: var(--color-secondary) !important; }\n";
    $css .= ".border-primary { border-color: var(--color-primary) !important; }\n";
    $css .= ".border-secondary { border-color: var(--color-secondary) !important; }\n";

    $css .= "\n";

    // Tipografías
    for ($i = 1; $i <= 5; $i++) {
        $fontClass = $theme->{'var_font_h'.$i} ?? 'h'.$i;
        $css .= "{$fontClass} {\n";
        $css .= "    font-family: var(--font-h{$i}) !important;\n";
        $css .= "    font-size: var(--size-h{$i}) !important;\n";
        $css .= "}\n";
    }

    // Crear la ruta dinámica
    $cssPath = public_path("templates/{$templateName}/theme.css");
    
    // Asegurar que el directorio existe
    if (!File::exists(dirname($cssPath))) {
        File::makeDirectory(dirname($cssPath), 0755, true);
    }

    File::put($cssPath, $css);

    return back()->with('success', "Archivo theme.css actualizado correctamente en templates/{$templateName}/ ✅");
}

public function updateTheme(Request $request)
{
    $variables = Cms_variable::first();
    $variables->update($request->all());

    // Llamar al generador
    $this->generateThemeCss();

    return back()->with('success', 'Tema y CSS actualizados ✅');
}


public function actualizarTemplate(Request $request)
{
    try {
        $templateId = $request->input('template');
         $website = app(\Hyn\Tenancy\Environment::class)->website();

           // Si estamos en tenant, simplemente usamos la base del tenant
        if ($website) {
        $registro = \Sitedigitalweb\Pagina\Tenant\Cms_Template::first(); // Aquí no hay website_id
        } else {
        // En la base central, también usamos sin filtro
        $registro = Cms_Template::first(); // O con filtro si tú creaste website_id
        }


        if ($registro) {
            $registro->update([
                'template' => $templateId
            ]);
        } else {
            $registro = $model::create([
                'template' => $templateId
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Template actualizado correctamente',
            'data' => $registro
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Error al actualizar template',
            'error' => $e->getMessage()
        ], 500);
    }
}



private function extractComponentHtml($component)
{
    if (isset($component['content'])) {
        return $component['content'];
    } elseif (is_string($component)) {
        return $component;
    } elseif (is_array($component) && isset($component['components'])) {
        $innerHtml = '';
        foreach ($component['components'] as $innerComponent) {
            $innerHtml .= $this->extractComponentHtml($innerComponent);
        }
        return $innerHtml;
    }
    return '';
}

private function isJson($string)
{
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}
    // Guardar plantilla
  public function store(Request $request)
{
    // Registrar en el log todo el contenido de la solicitud entrante
    \Log::debug('Request Data:', $request->all());
 
    // Validar los datos del request con reglas específicas
    $validated = $request->validate([
        'name'    => 'required|string|max:255', // El nombre es obligatorio y debe ser un string corto
        'content' => 'required', // El contenido puede ser un string o un array
        'styles'  => 'nullable|array', // Los estilos son opcionales y deben ser un array si están presentes
        'scripts' => 'nullable', // Los scripts pueden venir por separado como string
        'assets'  => 'nullable|array', // Los assets son opcionales y deben ser un array
        'id'      => 'nullable|integer' // El ID puede venir si se está actualizando una página existente
    ]);
    
    // Si el contenido es un array (por ejemplo, estructuras JSON complejas), se convierte a string
    $contentHtml = is_array($validated['content'])
        ? json_encode($validated['content'])
        : $validated['content'];

    // Inicializa el contenido sin scripts, inicialmente igual al contenido completo
    $contentWithoutScripts = $contentHtml;

    // Se obtiene el valor de 'scripts' si fue enviado explícitamente
    $scripts = $validated['scripts'] ?? null;

    // Si no se envió 'scripts', pero el contenido incluye etiquetas <script>, se extraen con regex
    if (!$scripts && is_string($contentHtml)) {
        if (preg_match_all('/<script\b[^>]*>(.*?)<\/script>/is', $contentHtml, $matches)) {
            $scripts = implode("\n", $matches[0]); // Concatenar todos los bloques <script> encontrados
            // Eliminar los bloques <script> del HTML para guardar una versión limpia
            $contentWithoutScripts = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $contentHtml);
        }
    }

    // Preparar los datos para guardar en la base de datos
    $pageData = [
        'name'    => $validated['name'],
        'content' => $contentWithoutScripts,
        'styles'  => $validated['styles'],
        'assets'  => $validated['assets'] ?? null,
        'scripts' => $scripts,
        'theme'   => $validated['theme'] ?? null // 
    ];

    // Obtener el contexto del sitio actual (multi-tenant)
    $website = app(\Hyn\Tenancy\Environment::class)->website();

    // Determinar el modelo que se usará, según si hay un tenant o no
    $pageModel = $website ? \Sitedigitalweb\Pagina\Tenant\Page::class : Page::class;

    // Crear o actualizar la página en base al ID (si está presente) y al website actual
    if ($website) {
        $template = $pageModel::updateOrCreate(
            ['id' => $validated['id'] ?? null],
            $pageData + ['website_id' => $website->id]
        );
    } else {
        $template = $pageModel::updateOrCreate(
            ['id' => $validated['id'] ?? null],
            $pageData
        );
    }

    // Devolver respuesta exitosa con ID del template y URL de vista previa
    return response()->json([
        'success'     => true,
        'id'          => $template->id,
        'preview_url' => route('preview', $template->id)
    ]);
}


    // Cargar plantilla
public function load($id)
{
    // Detectar si estamos dentro de un tenant
    $website = app(\Hyn\Tenancy\Environment::class)->website();

    // Seleccionar el modelo correcto según el contexto
    $pageModel = $website 
        ? \Sitedigitalweb\Pagina\Tenant\Page::class 
        : Page::class;

    // Construir la consulta
    $query = $pageModel::where('id', $id);

    // Aplicar filtro solo si estamos en un tenant y el modelo tiene 'website_id'
    if ($website && Schema::hasColumn((new $pageModel)->getTable(), 'website_id')) {
        $query->where('website_id', $website->id);
    }

    // Obtener la plantilla o fallar
    $template = $query->firstOrFail();

    // Preparar componentes
    $components = $this->prepareComponentsForEditor(
        is_string($template->content) ? json_decode($template->content, true) : ($template->content ?? [])
    );

    // Preparar estilos
    $styles = $this->prepareStylesForEditor(
        is_string($template->styles) ? json_decode($template->styles, true) : ($template->styles ?? [])
    );

    // Retornar la respuesta JSON
    return response()->json([
        'gjs-components' => $components,
        'gjs-styles'     => $styles,
        'gjs-assets'     => $template->assets ?? [],
        'tenant_id'      => $website->id ?? null,
    ]);
}


public function creartemplate()
{
    return view('pagina::templates.create');
}

private function prepareComponentsForEditor($content)
{
    if (is_array($content)) {
        return $this->fixSubmitButtonValue($content);
    }

    if (is_string($content) && $this->isJson($content)) {
        $decoded = json_decode($content, true);
        return is_array($decoded) ? $this->fixSubmitButtonValue($decoded) : [];
    }

    if (is_string($content)) {
        return [
            'type' => 'wrapper',
            'components' => [
                [
                    'type' => 'text',
                    'content' => $content
                ]
            ]
        ];
    }

    return [];
}

private function fixSubmitButtonValue(array $components): array
{
    foreach ($components as &$component) {
        // Si tiene hijos, procesarlos recursivamente
        if (!empty($component['components']) && is_array($component['components'])) {
            $component['components'] = $this->fixSubmitButtonValue($component['components']);
        }

        // Mover "value" dentro de "attributes" si el type es "submit-input"
        if (
            isset($component['type']) && 
            $component['type'] === 'submit-button' && 
            isset($component['value'])
        ) {
            // Aseguramos que 'attributes' existe como array
            if (!isset($component['attributes']) || !is_array($component['attributes'])) {
                $component['attributes'] = [];
            }

            // Solo lo movemos si no está ya
            if (!isset($component['attributes']['value'])) {
                $component['attributes']['value'] = $component['value'];
            }

            // Eliminamos el atributo superior
            unset($component['value']);
        }
    }

    return $components;
}


public function templatestore(Request $request)
{
    // Validación
    $request->validate([
        'template' => 'nullable|string|max:255',
        'description' => 'nullable|string|max:255',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'url' => 'nullable|url|max:255',
    ]);

    $imagePath = null;

    if ($request->hasFile('image')) {
        // Definir carpeta de destino en /public/grapichtemplate
        $destinationPath = public_path('graptemplate');
        
        // Crear la carpeta si no existe
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }


        // Nombre único para la imagen
        $fileName = time() . '_' . $request->file('image')->getClientOriginalName();

        // Mover el archivo
        $request->file('image')->move($destinationPath, $fileName);

        // Guardar la ruta relativa (para usar en asset())
        $imagePath = '/graptemplate/' . $fileName;
    }

    // Crear registro en BD
    $template = Cms_Template::create([
        'template' => $request->template,
        'description' => $request->description,
        'image' => $imagePath,
        'url' => $request->url,
    ]);

    return redirect()->route('sd.templates')
                     ->with('success', 'Template creado correctamente.');
}

public function edit($id)
{

    $template = Cms_Template::findOrFail($id);
    return view('pagina::templates.edit', compact('template'));
}

public function update(Request $request, $id)
{
    $request->validate([
        'template' => 'nullable|string|max:255',
        'description' => 'nullable|string|max:255',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'url' => 'nullable|url|max:255',
    ]);

    $template = Cms_Template::findOrFail($id);

    // Si subió nueva imagen
    if ($request->hasFile('image')) {
        $destinationPath = public_path('graptemplate');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        $fileName = time() . '_' . $request->file('image')->getClientOriginalName();
        $request->file('image')->move($destinationPath, $fileName);

        $template->image = '/graptemplate/' . $fileName;
    }

    // Actualizar otros campos
    $template->template = $request->template;
    $template->description = $request->description;
    $template->url = $request->url;
    $template->save();

    return redirect()->route('sd.templates')->with('success', 'Template actualizado correctamente.');
}



private function prepareStylesForEditor($styles)
{
    if (is_array($styles)) {
        return $styles;
    }
    
    if (is_string($styles) && $this->isJson($styles)) {
        $decoded = json_decode($styles, true);
        return is_array($decoded) ? $decoded : [];
    }
    
    return [];
}



protected function getEditorComponents()
{
    return [
        'slider' => $this->getSliderComponent(),
        'collapsible' => $this->getCollapsibleComponent(),
        'bootstrap-accordion' => $this->getBootstrapAccordionComponent()
    ];
}

protected function getBootstrapAccordionComponent()
{
    return [
        'name' => 'Acordeón Bootstrap',
        'type' => 'bootstrap-accordion',
        'component' => [
            'tagName' => 'div',
            'type' => 'bootstrap-accordion',
            'attributes' => [
                'class' => 'accordion',
                'id' => 'accordion-' . uniqid()
            ],
            'components' => [
                $this->createAccordionItem(1, true)
            ],
            'traits' => [
                [
                    'type' => 'select',
                    'label' => 'Estilo',
                    'name' => 'accordion-style',
                    'options' => [
                        ['value' => '', 'name' => 'Default'],
                        ['value' => 'accordion-flush', 'name' => 'Flush'],
                        ['value' => 'accordion-alternate', 'name' => 'Alternate']
                    ]
                ],
                [
                    'type' => 'button',
                    'label' => 'Añadir elemento',
                    'text' => '+ Añadir',
                    'command' => 'add-accordion-item'
                ]
            ]
        ],
        'styles' => `
            .accordion-button:not(.collapsed) {
                color: #0c63e4;
                background-color: #e7f1ff;
                box-shadow: inset 0 -1px 0 rgb(0 0 0 / 13%);
            }
            .accordion-button:focus {
                z-index: 3;
                border-color: #86b7fe;
                outline: 0;
                box-shadow: 0 0 0 0.25rem rgb(13 110 253 / 25%);
            }
            .accordion-flush .accordion-button {
                border-radius: 0;
            }
            .accordion-alternate .accordion-item {
                border: 0;
                margin-bottom: 10px;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }
        `,
        'script' => `
            // Asegurar que Bootstrap JS está cargado
            if (typeof bootstrap !== 'undefined') {
                // Inicializar acordeones dinámicamente
                document.querySelectorAll('.accordion').forEach(accordion => {
                    new bootstrap.Collapse(accordion.querySelector('.accordion-collapse'), {
                        toggle: false
                    });
                    
                    // Manejar clics en los botones
                    accordion.querySelectorAll('.accordion-button').forEach(button => {
                        button.addEventListener('click', function() {
                            const collapseId = this.getAttribute('data-bs-target');
                            const collapseElement = document.querySelector(collapseId);
                            new bootstrap.Collapse(collapseElement, {
                                toggle: true
                            });
                        });
                    });
                });
            }
        `
    ];
}

protected function createAccordionItem($index, $show = false)
{
    $itemId = uniqid();
    return [
        'tagName' => 'div',
        'attributes' => ['class' => 'accordion-item'],
        'components' => [
            [
                'tagName' => 'h2',
                'attributes' => [
                    'class' => 'accordion-header',
                    'id' => 'heading-' . $itemId
                ],
                'components' => [
                    [
                        'tagName' => 'button',
                        'type' => 'button',
                        'attributes' => [
                            'class' => 'accordion-button' . ($show ? '' : ' collapsed'),
                            'type' => 'button',
                            'data-bs-toggle' => 'collapse',
                            'data-bs-target' => '#collapse-' . $itemId,
                            'aria-expanded' => $show ? 'true' : 'false',
                            'aria-controls' => 'collapse-' . $itemId
                        ],
                        'content' => 'Elemento Acordeón #' . $index,
                        'editable' => true
                    ]
                ]
            ],
            [
                'tagName' => 'div',
                'attributes' => [
                    'id' => 'collapse-' . $itemId,
                    'class' => 'accordion-collapse collapse' . ($show ? ' show' : ''),
                    'aria-labelledby' => 'heading-' . $itemId
                ],
                'components' => [
                    [
                        'tagName' => 'div',
                        'attributes' => ['class' => 'accordion-body'],
                        'content' => '<strong>Este es el cuerpo del acordeón.</strong> Contenido de ejemplo.',
                        'editable' => true
                    ]
                ]
            ]
        ]
    ];
}


public function upload(Request $request){

$dominio =  $_SERVER['HTTP_HOST'];
$hostname = DB::table('tenancy.hostnames')->where('fqdn','=',$dominio)->get();

foreach ($hostname as $hostname) {
 $websites = DB::table('tenancy.websites')->where('id','=',$hostname->website_id)->get();   
}
foreach($websites as $websites){
$salida = $websites->uuid;
}


    if($_FILES)
{
    
$resultArray = array();
    foreach ( $_FILES as $file){

                $fileName = $file['name'];
                $tmpName = $file['tmp_name'];
                $fileSize = $file['size'];
                $fileType = $file['type'];
                if ($file['error'] != UPLOAD_ERR_OK)
                {
                        error_log($file['error']);
                        echo JSON_encode(null);
                }
                $fp = fopen($tmpName, 'r');
                $content = fread($fp, filesize($tmpName));
                fclose($fp);
                $webps = $file['name'];
                 $new_webps = preg_replace('"\.(jpg|jpeg|png|webp)$"', '.webp', $webps);
                $result=array(
                        'name'=>$new_webps,
                        'type'=>'image',
                        'src'=>"/saas"."/".$salida."/".$new_webps,
                        'height'=>350,
                        'width'=>250
                ); 


        $img = $file['name'];
        $extension = \File::extension($img);
        if(in_array($extension,["jpeg","jpg","png","webp"])){
    //old image
            $webp = $file['name'];
            $new_webp = preg_replace('"\.(jpg|jpeg|png|webp)$"', '.webp', $webp);

    
    $uploadDir = public_path('saas/'.$salida);

      $uploadDirsave = '/saas/'.$salida.'/'.$new_webp;
$targetPath = $uploadDir.'/'. $new_webp;
$lata = move_uploaded_file($tmpName, $targetPath);
    }

if(!$this->tenantName){
 GrapeImage::insert([
  'image' => $uploadDirsave
 ]);
}else{
\Sitedigitalweb\Pagina\Tenant\GrapeImage::insert([
  'image' => $uploadDirsave
 ]);
}

           

         

                array_push($resultArray,$result);
    }    
$response = array( 'data' => $resultArray );
echo json_encode($response);
}

    }



    
}