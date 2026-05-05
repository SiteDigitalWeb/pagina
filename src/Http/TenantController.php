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

    // ── Dominios permitidos para SSL wildcard ─────────────
    private const WILDCARD_DOMAIN = 'sitekonecta.com';
    private const WILDCARD_CERT_PATH = '/etc/letsencrypt/live/sitekonecta.com-0001';
    private const CERTBOT_EMAIL = 'admin@sitedigital.com.co';
    private const PHP_FPM_SOCKET = 'unix:/run/php/php8.4-fpm.sock';

    public function __construct()
    {
        if (tenancy()->initialized) {
            $this->tenantName = tenant('id');
        }
    }

    // ── VALIDAR DOMINIO (previene inyección de comandos) ──
    private function validateDomain(string $domain): bool
    {
        // Solo caracteres válidos en un dominio: letras, números, guiones y puntos
        return (bool) preg_match('/^[a-zA-Z0-9][a-zA-Z0-9\-\.]{1,251}[a-zA-Z0-9]$/', $domain)
            && !str_contains($domain, '..')
            && substr_count($domain, '.') >= 1;
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
        $domain   = sprintf('%s.%s', $fqdnSlug, env('APP_DOMAIN', self::WILDCARD_DOMAIN));

        // 1. Crear tenant
        $tenant = Tenant::create([
            'id'   => $fqdnSlug,
            'name' => $request->name,
        ]);

        $tenant->domains()->create(['domain' => $domain]);

        // 2. Inicializar tenant y ejecutar seeder
        tenancy()->initialize($tenant);

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

    // ── ACTUALIZAR DOMINIO DEL TENANT ACTUAL ──────────────
    public function store(Request $request)
    {
        $request->validate([
            'domain' => 'required|string|max:253',
        ]);

        $newDomain = strtolower(trim($request->domain));

        // ── Seguridad: validar formato del dominio ────────
        if (!$this->validateDomain($newDomain)) {
            return redirect()->back()->with('error', '❌ El dominio contiene caracteres inválidos.');
        }

        // ── Identificar tenant actual ─────────────────────
        if (tenancy()->initialized) {
            $tenantId = tenant('id');
        } else {
            $currentHost  = $request->getHost();
            $domainRecord = Domain::where('domain', $currentHost)->first();

            if ($domainRecord) {
                $tenantId = $domainRecord->tenant_id;
            } else {
                return redirect()->back()->with('error', '❌ No se pudo identificar el tenant actual.');
            }
        }

        $phpFpmSocket = self::PHP_FPM_SOCKET;
        $certPath     = "/etc/letsencrypt/live/{$newDomain}";

        try {
            // 1. Verificar DNS ─────────────────────────────
            $serverIp = trim(shell_exec("hostname -I | awk '{print $1}'"));
            $dnsIp    = gethostbyname($newDomain);

            if (!$serverIp) {
                return redirect()->back()->with('error', '❌ No se pudo obtener la IP del servidor.');
            }

            if ($serverIp !== $dnsIp || !filter_var($dnsIp, FILTER_VALIDATE_IP)) {
                return redirect()->back()->with('error', "❌ El dominio no apunta al servidor ({$serverIp}). DNS actual: {$dnsIp}");
            }

            // 2. Buscar el tenant ──────────────────────────
            $tenant = Tenant::find($tenantId);

            if (!$tenant) {
                return redirect()->back()->with('error', "❌ Tenant '{$tenantId}' no encontrado.");
            }

            // 3. Actualizar dominio principal ──────────────
            $primaryDomain = Domain::where('tenant_id', $tenantId)
                ->where('is_primary', true)
                ->first();

            if ($primaryDomain) {
                $oldDomain            = $primaryDomain->domain;
                $primaryDomain->domain    = $newDomain;
                $primaryDomain->is_custom = !str_ends_with($newDomain, '.' . self::WILDCARD_DOMAIN);
                $primaryDomain->save();
                $message = "Dominio actualizado: {$oldDomain} → {$newDomain}";
            } else {
                Domain::create([
                    'tenant_id'  => $tenant->id,
                    'domain'     => $newDomain,
                    'is_primary' => true,
                    'is_custom'  => !str_ends_with($newDomain, '.' . self::WILDCARD_DOMAIN),
                ]);
                $message = "Dominio creado: {$newDomain}";
            }

            // 4. Generar SSL si es dominio personalizado ───
            $isSubdomain = str_ends_with($newDomain, '.' . self::WILDCARD_DOMAIN);
            $sslMessage  = '';

            if (!$isSubdomain) {
                $sslResult  = $this->generateSslCertificate($newDomain);
                $sslMessage = $sslResult['message'];
            } else {
                $sslMessage = '✅ SSL cubierto por certificado wildcard';
            }

            // 5. Configurar Nginx ──────────────────────────
            $nginxConfig = $this->buildNginxConfig($newDomain, $phpFpmSocket, $certPath);
            $configPath  = "/etc/nginx/sites-available/{$newDomain}";

            $writeConfig = new Process(['sudo', 'tee', $configPath]);
            $writeConfig->setInput($nginxConfig);
            $writeConfig->run();

            if (!$writeConfig->isSuccessful()) {
                Log::error('Error escribiendo config Nginx: ' . $writeConfig->getErrorOutput());
                return redirect()->back()->with('error', '❌ Error al escribir la configuración de Nginx.');
            }

            $linkPath = "/etc/nginx/sites-enabled/{$newDomain}";
            if (!file_exists($linkPath)) {
                $linkProcess = new Process(['sudo', 'ln', '-sf', $configPath, $linkPath]);
                $linkProcess->run();
            }

            // 6. Validar y recargar Nginx ──────────────────
            $testNginx = new Process(['sudo', 'nginx', '-t']);
            $testNginx->run();

            if (!$testNginx->isSuccessful()) {
                // Revertir config si falla la validación
                $removeProcess = new Process(['sudo', 'rm', '-f', $configPath, $linkPath]);
                $removeProcess->run();
                Log::error('Nginx config inválida: ' . $testNginx->getErrorOutput());
                return redirect()->back()->with('error', '❌ Configuración de Nginx inválida. Se revirtió el cambio.');
            }

            $reload = new Process(['sudo', 'systemctl', 'reload', 'nginx']);
            $reload->run();

            Log::info("Tenant {$tenantId}: dominio actualizado a {$newDomain}");

            return redirect()->back()->with('success', "✅ {$message}. {$sslMessage}");

        } catch (\Exception $e) {
            Log::error('Error en store TenantController: ' . $e->getMessage());
            return redirect()->back()->with('error', '❌ Error inesperado. Revisa los logs.');
        }
    }

    // ── GENERAR SSL MANUALMENTE ───────────────────────────
    public function generateSSL(Request $request)
    {
        $request->validate([
            'domain' => 'required|string|max:253',
        ]);

        $domain = strtolower(trim($request->domain));

        // ── Seguridad: validar formato del dominio ────────
        if (!$this->validateDomain($domain)) {
            return response()->json([
                'success' => false,
                'message' => 'Dominio inválido. Solo se permiten letras, números, guiones y puntos.',
            ], 422);
        }

        $result = $this->generateSslCertificate($domain);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => "SSL generado para {$domain}",
                'output'  => $result['output'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Error generando SSL. Revisa los logs.',
            'output'  => $result['output'],
        ], 500);
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

    // ── HELPERS PRIVADOS ──────────────────────────────────

    /**
     * Genera certificado SSL con Certbot usando Process (sin shell_exec).
     * Retorna array con success, message y output.
     */
    private function generateSslCertificate(string $domain): array
    {
        // Usar Process en lugar de shell_exec para evitar inyección
        $process = new Process([
            'sudo', 'certbot', 'certonly',
            '--nginx',
            '-d', $domain,
            '-d', 'www.' . $domain,
            '--non-interactive',
            '--agree-tos',
            '-m', self::CERTBOT_EMAIL,
        ]);

        $process->setTimeout(120);
        $process->run();

        $output = $process->getOutput() . $process->getErrorOutput();

        if ($process->isSuccessful() || str_contains($output, 'Congratulations')) {
            Log::info("SSL generado correctamente para {$domain}");
            return [
                'success' => true,
                'message' => "✅ SSL generado correctamente para {$domain}",
                'output'  => $output,
            ];
        }

        Log::warning("Certbot falló para {$domain}: " . $output);
        return [
            'success' => false,
            'message' => "⚠️ SSL: Verificar manualmente para {$domain}",
            'output'  => $output,
        ];
    }

    /**
     * Construye la configuración de Nginx para un tenant.
     * Incluye snippets de seguridad (headers + bloqueo de archivos).
     */
    protected function buildNginxConfig(string $domain, string $phpFpmSocket, string $certPath): string
    {
        $sslCertPath = str_ends_with($domain, '.' . self::WILDCARD_DOMAIN)
            ? self::WILDCARD_CERT_PATH
            : $certPath;

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

    # Headers de seguridad y bloqueo de archivos
    include snippets/security-headers.conf;
    include snippets/block-files.conf;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass {$phpFpmSocket};
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.ht {
        deny all;
    }

    client_max_body_size 20M;
}
EOF;
    }
}