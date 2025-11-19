<?php

namespace Sitedigitalweb\Pagina\Http;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileManagerController extends Controller
{

    protected $tenantName = null;

 public function __construct(){
 $this->middleware('auth');
 $hostname = app(\Hyn\Tenancy\Environment::class)->hostname();
  if($hostname){
  $fqdn = $hostname->fqdn;
  $this->tenantName = explode(".", $fqdn)[0];
 }
 }


    public function index(Request $request)
    {
  $hostname = app(\Hyn\Tenancy\Environment::class)->hostname();

$uuid = $hostname && $hostname->website ? $hostname->website->uuid : null;

$tenant = $uuid ?? 'default';

        $folder = $request->get('folder', '');
        $currentPath = $tenant . ($folder ? "/{$folder}" : '');
        
        \Log::info("=== FILE MANAGER INDEX ===");
        \Log::info("Tenant: {$tenant}");
        \Log::info("Current Path: {$currentPath}");
        
        // Verificar físicamente qué archivos hay
        $physicalPath = public_path("saas/{$currentPath}");
        \Log::info("Physical path: {$physicalPath}");
        \Log::info("Physical path exists: " . (is_dir($physicalPath) ? 'YES' : 'NO'));
        
        if (is_dir($physicalPath)) {
            $physicalFiles = scandir($physicalPath);
            $imageFiles = array_filter($physicalFiles, function($file) {
                return preg_match('/\.(jpg|jpeg|png|gif|bmp|webp|svg|ico)$/i', $file);
            });
            \Log::info("Physical image files: " . json_encode(array_values($imageFiles)));
        }
        
        // Usar disk 'public'
        $files = Storage::disk('public')->files($currentPath);
        $directories = Storage::disk('public')->directories($currentPath);
        
        \Log::info("Storage files found: " . count($files));
        \Log::info("Storage directories found: " . count($directories));
        
        $fileList = [];
        foreach ($files as $file) {
            if (preg_match('/\.(jpg|jpeg|png|gif|bmp|webp|svg|ico)$/i', $file)) {
                // Generar URL RELATIVA (sin dominio)
                $fileUrl = $this->getRelativePath($file);
                
                // Verificar que el archivo existe físicamente
                $physicalFile = public_path("saas/{$file}");
                $fileExists = file_exists($physicalFile);
                
                \Log::info("Processing file: {$file}");
                \Log::info(" - Relative Path: {$fileUrl}");
                \Log::info(" - Physical exists: " . ($fileExists ? 'YES' : 'NO'));
                
                if ($fileExists) {
                    $fileSize = filesize($physicalFile);
                    $fileModified = filemtime($physicalFile);
                    
                    $fileList[] = [
                        'name' => basename($file),
                        'url' => $fileUrl,
                        'path' => $file,
                        'type' => 'file',
                        'size' => $this->formatBytes($fileSize),
                        'modified' => $fileModified,
                        'physical_exists' => true
                    ];
                    
                    \Log::info(" - ADDED to fileList");
                } else {
                    \Log::warning(" - SKIPPED - File doesn't exist physically");
                }
            }
        }
        
        \Log::info("Total files in fileList: " . count($fileList));
        
        // Ordenar archivos por fecha de modificación
        usort($fileList, function($a, $b) {
            return $b['modified'] - $a['modified'];
        });
        
        $folderList = [];
        foreach ($directories as $dir) {
            $folderList[] = [
                'name' => basename($dir),
                'path' => $dir,
                'type' => 'folder'
            ];
        }
        
        return view('file-manager.index', compact('fileList', 'folderList', 'currentPath', 'tenant'));
    }
    
    /**
     * Generar RUTA RELATIVA (sin dominio)
     */
    private function getRelativePath($filePath)
    {
        return '/saas/' . $filePath;
    }
    
    public function upload(Request $request)
    {
        \Log::info("=== FILE UPLOAD START ===");
        
        try {
            $request->validate([
                'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg,ico|max:2048',
                'tenant' => 'required|string',
                'folder' => 'nullable|string'
            ]);
            
            $hostname = app(\Hyn\Tenancy\Environment::class)->hostname();

$uuid = $hostname && $hostname->website ? $hostname->website->uuid : null;

$tenant = $uuid ?? 'default';
            $folder = $request->folder ?: '';
            $file = $request->file('file');
            
            \Log::info("Upload - Tenant: {$tenant}, Folder: {$folder}");
            \Log::info("File: " . $file->getClientOriginalName());
            \Log::info("File size: " . $file->getSize());
            
            // Crear nombre único
            $fileName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) 
                       . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $tenant . ($folder ? "/{$folder}" : '');
            
            \Log::info("Target path: {$path}");
            \Log::info("File name: {$fileName}");
            
            // RUTA FÍSICA ABSOLUTA
            $physicalDir = public_path("saas/{$path}");
            $physicalPath = public_path("saas/{$path}/{$fileName}");
            
            \Log::info("Physical directory: {$physicalDir}");
            \Log::info("Physical file path: {$physicalPath}");
            
            // VERIFICAR Y CREAR DIRECTORIO MANUALMENTE
            if (!is_dir($physicalDir)) {
                \Log::info("Directory doesn't exist, creating: {$physicalDir}");
                $mkdirResult = mkdir($physicalDir, 0755, true);
                \Log::info("mkdir result: " . ($mkdirResult ? 'SUCCESS' : 'FAILED'));
                
                if (!$mkdirResult) {
                    throw new \Exception("No se pudo crear el directorio: {$physicalDir}");
                }
            }
            
            // VERIFICAR PERMISOS DEL DIRECTORIO
            \Log::info("Directory exists: " . (is_dir($physicalDir) ? 'YES' : 'NO'));
            \Log::info("Directory writable: " . (is_writable($physicalDir) ? 'YES' : 'NO'));
            
            // GUARDAR MANUALMENTE
            \Log::info("Attempting manual file save...");
            $manualSave = $file->move($physicalDir, $fileName);
            \Log::info("Manual save result: " . ($manualSave ? 'SUCCESS' : 'FAILED'));
            
            if (!$manualSave) {
                throw new \Exception('No se pudo guardar el archivo manualmente');
            }
            
            // VERIFICAR QUE EL ARCHIVO EXISTE FÍSICAMENTE
            $fileExists = file_exists($physicalPath);
            \Log::info("Physical file exists after save: " . ($fileExists ? 'YES' : 'NO'));
            
            if (!$fileExists) {
                throw new \Exception('El archivo no se creó físicamente');
            }
            
            $fileSize = filesize($physicalPath);
            \Log::info("Physical file size: {$fileSize} bytes");
            
            // Generar RUTA RELATIVA (sin dominio)
            $fileUrl = $this->getRelativePath("{$path}/{$fileName}");
            \Log::info("Generated Relative Path: {$fileUrl}");
            
            return response()->json([
                'success' => true,
                'url' => $fileUrl,
                'name' => $fileName,
                'path' => "{$path}/{$fileName}",
                'physical_path' => $physicalPath
            ]);
            
        } catch (\Exception $e) {
            \Log::error("UPLOAD ERROR: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al subir archivo: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function createFolder(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'tenant' => 'required|string',
                'current_path' => 'required|string'
            ]);
            
            $newFolderPath = $request->current_path . '/' . $request->name;
            
            if (Storage::disk('public')->exists($newFolderPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La carpeta ya existe'
                ]);
            }
            
            $result = Storage::disk('public')->makeDirectory($newFolderPath);
            
            if (!$result) {
                throw new \Exception('No se pudo crear la carpeta');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Carpeta creada correctamente'
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Error creando carpeta: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear carpeta: ' . $e->getMessage()
            ], 500);
        }
    }
    
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}