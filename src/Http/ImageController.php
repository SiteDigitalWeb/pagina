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
 
    $uuid = $website ? $website->uuid : 'default';

    $storagePath = public_path("saas/{$uuid}");

    // Crear carpeta si no existe
    if (!File::exists($storagePath)) {
        File::makeDirectory($storagePath, 0755, true);
    }

    if ($request->hasFile('files')) {
        foreach ($request->file('files') as $file) {
            // Tomar nombre original y limpiar caracteres peligrosos
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $safeName = Str::slug($originalName, '_'); // ejemplo: "mi_foto_de_viaje"

            // Evitar colisiones de nombres
            $finalName = $safeName . '.' . $extension;
            $counter = 1;
            while (File::exists($storagePath . '/' . $finalName)) {
                $finalName = $safeName . '_' . $counter . '.' . $extension;
                $counter++;
            }

            // Mover archivo
            $file->move($storagePath, $finalName);

            $uploadedAssets[] = [
                'src' => asset("saas/{$uuid}/{$finalName}"),
                'name' => $finalName,
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
    $website = app(\Hyn\Tenancy\Environment::class)->website();

    // Si no hay tenant o el uuid está vacío, usar "default"
    $uuid = $website && !empty($website->uuid) ? $website->uuid : 'default';

    $folderPath = public_path("saas/{$uuid}");
    $images = [];

    if (File::exists($folderPath)) {
        $files = File::files($folderPath);

        foreach ($files as $file) {
            if (Str::startsWith(File::mimeType($file), 'image/')) {
                $images[] = [
                    'url'  => asset("saas/{$uuid}/" . $file->getFilename()),
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