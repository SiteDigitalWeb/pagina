<?php

namespace Sitedigitalweb\Pagina\Http;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Sitedigitalweb\Pagina\Cms_Pais;
use Sitedigitalweb\Pagina\Cms_Template;
use Stancl\Tenancy\Database\Models\Domain;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Database\Seeders\TenantSeeder;

class TenantController extends Controller
{
    protected ?string $tenantName = null;

    public function __construct()
    {
        if (tenancy()->initialized) {
            $this->tenantName = tenant('id');
        }
    }

    // ── REGISTRO DE NUEVO TENANT ──────────────────────────
    public function register()
    {
        $paises = $this->tenantName
            ? \Sitedigitalweb\Pagina\Tenant\Cms_Pais::orderBy('pais')->get()
            : Cms_Pais::orderBy('pais')->get();

        return view('pagina::tenants.register', [
            'tenantName' => $this->tenantName,
            'paises'     => $paises,
        ]);
    }

    // ── CREAR TENANT ──────────────────────────────────────
    public function create(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name'                  => 'required|string|max:255',
        'email'                 => 'required|email|unique:users,email',
        'fqdn'                  => ['required', 'regex:/^[a-zA-Z0-9\-]+$/', 'unique:tenants,id'],
        'password'              => 'required|string|min:8|same:password_confirmation',
        'password_confirmation' => 'required|string|min:8',
        'date'                  => 'required|date',
        'plan'                  => 'required|integer',
    ], [
        'email.unique'  => 'Este correo ya está registrado.',
        'fqdn.regex'    => 'El hostname solo puede contener letras, números y guiones.',
        'fqdn.unique'   => 'Este hostname ya está en uso.',
        'password.same' => 'Las contraseñas no coinciden.',
    ]);

    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput();
    }

    $fqdnSlug = $request->fqdn;
    $domain   = sprintf('%s.%s', $fqdnSlug, env('APP_DOMAIN', 'sitekonecta.com'));

    // 1. Crear tenant
    $tenant = Tenant::create([
        'id'   => $fqdnSlug,
        'name' => $request->name,
    ]);

    $tenant->domains()->create(['domain' => $domain]);

    // 2. 🔥 INICIALIZAR TENANT Y EJECUTAR SEEDER 🔥
    tenancy()->initialize($tenant);
    
    // Ejecutar TenantSeeder para insertar datos iniciales
    $seeder = new \Database\Seeders\TenantSeeder();
    $seeder->run();
    
    // 3. Crear usuario admin dentro del tenant
    $user = \App\Models\User::create([
        'name'     => $request->name,
        'email'    => $request->email,
        'password' => Hash::make($request->password),
        'pais_id'  => $request->pais_id,
    ]);

    if (class_exists('Spatie\Permission\Models\Role')) {
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
        $user->assignRole($role);
    }

    // 4. Salir del tenant
    tenancy()->end();

    return redirect('/sd/register-tenant')->with('status', 'ok_create');
}

    // ── ACTUALIZAR DOMINIO DEL TENANT EXISTENTE ───────────
    // ── ACTUALIZAR DOMINIO DEL TENANT EXISTENTE ───────────
// ── ACTUALIZAR DOMINIO DEL TENANT ACTUAL ───────────
public function store(Request $request)
{
    $request->validate([
        'domain' => 'required|string',
    ]);

    // 🔥 IDENTIFICAR TENANT ACTUAL
    if (tenancy()->initialized) {
        $tenantId = tenant('id');
    } else {
        // Si no hay tenant inicializado, intentar por dominio actual
        $currentHost = $request->getHost();
        $domainRecord = Domain::where('domain', $currentHost)->first();
        
        if ($domainRecord) {
            $tenantId = $domainRecord->tenant_id;
        } else {
            return redirect()->back()->with('error', '❌ No se pudo identificar el tenant actual.');
        }
    }

    $newDomain = trim($request->domain);
    $phpFpmSocket = 'unix:/run/php/php8.4-fpm.sock';
    $certPath = "/etc/letsencrypt/live/{$newDomain}";

    try {
        // 1. Verificar DNS
        $serverIp = trim(shell_exec("hostname -I | awk '{print $1}'"));
        $dnsIp = gethostbyname($newDomain);

        if (!$serverIp) {
            return redirect()->back()->with('error', '❌ No se pudo obtener la IP del servidor.');
        }

        if ($serverIp !== $dnsIp || !filter_var($dnsIp, FILTER_VALIDATE_IP)) {
            return redirect()->back()->with('error', "❌ El dominio no apunta al servidor ({$serverIp}). DNS actual: {$dnsIp}");
        }

        // 2. Buscar el tenant actual
        $tenant = Tenant::find($tenantId);
        
        if (!$tenant) {
            return redirect()->back()->with('error', "❌ Tenant '{$tenantId}' no encontrado.");
        }

        // 3. Actualizar el dominio principal
        $primaryDomain = Domain::where('tenant_id', $tenantId)->where('is_primary', true)->first();
        
        if ($primaryDomain) {
            $oldDomain = $primaryDomain->domain;
            $primaryDomain->domain = $newDomain;
            $primaryDomain->is_custom = !str_ends_with($newDomain, '.sitekonecta.com');
            $primaryDomain->save();
            $message = "Dominio actualizado: {$oldDomain} → {$newDomain}";
        } else {
            Domain::create([
                'tenant_id' => $tenant->id,
                'domain' => $newDomain,
                'is_primary' => true,
                'is_custom' => !str_ends_with($newDomain, '.sitekonecta.com'),
            ]);
            $message = "Dominio creado: {$newDomain}";
        }

        // 4. Generar SSL si es necesario
        $isSubdomain = str_ends_with($newDomain, '.sitekonecta.com');
        
        if (!$isSubdomain) {
            $command = "sudo certbot certonly --nginx -d {$newDomain} -d www.{$newDomain} --non-interactive --agree-tos -m admin@sitedigital.com.co 2>&1";
            $output = shell_exec($command);
            
            if (strpos($output, 'Congratulations') !== false) {
                $sslMessage = "✅ SSL generado correctamente";
            } else {
                Log::warning('Certbot output: ' . $output);
                $sslMessage = "⚠️ SSL: Verificar manualmente";
            }
        } else {
            $sslMessage = "✅ SSL cubierto por certificado wildcard";
        }

        // 5. Configurar Nginx
        $nginxConfig = $this->buildNginxConfig($newDomain, $phpFpmSocket, $certPath);
        $configPath = "/etc/nginx/sites-available/{$newDomain}";
        
        $writeConfig = new Process(['sudo', 'tee', $configPath]);
        $writeConfig->setInput($nginxConfig);
        $writeConfig->run();
        
        $linkPath = "/etc/nginx/sites-enabled/{$newDomain}";
        if (!file_exists($linkPath)) {
            $linkProcess = new Process(['sudo', 'ln', '-sf', $configPath, $linkPath]);
            $linkProcess->run();
        }

        // 6. Recargar Nginx
        $reload = new Process(['sudo', 'systemctl', 'reload', 'nginx']);
        $reload->run();

        return redirect()->back()->with('success', "✅ {$message}. {$sslMessage}");

    } catch (\Exception $e) {
        Log::error('Error: ' . $e->getMessage());
        return redirect()->back()->with('error', '❌ Error: ' . $e->getMessage());
    }
}

    // ── GENERAR SSL MANUALMENTE ───────────────────────────
    public function generateSSL(Request $request)
    {
        $request->validate([
            'domain' => 'required|string',
        ]);

        $domain = trim($request->domain);
        
        $command = "sudo certbot certonly --nginx -d {$domain} -d www.{$domain} --non-interactive --agree-tos -m admin@sitedigital.com.co 2>&1";
        $output = shell_exec($command);
        
        if (strpos($output, 'Congratulations') !== false) {
            return response()->json(['success' => true, 'message' => "SSL generado para {$domain}", 'output' => $output]);
        }
        
        return response()->json(['success' => false, 'message' => 'Error generando SSL', 'output' => $output], 500);
    }

    // ── GRAPE COMPONENTS ──────────────────────────────────
    public function getGrapeComponents(Request $request)
    {
        $templateQuery = $this->tenantName
            ? \Sitedigitalweb\Pagina\Tenant\Cms_Template::query()
            : Cms_Template::query();

        $cmsTemplate = $templateQuery->first();

        if (!$cmsTemplate) {
            return response()->json(['error' => 'No active template found'], 404);
        }

        $template = $cmsTemplate->template;
        $files    = glob(resource_path("views/{$template}/*.blade.php"));

        if (empty($files)) {
            return response()->json(['error' => 'No components found'], 404);
        }

        $components = collect($files)->map(function ($file) use ($template) {
            $name = basename($file, '.blade.php');
            return [
                'id'       => $name,
                'label'    => ucfirst(str_replace('-', ' ', $name)),
                'content'  => view("{$template}.{$name}")->render(),
                'category' => 'Mis Componentes',
            ];
        });

        return response()->json($components);
    }

    public function certificate()
    {
        return view('pagina::certificate.certificate');
    }

    // ── HELPERS ───────────────────────────────────────────
    protected function buildNginxConfig($domain, $phpFpmSocket, $certPath)
    {
        if (str_ends_with($domain, '.sitekonecta.com')) {
            $sslCertPath = "/etc/letsencrypt/live/sitekonecta.com-0001";
        } else {
            $sslCertPath = $certPath;
        }
        
        return <<<EOF
server {
    listen 80;
    server_name {$domain} www.{$domain};
    return 301 https://\$server_name\$request_uri;
}

server {
    listen 443 ssl http2;
    server_name {$domain} www.{$domain};
    root /var/www/sitecms/public;
    index index.php;

    ssl_certificate {$sslCertPath}/fullchain.pem;
    ssl_certificate_key {$sslCertPath}/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass {$phpFpmSocket};
    }

    location ~ /\.ht {
        deny all;
    }
    
    client_max_body_size 20M;
}
EOF;
    }
}