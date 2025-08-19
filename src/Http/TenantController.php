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
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;


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

public function certificate()
{
    return view('pagina::certificate.certificate');
}

public function store(Request $request)
    {
        $request->validate([
            'domain' => 'required|unique:tenants,domain'
        ]);

        $domain = $request->domain;

        // Guardar tenant
        $tenant = Tenant::create([
            'domain' => $domain
        ]);

        // 1. Crear certificado con Certbot
        $process = new Process([
            'certbot', 'certonly', '--nginx',
            '-d', $domain,
            '--non-interactive', '--agree-tos',
            '-m', 'soporte@tudominio.com'
        ]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // 2. Crear bloque Nginx automáticamente
        $nginxConfig = "
server {
    listen 80;
    server_name {$domain};
    return 301 https://\$host\$request_uri;
}

server {
    listen 443 ssl;
    server_name {$domain};

    ssl_certificate /etc/letsencrypt/live/{$domain}/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/{$domain}/privkey.pem;

    root /var/www/{$domain}/public;
    index index.php index.html;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }
}
";
        $filePath = "/etc/nginx/sites-available/{$domain}";
        file_put_contents($filePath, $nginxConfig);
        symlink($filePath, "/etc/nginx/sites-enabled/{$domain}");

        // 3. Recargar Nginx
        (new Process(['systemctl', 'reload', 'nginx']))->run();

        return back()->with('success', "Tenant {$domain} creado con SSL y bloque Nginx.");
    }

 
}





