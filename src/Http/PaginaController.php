<?php

 namespace Sitedigitalweb\Pagina\Http;
 use Sitedigitalweb\Pagina\Page;
 use Sitedigitalweb\Pagina\Inputweb;
 use Sitedigitalweb\Pagina\Content;
 use DB;
 use Auth;
 use Zipper;
 use File;
 use Storage;
 use Sitedigitalweb\Pagina\User;
 use Sitedigitalweb\Pagina\Zippera;
 use Sitedigitalweb\Pagina\Messagema;
 use Sitedigitalweb\Pagina\Color;
 use Sitedigitalweb\Pagina\Pais;
 use App\Http\Controllers\Controller;
 use Input;
 use Sitedigitalweb\Pagina\Diagrama;
 use Illuminate\Support\Str;
 use Illuminate\Filesystem\Filesystem;
 use Illuminate\Http\Request;
 use Hyn\Tenancy\Models\Hostname;
 use Hyn\Tenancy\Models\Website;
 use Hyn\Tenancy\Repositories\HostnameRepository;
 use Hyn\Tenancy\Repositories\WebsiteRepository;
 use Carbon\Carbon;
 use Hash;
 use GuzzleHttp\Client;
 use App\Http\Requests\StorePageRequest;

 class PaginaController extends Controller{

protected $tenantName = null;

 public function __construct(){
  $this->middleware('auth');

  $hostname = app(\Hyn\Tenancy\Environment::class)->hostname();
        if ($hostname){
            $fqdn = $hostname->fqdn;
            $this->tenantName = explode(".", $fqdn)[0];
        }

 }

public function index()
{
    $pageModel = $this->tenantName 
        ? \Sitedigitalweb\Pagina\Tenant\Page::class 
        : Page::class;

    $paginas = $pageModel::all();
    $menu = $pageModel::whereNull('page_id')->get();
    $casta = $pageModel::count();

    return view('pagina::pages.index', compact('paginas', 'casta', 'menu'));
}


public function show()
{
    $number = Auth::id(); // Más eficiente que Auth::user()->id
    
    $user = $this->tenantName
        ? \Sitedigitalweb\Pagina\Tenant\Page::where('position', 1)->count()
        : Page::where('position', 1)->count();
    
    return view('pagina::pages.create', compact('user', 'number'));
}


public function create(StorePageRequest $request)
{
    try {
        $validated = $request->validated();
        $idioma = $request->input('language'); // Cambiado de 'idioma' a 'language' para coincidir con las reglas

        $pageData = [
            'page' => $request->input('page'),
            'slugcon' => $request->input('slug'),
            'slug' => $idioma === 'ne' ? $request->input('slug') : $idioma.'/'.$request->input('slug'),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'keywords' => $request->input('keywords'),
            'position' => $request->input('position'),
            'menu_type' => $request->input('menu_type'),
            'visibility' => $request->input('visibility'),
            'visibility_ecommerce' => $request->input('visibility_ecommerce'),
            'visibility_blog' => $request->input('visibility_blog'),
            'language' => $idioma,
            'pixel' => $request->input('pixel'),
            'follow' => $request->input('follow'),
            'page_id' => $request->input('page_id'),
        ];

        if (!$this->tenantName) {
            $pagina = Page::create($pageData);
        } else {
            $pagina = \Sitedigitalweb\Pagina\Tenant\Page::create($pageData);
        }

        return redirect('sd/pages')->with([
            'status' => 'ok_create',
            'message' => 'Página creada exitosamente'
        ]);

    } catch (\Exception $e) {
        // Log del error para debugging
        \Log::error('Error al crear página: ' . $e->getMessage());
        
        return redirect()->back()
            ->withInput()
            ->withErrors([
                'error' => 'Ocurrió un error al guardar la página. Por favor intente nuevamente.'
            ]);
    }
}


public function update(StorePageRequest $request, $id)
{
    try {
        if (!$this->tenantName) {
            $page = Page::findOrFail($id);
        } else {
            $page = \Sitedigitalweb\Pagina\Tenant\Page::findOrFail($id);
        }

        $idioma = strtolower($request->input('language'));
        $slugInput = $request->input('slug'); // no hacemos trim('/')

        // Normalización del slug según idioma
        $finalSlug = match ($idioma) {
            'ne' => $slugInput === '/' ? '/' : trim($slugInput, '/'),
            'es', 'en', 'fr' => $slugInput === '/' 
                ? '/' 
                : $idioma . '/' . ltrim($slugInput, '/'),
            default => $slugInput === '/' ? '/' : trim($slugInput, '/'),
        };

        $pageData = [
            'page' => $request->input('page'),
            'slugcon' => $slugInput,
            'slug' => $finalSlug,
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'keywords' => $request->input('keywords'),
            'position' => $request->input('position'),
            'menu_type' => $request->input('menu_type'),
            'visibility' => $request->input('visibility'),
            'visibility_ecommerce' => $request->input('visibility_ecommerce'),
            'visibility_blog' => $request->input('visibility_blog'),
            'language' => $idioma,
            'pixel' => $request->input('pixel'),
            'follow' => $request->input('follow'),
            'page_id' => $request->input('page_id'),
        ];

        $page->update($pageData);

        return redirect('sd/pages')
            ->with([
                'status' => 'ok_update',
                'message' => 'Página actualizada exitosamente'
            ]);

    } catch (\Exception $e) {
        \Log::error('Error al actualizar página: ' . $e->getMessage());
        
        return redirect()->back()
            ->withInput()
            ->withErrors([
                'error' => 'Ocurrió un error al actualizar la página. Por favor intente nuevamente.'
            ]);
    }
}



public function edit($id)
{
    if (!$this->tenantName) {
        $page = Page::findOrFail($id);
    } else {
        $page = \Sitedigitalweb\Pagina\Tenant\Page::findOrFail($id);
    }

    return view('pagina::pages.edit', compact('page'));
}


public function destroy($id)
{
    try {
        if (!$this->tenantName) {
            $page = Page::findOrFail($id);
        } else {
            $page = \Sitedigitalweb\Pagina\Tenant\Page::findOrFail($id);
        }

        // Validar que no sea una página del sistema protegida
        if ($page->is_protected) {
            return redirect()->back()
                ->with([
                    'status' => 'error',
                    'message' => 'No se puede eliminar una página protegida del sistema'
                ]);
        }

        // Validar que no tenga páginas hijas
        if ($page->children()->exists()) {
            return redirect()->back()
                ->with([
                    'status' => 'error',
                    'message' => 'No se puede eliminar la página porque tiene subpáginas asociadas'
                ]);
        }

        $page->delete();

        return redirect('sd/pages')
            ->with([
                'status' => 'ok_delete',
                'message' => 'Página eliminada correctamente'
            ]);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        \Log::error('Página no encontrada al intentar eliminar: ' . $e->getMessage());
        return redirect()->back()
            ->with([
                'status' => 'error',
                'message' => 'La página que intentas eliminar no existe'
            ]);
            
    } catch (\Exception $e) {
        \Log::error('Error al eliminar página: ' . $e->getMessage());
        return redirect()->back()
            ->with([
                'status' => 'error',
                'message' => 'Ocurrió un error al eliminar la página'
            ]);
    }
}


















 public function sitesaas(){


$tarjetas = DB::table('tarjetas')->where('email', '=', Auth::user()->email)->get();
$tarjetascont = DB::table('tarjetas')->where('email', '=', Auth::user()->email)->count();
$suscripcioncont = DB::table('suscripcion')->where('user_id', '=', Auth::user()->id)->count();

if(!Auth::user()->saas_id){
  $suscripcion = DB::table('suscripcion')->where('user_id','=', Auth::user()->id)->orderby('id','DESC')->take(1)->get();

  $planes = DB::table('planes')->get();
 return View('pagina::saas.dashboard')->with('planes', $planes)->with('suscripcion', $suscripcion)->with('tarjetas', $tarjetas)->with('tarjetascont', $tarjetascont)->with('suscripcioncont', $suscripcioncont);
}else{

  $number = Auth::user()->id;
$idsuscripcion = DB::table('trans_payco')->where('email', '=', Auth::user()->email)->pluck('extra1')->first();

  $facturas = DB::table('trans_payco')->where('email','=', Auth::user()->email)->get();

  $infosaas = DB::table('tenancy.hostnames')
  ->join('tenancy.websites','websites.id','=','hostnames.website_id')
  ->where('hostnames.id', Auth::user()->saas_id)
  ->get();

    foreach ($infosaas as $infosaasweb) {
     $mihost =  ($infosaasweb->uuid.'.');
   $website = DB::table($mihost.'users')->get();

 $dias = date('Y-m-d');
 if($dias <=  $infosaasweb->presentacion){
  $resp = 'true';
 }else{
  $resp = 'false';
 }

  }

  return View('pagina::saas.dashboard')->with('number', $number)->with('infosaas', $infosaas)->with('website', $website)->with('resp', $resp)->with('facturas', $facturas)->with('idsuscripcion', $idsuscripcion)->with('tarjetas', $tarjetas);
  }
} 

 public function editarsaas(){
  $paises = Pais::all();
  $usuario = User::leftJoin('paises', 'paises.id', '=', 'users.pais_id')->where('users.id','=', Auth::user()->id)->get();
  return View('pagina::saas.editar-usuario')->with('usuario', $usuario)->with('paises', $paises);
 }

  public function editarcontrasena(){
  $paises = Pais::all();
  $usuario = User::leftJoin('paises', 'paises.id', '=', 'users.pais_id')->where('users.id','=', Auth::user()->id)->get();
  return View('pagina::saas.editar-contrasena')->with('usuario', $usuario)->with('paises', $paises);
 }




}