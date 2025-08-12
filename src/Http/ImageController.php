<?php

namespace Sitedigitalweb\Pagina\Http;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; // Para generar nombres únicos
use Illuminate\Support\Facades\File;

class ImageController extends Controller
{
    /**
     * Sube una o varias imágenes y las guarda.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
{
    $request->validate([
        'files.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:500',
    ], [
        'files.*.required' => 'Debe seleccionar un archivo.',
        'files.*.image' => 'El archivo debe ser una imagen.',
        'files.*.mimes' => 'El archivo debe ser de tipo: jpeg, png, jpg, gif, svg o webp.',
        'files.*.max' => 'El tamaño máximo permitido para la imagen es 500k.',
    ]);

    $uploadedAssets = [];

    $website = app(\Hyn\Tenancy\Environment::class)->website();
    $uuid = $website->uuid;

    $tenant = $this->tenantName ?? 'default'; // Reemplaza esto con tu método real
    $storagePath = public_path("saas/{$uuid}");

    // Crea la carpeta si no existe
    if (!File::exists($storagePath)) {
        File::makeDirectory($storagePath, 0755, true);
    }

    if ($request->hasFile('files')) {
        foreach ($request->file('files') as $file) {
            $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $file->move($storagePath, $fileName);

            $uploadedAssets[] = [
                'src' => asset("saas/{$uuid}/{$fileName}"),
                'name' => $fileName,
                'type' => $file->getClientMimeType(),
            ];
        }
    }

    return response()->json($uploadedAssets);
}

    /**
     * Lista todas las imágenes guardadas en la carpeta de editor.
     *
     * @return \Illuminate\Http\JsonResponse
     */
   public function index()
{
    $tenant = $this->tenantName ?? 'default'; // Reemplaza según tu lógica
    $folderPath = public_path("saas/{$tenant}");

    $website = app(\Hyn\Tenancy\Environment::class)->website();
    $uuid = $website->uuid;

    $images = [];

    if (File::exists($folderPath)) {
        $files = File::files($folderPath);

        foreach ($files as $file) {
            if (Str::startsWith(File::mimeType($file), 'image/')) {
                $images[] = [
                    'url' => asset("saas/{$uuid}/" . $file->getFilename()),
                    'name' => $file->getFilename(),
                    'path' => "saas/{$uuid}/" . $file->getFilename(),
                ];
            }
        }
    }

    return response()->json($images);
}

    /**
     * Elimina una imagen específica.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
     public function destroy(Request $request)
{
    $request->validate([
        'url' => 'required|string',
    ]);

    $imageUrl = $request->input('url');

    // Extrae solo la ruta desde la URL (elimina protocolo y dominio)
    $parsedPath = parse_url($imageUrl, PHP_URL_PATH); // ej: /saas/default/imagen.png
    $relativePath = ltrim($parsedPath, '/'); // Quita la barra inicial

    // Seguridad: asegurarse que esté dentro de saas/
    if (!Str::startsWith($relativePath, 'saas/')) {
        return response()->json([
            'success' => false,
            'message' => 'La URL proporcionada no apunta a una imagen válida del sistema.',
        ], 400);
    }

    $filePath = public_path($relativePath);

    if (File::exists($filePath)) {
        File::delete($filePath);
        return response()->json([
            'success' => true,
            'message' => 'Imagen eliminada correctamente.'
        ]);
    } else {
        return response()->json([
            'success' => false,
            'message' => 'La imagen no fue encontrada en el servidor.'
        ], 404);
    }
  }
}