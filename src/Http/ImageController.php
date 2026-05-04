<?php

namespace Sitedigitalweb\Pagina\Http;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageController extends Controller
{
    /**
     * Optimiza una imagen usando GD (nativo de PHP)
     * Reduce el tamaño sin pérdida visible de calidad
     *
     * @param string $imagePath Ruta de la imagen a optimizar
     * @param int $quality Calidad (1-100, default: 75)
     * @return bool
     */
    private function optimizeImage($imagePath, $quality = 75)
    {
        // Verificar que GD está instalado
        if (!extension_loaded('gd')) {
            \Log::warning('GD extension no está disponible. No se pudo optimizar la imagen.');
            return false;
        }

        try {
            $imageInfo = getimagesize($imagePath);
            if (!$imageInfo) {
                return false;
            }

            $mime = $imageInfo['mime'];
            $optimized = false;

            switch ($mime) {
                case 'image/jpeg':
                case 'image/jpg':
                    $img = imagecreatefromjpeg($imagePath);
                    if ($img) {
                        imagejpeg($img, $imagePath, $quality);
                        $optimized = true;
                    }
                    break;
                    
                case 'image/png':
                    $img = imagecreatefrompng($imagePath);
                    if ($img) {
                        // Preservar transparencia
                        imagealphablending($img, true);
                        imagesavealpha($img, true);
                        // PNG: calidad 0-9 (9 = máxima compresión)
                        $pngQuality = 9 - round(($quality / 100) * 9);
                        imagepng($img, $imagePath, $pngQuality);
                        $optimized = true;
                    }
                    break;
                    
                case 'image/webp':
                    if (function_exists('imagewebp')) {
                        $img = imagecreatefromwebp($imagePath);
                        if ($img) {
                            imagewebp($img, $imagePath, $quality);
                            $optimized = true;
                        }
                    }
                    break;
                    
                case 'image/gif':
                    $img = imagecreatefromgif($imagePath);
                    if ($img) {
                        imagegif($img, $imagePath);
                        $optimized = true;
                    }
                    break;
            }

            if (isset($img) && is_resource($img)) {
                imagedestroy($img);
            }

            return $optimized;

        } catch (\Exception $e) {
            \Log::error('Error optimizando imagen: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Sube una o varias imágenes y las guarda en DigitalOcean Spaces.
     * Optimiza automáticamente las imágenes antes de subirlas.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        $request->validate([
            'files.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120', // 5MB máximo
        ], [
            'files.*.required' => 'Debe seleccionar un archivo.',
            'files.*.image'    => 'El archivo debe ser una imagen.',
            'files.*.mimes'    => 'El archivo debe ser de tipo: jpeg, png, jpg, gif, svg o webp.',
            'files.*.max'      => 'El tamaño máximo permitido es 5MB.',
        ]);

        $uploadedAssets = [];
        $disk = 'spaces';
        $tenantId = tenancy()->initialized ? tenant('id') : 'default';

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                // Obtener información original antes de optimizar
                $originalSize = $file->getSize();
                $tempPath = $file->getPathname();
                
                // OPTIMIZAR LA IMAGEN ANTES DE SUBIR
                $optimized = $this->optimizeImage($tempPath, 75);
                
                // Obtener nuevo tamaño después de optimizar
                $optimizedSize = file_exists($tempPath) ? filesize($tempPath) : $originalSize;
                
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $safeName = Str::slug($originalName, '_');
                $finalName = $safeName . '.' . $extension;

                // Prevenir duplicados
                $counter = 1;
                while (Storage::disk($disk)->exists("{$tenantId}/{$finalName}")) {
                    $finalName = $safeName . '_' . $counter . '.' . $extension;
                    $counter++;
                }

                // Subir a DigitalOcean Spaces (archivo ya optimizado)
                $path = $file->storeAs(
                    "{$tenantId}",
                    $finalName,
                    ['disk' => $disk]
                );

                // Calcular porcentaje de ahorro
                $savings = $originalSize > 0 
                    ? round((1 - $optimizedSize / $originalSize) * 100, 1) 
                    : 0;

                $uploadedAssets[] = [
                    'src'  => Storage::disk($disk)->url("{$tenantId}/{$finalName}"),
                    'name' => $finalName,
                    'type' => $file->getClientMimeType(),
                    'size' => $optimizedSize,
                    'original_size' => $originalSize,
                    'optimized' => $optimized,
                    'savings_percent' => $savings,
                ];
            }
        }

        return response()->json($uploadedAssets);
    }

    /**
     * Lista todas las imágenes del tenant actual en DigitalOcean Spaces.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $tenantId = tenancy()->initialized ? tenant('id') : 'default';
        $disk = 'spaces';
        $images = [];

        try {
            $files = Storage::disk($disk)->files("{$tenantId}");
            
            foreach ($files as $file) {
                $mimeType = Storage::disk($disk)->mimeType($file);
                if (str_starts_with($mimeType, 'image/')) {
                    $images[] = [
                        'url'  => Storage::disk($disk)->url($file),
                        'name' => basename($file),
                        'path' => $file,
                        'type' => $mimeType,
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error al listar imágenes desde Spaces: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar las imágenes'], 500);
        }

        return response()->json($images);
    }

    /**
     * Elimina una imagen de DigitalOcean Spaces.
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
        $disk = 'spaces';

        try {
            // Extraer la ruta relativa de la URL
            $parsedUrl = parse_url($imageUrl);
            $path = ltrim($parsedUrl['path'] ?? '', '/');
            
            // Si la URL incluye el bucket, limpiar
            $bucket = env('DO_SPACES_BUCKET');
            $path = str_replace($bucket . '/', '', $path);

            // Verificar que la ruta esté dentro de saas/
            if (!str_starts_with($path, '')) {
                return response()->json([
                    'success' => false,
                    'message' => 'La URL proporcionada no pertenece al sistema.',
                ], 400);
            }

            // Verificar que el archivo existe
            if (!Storage::disk($disk)->exists($path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La imagen no fue encontrada en el servidor.',
                ], 404);
            }

            // Eliminar el archivo
            Storage::disk($disk)->delete($path);

            return response()->json([
                'success' => true,
                'message' => 'Imagen eliminada correctamente.',
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al eliminar imagen de Spaces: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la imagen: ' . $e->getMessage(),
            ], 500);
        }
    }
}