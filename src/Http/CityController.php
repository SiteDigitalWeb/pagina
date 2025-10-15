<?php
namespace Sitedigitalweb\Pagina\Http;


use Illuminate\Http\Request;
use Sitedigitalweb\Pagina\CmsCity;
use Sitedigitalweb\Pagina\CmsCountry;
use App\Http\Controllers\Controller;

class CityController extends Controller
{

    protected $tenantName = null;

    public function __construct()
    {
        if (!session()->has('cart')) {
            session()->put('cart', []);
        }

        $hostname = app(\Hyn\Tenancy\Environment::class)->hostname();
        if ($hostname) {
            $fqdn = $hostname->fqdn;
            $this->tenantName = explode(".", $fqdn)[0];
        }
    }

    private function resolveModel()
    {
        return $this->tenantName
            ? \Sitedigitalweb\Pagina\Tenant\Cms_departamento::class
            : \Sitedigitalweb\Pagina\Cms_departamento::class;
    }
    private function resolveModelP()
    {
        return $this->tenantName
            ? \Sitedigitalweb\Pagina\Tenant\Cms_Pais::class
            : \Sitedigitalweb\Pagina\Cms_Pais::class;
    }
    public function index(){
    $model = $this->resolveModel();
    $departamentos = $model::all();
 
      return view('pagina::cities.index', compact('departamentos'));
    }

     // Mostrar producto individual
    public function show($id)
    {
        $model = $this->resolveModel();
        $producto = $model::findOrFail($id);
        return view('gestion::products.show', compact('producto'));
    }



    public function create()
    {
        $model = $this->resolveModelP();
        $countries = $model::all();
        return view('pagina::cities.create', compact('countries'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
        'departamento' => 'required|string|max:255',
        'pais_id'   => 'required|integer|exists:cms_paises,id', 
]);
        $model = $this->resolveModel();
        $model::create($request->all());

        return redirect('sd/city')->with('status', 'ok_create');
    }

    public function edit(CmsCity $city)
    {
        $countries = CmsCountry::all();
        return view('pagina::cities.edit', compact('city', 'countries'));
    }

    public function update(Request $request, CmsCity $city)
    {
        $request->validate([
            'name'       => 'required|string|max:150',
            'country_id' => 'required|exists:cms_countries,id',
        ]);

        $city->update($request->all());

        return redirect()->route('cities.index')->with('success', 'Ciudad actualizada correctamente.');
    }

    public function destroy(CmsCity $city)
    {
        $city->delete();
        return redirect()->route('cities.index')->with('success', 'Ciudad eliminada correctamente.');
    }

    public function getCiudades(Request $request)
{
    $pais_id = $request->get('cat_id');
    $ciudades = \Sitedigitalweb\Pagina\Cms_departamento::where('pais_id', $pais_id)->get();

    return response()->json($ciudades);
}
}
