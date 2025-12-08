<?php

namespace Sitedigitalweb\Pagina\Http;

use Sitedigitalweb\Pagina\PwaManifest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\Controller;

class PwaManifestController extends Controller
{
    public function index()
    {
        $manifests = PwaManifest::all();
        return view('admin.pwa.index', compact('manifests'));
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
            $validated['icons'] = (new PwaManifest())->getDefaultIcons();
        } else {
            $validated['icons'] = $request->icons;
        }

        // Convertir arrays a JSON
        $validated['icons'] = json_encode($validated['icons']);
        $validated['screenshots'] = $request->screenshots ? json_encode($request->screenshots) : null;
        $validated['shortcuts'] = $request->shortcuts ? json_encode($request->shortcuts) : null;
        $validated['categories'] = $request->categories ? json_encode(explode(',', $request->categories)) : null;
        $validated['protocol_handlers'] = $request->protocol_handlers ? json_encode($request->protocol_handlers) : null;

        // Deshabilitar otros manifests si este se activa
        if ($request->enabled) {
            PwaManifest::where('id', '!=', 0)->update(['enabled' => false]);
        }

        PwaManifest::create($validated);

        return redirect()->route('admin.pwa.index')
            ->with('success', 'Manifest creado exitosamente.');
    }

    public function edit(PwaManifest $pwaManifest)
    {
        return view('admin.pwa.edit', compact('pwaManifest'));
    }

    public function update(Request $request, PwaManifest $pwaManifest)
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
            'enabled' => 'boolean'
        ]);

        if ($request->has('icons')) {
            $validated['icons'] = json_encode($request->icons);
        }

        if ($request->has('screenshots')) {
            $validated['screenshots'] = json_encode($request->screenshots);
        }

        if ($request->has('shortcuts')) {
            $validated['shortcuts'] = json_encode($request->shortcuts);
        }

        if ($request->has('categories')) {
            $validated['categories'] = json_encode(explode(',', $request->categories));
        }

        if ($request->has('protocol_handlers')) {
            $validated['protocol_handlers'] = json_encode($request->protocol_handlers);
        }

        // Deshabilitar otros manifests si este se activa
        if ($request->enabled && !$pwaManifest->enabled) {
            PwaManifest::where('id', '!=', $pwaManifest->id)->update(['enabled' => false]);
        }

        $pwaManifest->update($validated);

        return redirect()->route('admin.pwa.index')
            ->with('success', 'Manifest actualizado exitosamente.');
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
    // Usa el namespace completo o importa la clase
    $manifest = PwaManifest::getActive();
    
    if (!$manifest) {
        $manifest = PwaManifest::create([
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

    return response()->json($manifest->toManifestArray())
        ->header('Content-Type', 'application/json')
        ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
}

    
}