<?php

namespace Sitedigitalweb\Pagina\Http;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Sitedigitalweb\Pagina\Cms_Pais;
use Sitedigitalweb\Pagina\Cms_Template;
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
            'domain' => 'required|string',
        ]);

        $domain       = trim($request->domain);
        $phpFpmSocket = 'unix:/run/php/php8.4-fpm.sock';
        $certPath     = "/etc/letsencrypt/live/{$domain}";

        // Buscar el tenant por dominio
        $tenantDomain = \Stancl\Tenancy\Database\Models\Domain::where('domain', $domain)->first();

        if (! $tenantDomain) {
            return redirect()->back()->with('error', "❌ No se encontró tenant para el dominio {$domain}.");
        }

        $tenant = Tenant::find($tenantDomain->tenant_id);

        try {
            // 1. Verificación DNS
            $serverIp = trim(shell_exec("hostname -I | awk '{print $1}'"));
            $dnsIp    = gethostbyname($domain);

            if (! $serverIp) {
                return redirect()->back()->with('error', '❌ No se pudo obtener la IP del servidor.');
            }

            if ($serverIp !== $dnsIp || ! filter_var($dnsIp, FILTER_VALIDATE_IP)) {
                return redirect()->back()->with('error', "❌ El dominio no apunta al servidor ({$serverIp}). DNS actual: {$dnsIp}");
            }

            // 2. Emitir certificado con Certbot
            $issue = new Process([
                'sudo', 'certbot', 'certonly',
                '--nginx',
                '-d', $domain,
                '-d', "www.{$domain}",
                '--non-interactive',
                '--agree-tos',
                '-m', env('CERTBOT_EMAIL', 'admin@sitedigital.com.co'),
            ]);
            $issue->setTimeout(600);
            $issue->run();

            if (! $issue->isSuccessful()) {
                throw new ProcessFailedException($issue);
            }

            // 3. Crear config Nginx
            $nginxConfig = $this->buildNginxConfig($domain, $phpFpmSocket, $certPath);

            $filePath = "/etc/nginx/sites-available/{$domain}";

            $writeCfg = new Process(['sudo', 'tee', $filePath]);
            $writeCfg->setInput($nginxConfig);
            $writeCfg->run();

            if (! $writeCfg->isSuccessful()) {
                throw new ProcessFailedException($writeCfg);
            }

            $enableCfg = new Process(['sudo', 'ln', '-sf', $filePath, "/etc/nginx/sites-enabled/{$domain}"]);
            $enableCfg->run();

            if (! $enableCfg->isSuccessful()) {
                throw new ProcessFailedException($enableCfg);
            }

            // 4. Recargar Nginx
            $reload = new Process(['sudo', 'systemctl', 'reload', 'nginx']);
            $reload->run();

            if (! $reload->isSuccessful()) {
                throw new ProcessFailedException($reload);
            }

            return redirect()->back()->with('success', "✅ SSL activo para {$domain}.");

        } catch (\Throwable $e) {
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

        if (! $cmsTemplate) {
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
    protected function buildNginxConfig(string $domain, string $phpFpm, string $certPath): string
    {
        return <<<EOL
server {
    listen 80;
    server_name {$domain} www.{$domain};
    return 301 https://{$domain}\$request_uri;
}

server {
    listen 443 ssl http2;
    server_name www.{$domain};
    ssl_certificate {$certPath}/fullchain.pem;
    ssl_certificate_key {$certPath}/privkey.pem;
    return 301 https://{$domain}\$request_uri;
}

server {
    listen 443 ssl http2;
    server_name {$domain};

    ssl_certificate {$certPath}/fullchain.pem;
    ssl_certificate_key {$certPath}/privkey.pem;

    root /var/www/html/public;
    index index.php index.html;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass {$phpFpm};
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOL;
    }
}




