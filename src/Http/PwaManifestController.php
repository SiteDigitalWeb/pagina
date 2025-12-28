<?php

namespace Sitedigitalweb\Pagina\Http;

use Sitedigitalweb\Pagina\PwaManifest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\Controller;

class PwaManifestController extends Controller
{

  private function resolveUserModel()
    {
    $website = app(\Hyn\Tenancy\Environment::class)->website();

    return $website 
        ? \Sitedigitalweb\Pagina\Tenant\PwaManifest::class
        : \Sitedigitalweb\Pagina\PwaManifest::class;
    }



    private function getPwaModel()
{
    $website = app(\Hyn\Tenancy\Environment::class)->website();
    
    if ($website) {
        // Estamos en tenant
        return \Sitedigitalweb\Pagina\Tenant\PwaManifest::class;
    } else {
        // Estamos en base central
        return \Sitedigitalweb\Pagina\PwaManifest::class;
    }
}
  public function index()
{
    $env = app(\Hyn\Tenancy\Environment::class);
    $website = $env->website();

    if ($website) {
        $vapid = VapidKey::where('website_id', $website->id)->firstOrFail();
        $publicKey = $vapid->public_key;
    } else {
        // dominio raíz
        $publicKey = config('push.vapid_public_key');
    }

    return view('pwa.home', [
        'vapidPublicKey' => $publicKey,
    ]);
}





    public function create()
    {
        return view('admin.pwa.create');
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'short_name' => 'required|string|max:50',
        'description' => 'nullable|string',
        'start_url' => 'nullable|string',
        'display' => 'required|in:standalone,fullscreen,minimal-ui,browser',
        'background_color' => 'required|string|max:7',
        'theme_color' => 'required|string|max:7',
        'orientation' => 'required|in:any,natural,landscape,portrait',
        'scope' => 'nullable|string',
        'lang' => 'required|string|max:10',
        'dir' => 'required|in:ltr,rtl,auto',
        'icons' => 'nullable|array',
        'screenshots' => 'nullable|array',
        'shortcuts' => 'nullable|array',
        'categories' => 'nullable|array',
        'protocol_handlers' => 'nullable|array',
        'enabled' => 'boolean'
    ]);

    // Si no se proporcionan iconos, usar valores por defecto
    if (empty($request->icons)) {
        $validated['icons'] = (new \Sitedigitalweb\Pagina\PwaManifest())->getDefaultIcons();
    } else {
        $validated['icons'] = $request->icons;
    }

    // Convertir arrays a JSON
    $validated['icons'] = json_encode($validated['icons']);
    $validated['screenshots'] = $request->screenshots ? json_encode($request->screenshots) : null;
    $validated['shortcuts'] = $request->shortcuts ? json_encode($request->shortcuts) : null;
    $validated['categories'] = $request->categories ? json_encode(explode(',', $request->categories)) : null;
    $validated['protocol_handlers'] = $request->protocol_handlers ? json_encode($request->protocol_handlers) : null;

    // Detectar si estamos en tenant o en base central
    $website = app(\Hyn\Tenancy\Environment::class)->website();

    if ($website) {
        // Estamos en tenant - usar el modelo tenant
        $modelClass = \Sitedigitalweb\Pagina\Tenant\PwaManifest::class;
    } else {
        // Estamos en base central - usar el modelo central
        $modelClass = PwaManifest::class;
    }

    // Deshabilitar otros manifests del mismo entorno (tenant o central)
    if ($request->enabled) {
        $modelClass::where('id', '!=', 0)->update(['enabled' => false]);
    }

    // Crear el nuevo manifest
    $modelClass::create($validated);

    return redirect()->route('admin.pwa.index')
        ->with('success', 'Manifest creado exitosamente.');
}

   public function edit($id)
{
    // Detectar si estamos en tenant o en base central
    $website = app(\Hyn\Tenancy\Environment::class)->website();

    if ($website) {
        // Estamos en tenant - usar el modelo tenant
        $pwaManifest = \Sitedigitalweb\Pagina\Tenant\PwaManifest::findOrFail($id);
    } else {
        // Estamos en base central - usar el modelo central
        $pwaManifest = \Sitedigitalweb\Pagina\PwaManifest::findOrFail($id);
    }
    
    return view('admin.pwa.edit', compact('pwaManifest'));
}

    public function update(Request $request, $id)
{
    // Obtener el modelo correcto según el contexto
    $modelClass = $this->getPwaModel();
    $pwaManifest = $modelClass::findOrFail($id);
    
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'short_name' => 'required|string|max:50',
        'description' => 'nullable|string',
        'start_url' => 'nullable|string',
        'display' => 'required|in:standalone,fullscreen,minimal-ui,browser',
        'background_color' => 'required|string|max:7',
        'theme_color' => 'required|string|max:7',
        'orientation' => 'required|in:any,natural,landscape,portrait',
        'scope' => 'nullable|string',
        'lang' => 'required|string|max:10',
        'dir' => 'required|in:ltr,rtl,auto',
        'enabled' => 'boolean',
        'icons' => 'nullable|array',
        'screenshots' => 'nullable|array',
        'shortcuts' => 'nullable|array',
        'categories' => 'nullable|string',
        'protocol_handlers' => 'nullable|array'
    ]);

    // Procesar iconos si se proporcionan
    if ($request->has('icons')) {
        $icons = $this->processIcons($request->icons);
        $validated['icons'] = json_encode($icons);
    }

    // Procesar otros campos JSON
    if ($request->filled('screenshots')) {
        $validated['screenshots'] = json_encode($validated['screenshots']);
    }

    if ($request->filled('shortcuts')) {
        $validated['shortcuts'] = json_encode($validated['shortcuts']);
    }

    if ($request->filled('categories')) {
        $validated['categories'] = json_encode(explode(',', $validated['categories']));
    }

    if ($request->filled('protocol_handlers')) {
        $validated['protocol_handlers'] = json_encode($validated['protocol_handlers']);
    }

    // Deshabilitar otros manifests del mismo contexto si este se activa
    if ($request->enabled && !$pwaManifest->enabled) {
        $modelClass::where('id', '!=', $pwaManifest->id)
            ->update(['enabled' => false]);
    }

    $pwaManifest->update($validated);

    return redirect()->route('admin.pwa.index')
        ->with('success', 'Manifest actualizado exitosamente.');
}

/**
 * Procesar el array de iconos desde el formulario
 */
private function processIcons($iconInputs)
{
    $icons = [];
    
    if (isset($iconInputs['src']) && is_array($iconInputs['src'])) {
        foreach ($iconInputs['src'] as $index => $src) {
            if (!empty($src) && !empty($iconInputs['sizes'][$index])) {
                $icons[] = [
                    'src' => $src,
                    'sizes' => $iconInputs['sizes'][$index],
                    'type' => $iconInputs['type'][$index] ?? 'image/png',
                    'purpose' => $iconInputs['purpose'][$index] ?? 'any'
                ];
            }
        }
    }
    
    return $icons;
}


    public function destroy(PwaManifest $pwaManifest)
    {
        $pwaManifest->delete();
        return redirect()->route('admin.pwa.index')
            ->with('success', 'Manifest eliminado exitosamente.');
    }

    public function toggle(PwaManifest $pwaManifest)
    {
        if (!$pwaManifest->enabled) {
            PwaManifest::where('id', '!=', $pwaManifest->id)->update(['enabled' => false]);
        }
        
        $pwaManifest->update(['enabled' => !$pwaManifest->enabled]);
        
        return back()->with('success', 'Estado del manifest actualizado.');
    }


public function manifest()
{
    // Usa el modelo del tenant correctamente
    $model = $this->resolveUserModel();
    $manifest = $model::getActive();  // Asegúrate que este método esté bien definido

    // Si no existe el manifiesto, lo creamos
    if (!$manifest) {
        $manifest = $model::create([
            'name' => 'SiteCMS',
            'short_name' => 'SiteCMS',
            'description' => 'Sistema de gestión de contenidos PWA',
            'start_url' => '/',
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => '#000000',
            'orientation' => 'any',
            'scope' => '/',
            'lang' => 'es',
            'dir' => 'ltr',
            'enabled' => true
        ]);
    }

    // Devolvemos el manifiesto como un array
    return response()->json([
        'name' => $manifest->name,
        'short_name' => $manifest->short_name,
        'description' => $manifest->description,
        'start_url' => $manifest->start_url,
        'display' => $manifest->display,
        'background_color' => $manifest->background_color,
        'theme_color' => $manifest->theme_color,
        'orientation' => $manifest->orientation,
        'scope' => $manifest->scope,
        'lang' => $manifest->lang,
        'dir' => $manifest->dir,
        'icons' => json_decode($manifest->icons), // Asegúrate de que los iconos estén en formato JSON correctamente
        'screenshots' => $manifest->screenshots ?? null,
        'shortcuts' => $manifest->shortcuts ?? null,
        'categories' => json_decode($manifest->categories) ?? null,
        'edge_side_panel' => $manifest->edge_side_panel ?? null,
        'launch_handler' => $manifest->launch_handler ?? null,
        'handle_links' => $manifest->handle_links ?? 'preferred',
        'protocol_handlers' => $manifest->protocol_handlers ?? null
    ])
    ->header('Content-Type', 'application/json')
    ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
}


    
}