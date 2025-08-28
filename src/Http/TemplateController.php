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
use DB;


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
        // Entorno tenant específico, sin usar website_id
        $template = \Sitedigitalweb\Pagina\Tenant\Page::findOrFail($id);
        $recaptcha = \Sitedigitalweb\Pagina\Tenant\Cms_Recaptcha::first();
        $web = \Sitedigitalweb\Pagina\Tenant\Cms_Template::first();
    } else {
        // Entorno central (host)
        $template = Page::findOrFail($id);
        $recaptcha = Cms_Recaptcha::first();
        $web = Cms_Template::first();
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

    $templateFolder = $web->template ?? 'default';


    return view('pagina::pages.preview', compact('template', 'content', 'styles', 'scripts', 'tenantData', 'recaptcha', 'web'));
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

    // Suponiendo que en cms_template tienes una columna 'template_name'
    $templateFolder = $web->template ?? 'default';

    return view($templateFolder . '.pages.page', compact('template', 'content', 'styles', 'scripts', 'tenantData', 'recaptcha', 'web', 'menuPages'));

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

    $templateFolder = $web->template ?? 'default';
    
      return view($templateFolder . '.pages.page', compact('template', 'content', 'styles', 'scripts', 'tenantData', 'recaptcha', 'web', 'menuPages'));

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
                'select-input', 'number-input', 'submit-input', 'date-input'
            ])) {
                $innerHtml .= $this->renderInputComponent($child);
            } else {
                $innerHtml .= $this->renderComponent($child);
            }
        }
    }

    // Si es un formulario dinámico, cambiar etiqueta a <form>
    if (($component['type'] ?? '') === 'dynamic-form') {
        $tag = 'form';
    }

    // Construir y retornar HTML final
    return $this->buildFinalHtml($tag, $attrString, $innerHtml, $isImage);
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
        case 'submit-input':
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
        'scripts' => $scripts
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
            $component['type'] === 'submit-input' && 
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