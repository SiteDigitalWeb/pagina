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

class TenantController extends Controller
{
    protected ?string $tenantName = null;

    public function __construct()
    {
        // Stancl Tenancy — obtener tenant activo
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
        $domain   = sprintf('%s.%s', $fqdnSlug, env('APP_DOMAIN'));

        // 1. Crear tenant con Stancl — dispara CreateDatabase + MigrateDatabase
        $tenant = Tenant::create([
            'id'   => $fqdnSlug,
            'name' => $request->name,
        ]);

        $tenant->domains()->create(['domain' => $domain]);

        // 2. Crear usuario admin dentro del tenant
        tenancy()->initialize($tenant);

        $user = \App\Models\User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'pais_id'  => $request->pais_id,
        ]);

        $user->assignRole('admin');

        tenancy()->end();

        return redirect('/sd/register-tenant')->with('status', 'ok_create');
    }

    // ── CERTIFICADO SSL CON CERTBOT ───────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'domain' => 'required|string|unique:domains,domain',
        ]);

        $domain = trim($request->domain);
        $phpFpmSocket = 'unix:/run/php/php8.4-fpm.sock';
        $certPath = "/etc/letsencrypt/live/{$domain}";

        try {
            // 1. Verificar que el dominio apunta al servidor
            $serverIp = trim(shell_exec("hostname -I | awk '{print $1}'"));
            $dnsIp = gethostbyname($domain);

            if (!$serverIp) {
                return redirect()->back()->with('error', '❌ No se pudo obtener la IP del servidor.');
            }

            if ($serverIp !== $dnsIp || !filter_var($dnsIp, FILTER_VALIDATE_IP)) {
                return redirect()->back()->with('error', "❌ El dominio no apunta al servidor ({$serverIp}). DNS actual: {$dnsIp}");
            }

            // 2. Buscar o crear el tenant
            $tenantDomain = Domain::where('domain', $domain)->first();
            
            if (!$tenantDomain) {
                // Crear nuevo tenant
                $tenantId = str_replace(['.', '-'], '_', $domain);
                $tenant = Tenant::create([
                    'id' => $tenantId,
                    'name' => $domain,
                    'plan' => 'premium',
                    'status' => 'active',
                ]);
                
                Domain::create([
                    'tenant_id' => $tenant->id,
                    'domain' => $domain,
                    'is_primary' => true,
                    'is_custom' => true,
                ]);
            } else {
                $tenant = Tenant::find($tenantDomain->tenant_id);
            }

            // 3. Generar certificado SSL con Certbot (CORREGIDO - usar Process correctamente)
            $process = new Process([
                'sudo', 'certbot', 'certonly', '--nginx',
                '-d', $domain,
                '-d', "www.{$domain}",
                '--non-interactive',
                '--agree-tos',
                '-m', 'admin@sitedigital.com.co',
                '--keep-until-expiring'
            ]);
            $process->setTimeout(600);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            // 4. Crear configuración Nginx para el dominio
            $nginxConfig = $this->buildNginxConfig($domain, $phpFpmSocket, $certPath);
            
            $configPath = "/etc/nginx/sites-available/{$domain}";
            file_put_contents($configPath, $nginxConfig);
            
            // Crear enlace simbólico
            if (!file_exists("/etc/nginx/sites-enabled/{$domain}")) {
                symlink($configPath, "/etc/nginx/sites-enabled/{$domain}");
            }

            // 5. Recargar Nginx (CORREGIDO)
            $reload = new Process(['sudo', 'systemctl', 'reload', 'nginx']);
            $reload->run();

            return redirect()->back()->with('success', "✅ SSL activo para {$domain}. El tenant ha sido creado/actualizado.");

        } catch (ProcessFailedException $e) {
            Log::error('Error al generar SSL: ' . $e->getMessage());
            return redirect()->back()->with('error', '❌ Error SSL: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Error general: ' . $e->getMessage());
            return redirect()->back()->with('error', '❌ Error: ' . $e->getMessage());
        }
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
        // Si es subdominio de sitekonecta.com, usar el certificado principal
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
}
EOF;
    }
}