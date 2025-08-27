<?php

namespace Sitedigitalweb\Pagina\Http;
use App\Providers\RouteServiceProvider;


use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Session;
use DigitalsiteSaaS\Carrito\Transaccion;
use DigitalsiteSaaS\Pagina\Credencial;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Models\Website;
use Hyn\Tenancy\Repositories\HostnameRepository;
use Hyn\Tenancy\Repositories\WebsiteRepository;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\User;
use App\Models\Tenant;
use Input;
use File;
use Redirect;
use GuzzleHttp\Client;
use DB;
use Mail;
use DigitalsiteSaaS\Usuario\Usuario;
use Auth;
use Sitedigitalweb\Pagina\Cms_Pais;
use Sitedigitalweb\Pagina\Cms_Template;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Carbon\Carbon;


class TenantController extends Controller{
    
use RegistersUsers;

protected $tenantName = null;

  public function __construct(){
   $hostname = app(\Hyn\Tenancy\Environment::class)->hostname();
    if ($hostname){
     $fqdn = $hostname->fqdn;
     $this->tenantName = explode(".", $fqdn)[0];
    }
  }
  
 protected function create(Request $request){
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'fqdn' => ['required', 'regex:/^[a-zA-Z0-9\-]+$/', 'unique:tenancy.hostnames,fqdn'],
        'password' => 'required|string|min:8|same:password_confirmation',
        'password_confirmation' => 'required|string|min:8',
        'date' => 'required|date',
        'plan' => 'required|integer',
    ], [
        'email.unique' => 'Este correo ya está registrado.',
        'fqdn.regex' => 'El hostname solo puede contener letras, números y guiones (sin espacios ni caracteres especiales).',
        'fqdn.unique' => 'Este hostname ya está en uso.',
        'password.same' => 'Las contraseñas no coinciden.',
    ]);

    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput();
    }

    $name = Input::get('name');
    $email = Input::get('email');
    $fqdns = Input::get('fqdn');
    $fecha_actual = Input::get('date');
    $plan = Input::get('plan');
    $password = Input::get('password');
    $pais_id = Input::get('pais_id');
    $passwordhash = Hash::make($password);

    $fqdn = sprintf('%s.%s', $fqdns, env('APP_DOMAIN'));

    $website = new Website;
    $website->uuid = Str::random(10);

    // ✅ Crear carpeta en public/saas/{uuid}
    $path = public_path("saas/" . $website->uuid);
    if (!File::exists($path)) {
        File::makeDirectory($path, 0775, true);
    }

    app(WebsiteRepository::class)->create($website);

    $hostname = new Hostname();
    $hostname->fqdn = $fqdn;
    $hostname = app(HostnameRepository::class)->create($hostname);
    app(HostnameRepository::class)->attach($hostname, $website);

    DB::table('sitecms.users')->insert([
        'name' => $name,
        'email' => $email,
        'remember_token' => $passwordhash,
        'password' => $passwordhash,
        'rol_id' => 4,
        'saas_id' => $hostname->website_id,
        'pais_id' => $pais_id
    ]);

    DB::table('tenancy.hostnames')
        ->where('id', $hostname->id)
        ->update([
            'presentacion' => date("Y-m-d", strtotime($fecha_actual)),
            'plan_id' => $plan
        ]);

    $mihost = $website->uuid . '.';
    DB::table($mihost . 'users')
        ->where('id', 1)
        ->update(['password' => Hash::make($password)]);

    return Redirect('/sd/register-tenant')->with('status', 'ok_create');
 }

 public function register(){
  $paises = ($this->tenantName 
    ? \Sitedigitalweb\Pagina\Tenant\Cms_Pais::query()
    : Cms_Pais::query())
    ->orderBy('pais') // o el campo que uses
    ->get();

return view('pagina::tenants.register', [
    'tenantName' => $this->tenantName, // Asegúrate de que $this->tenantName exista
    'paises' => $paises
]);

 }


   public function getGrapeComponents(Request $request)
    {
        // Identificar el template desde la BD considerando el tenant
        $templateQuery = ($this->tenantName
            ? \Sitedigitalweb\Pagina\Tenant\Cms_Template::query()
            : Cms_Template::query());

        $cmsTemplate = $templateQuery->first();

        if (!$cmsTemplate) {
            return response()->json(['error' => 'No active template found'], 404);
        }

        $template = $cmsTemplate->template; // Campo donde está el nombre del template

        $componentsPath = resource_path('views/' . $template);
        $files = glob($componentsPath . '/*.blade.php');

        if (empty($files)) {
            return response()->json(['error' => 'No components found'], 404);
        }

        $components = [];

        foreach ($files as $file) {
            $name = basename($file, '.blade.php');
            $html = view("$template.$name")->render();

            $components[] = [
                'id' => $name,
                'label' => ucfirst(str_replace('-', ' ', $name)),
                'content' => $html,
                'category' => 'Mis Componentes',
            ];
        }

        return response()->json($components);
    }

public function certificate()
{
    return view('pagina::certificate.certificate');
}

public function store(Request $request)
{
    $request->validate([
        'domain' => 'required|unique:tenants,domain'
    ]);

    $domain = trim($request->domain);

    // 1) Crear el tenant en estado "pending"
    $tenant = Tenant::create([
        'domain'         => $domain,
        'ssl_status'     => 'pending',
        'dns_verified'   => false,
    ]);

    // Definir valores reutilizables
    $phpFpmSocket = 'unix:/run/php/php8.3-fpm.sock';
    $certPath = "/etc/letsencrypt/live/{$domain}";

    try {
        /** =============================
         * 2) Verificación DNS
         ============================== */
        $serverIp = trim(shell_exec("hostname -I | awk '{print $1}'"));

        if (!$serverIp) {
            $tenant->update([
                'ssl_status'  => 'dns_error',
                'last_error'  => 'No fue posible obtener la IP del servidor para la verificación DNS.',
            ]);
            return redirect()->back()->with('error', "❌ DNS no verificado: no se pudo obtener la IP del servidor.");
        }

        $dnsIp = gethostbyname($domain);

        if ($serverIp === $dnsIp && filter_var($dnsIp, FILTER_VALIDATE_IP)) {
            $tenant->update([
                'dns_verified'    => true,
                'dns_verified_at' => Carbon::now(),
            ]);
        } else {
            $tenant->update([
                'ssl_status'  => 'dns_error',
                'last_error'  => "El dominio {$domain} no apunta a la IP del servidor ({$serverIp}). DNS actual: {$dnsIp}",
            ]);
            return redirect()->back()->with('error', "❌ DNS no verificado: El dominio no apunta a la IP del servidor ({$serverIp}). DNS: {$dnsIp}");
        }

        /** =============================
         * 3) Emitir certificado con Certbot
         ============================== */
        $issue = new Process([
            'sudo', 'certbot', 'certonly',
            '--nginx', '-d', $domain,
            '--non-interactive',
            '--agree-tos',
            '-m', 'soporte@tudominio.com',
        ]);
        $issue->setTimeout(600);
        $issue->run();

        if (!$issue->isSuccessful()) {
            $tenant->update([
                'ssl_status' => 'error',
                'last_error' => $issue->getErrorOutput() ?: $issue->getOutput(),
            ]);
            throw new ProcessFailedException($issue);
        }

        /** =============================
         * 4) Registrar info del certificado
         ============================== */
        $sslIssuedAt = Carbon::now();
        $endDateRaw = trim(shell_exec("sudo openssl x509 -enddate -noout -in {$certPath}/cert.pem | cut -d= -f2"));

        $sslExpiresAt = null;
        try {
            if (!empty($endDateRaw)) {
                $sslExpiresAt = Carbon::parse($endDateRaw);
            }
        } catch (\Throwable $t) {
            $tenant->update([
                'last_error' => ($tenant->last_error ? $tenant->last_error . ' | ' : '') . "No se pudo parsear la fecha de expiración del certificado: {$endDateRaw}"
            ]);
        }

        $tenant->update([
            'ssl_status'       => 'active',
            'certificate_path' => $certPath,
            'ssl_issued_at'    => $sslIssuedAt,
            'ssl_expires_at'   => $sslExpiresAt,
        ]);

        /** =============================
         * 5) Crear bloque Nginx
         ============================== */
        $nginxConfig = <<<'EOL'

server {
    listen 443 ssl http2;
    server_name www.__DOMAIN__;

    ssl_certificate /etc/letsencrypt/live/__DOMAIN__/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/__DOMAIN__/privkey.pem;

    return 301 https://__DOMAIN__$request_uri;
}

server {
    listen 80;
    server_name www.__DOMAIN__;
    return 301 https://__DOMAIN__$request_uri;
}

# Redirección HTTP sin www -> HTTPS sin www
server {
    listen 80;
    server_name __DOMAIN__;
    return 301 https://__DOMAIN__$request_uri;
}

server {
    listen 443 ssl http2;
    server_name __DOMAIN__;

    ssl_certificate /etc/letsencrypt/live/__DOMAIN__/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/__DOMAIN__/privkey.pem;

    root /var/www/laravel_app/public;
    index index.php index.html;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass __PHP_FPM__;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOL;

        $nginxConfig = str_replace(['__DOMAIN__', '__PHP_FPM__'], [$domain, $phpFpmSocket], $nginxConfig);
        
        $filePath = "/etc/nginx/sites-available/{$domain}";
        $writeCfg = new Process(['sudo', 'tee', $filePath]);
        $writeCfg->setInput($nginxConfig);
        $writeCfg->run();

        if (!$writeCfg->isSuccessful()) {
            $tenant->update([
                'ssl_status' => 'error',
                'last_error' => "No se pudo escribir la config Nginx: " . ($writeCfg->getErrorOutput() ?: $writeCfg->getOutput()),
            ]);
            throw new ProcessFailedException($writeCfg);
        }

        $enableCfg = new Process(['sudo', 'ln', '-sf', $filePath, "/etc/nginx/sites-enabled/{$domain}"]);
        $enableCfg->run();

        if (!$enableCfg->isSuccessful()) {
            $tenant->update([
                'ssl_status' => 'error',
                'last_error' => "No se pudo habilitar la config Nginx: " . ($enableCfg->getErrorOutput() ?: $enableCfg->getOutput()),
            ]);
            throw new ProcessFailedException($enableCfg);
        }

        /** =============================
         * 6) Recargar Nginx
         ============================== */
        $reload = new Process(['sudo', 'systemctl', 'reload', 'nginx']);
        $reload->run();

        if (!$reload->isSuccessful()) {
            $tenant->update([
                'ssl_status' => 'error',
                'last_error' => "No se pudo recargar Nginx: " . ($reload->getErrorOutput() ?: $reload->getOutput()),
            ]);
            throw new ProcessFailedException($reload);
        }

        /** =============================
         * 7) OK
         ============================== */
        return redirect()->back()->with('success', "✅ Tenant {$domain} creado correctamente, DNS verificado y SSL activo.");

    } catch (\Throwable $e) {
        $newStatus = $tenant->ssl_status === 'dns_error' ? 'dns_error' : 'error';
        $tenant->update([
            'ssl_status' => $newStatus,
            'last_error' => $e->getMessage(),
        ]);
        return redirect()->back()->with('error', "❌ Ocurrió un error creando el SSL/Nginx: " . $e->getMessage());
    }
}
}




