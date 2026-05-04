<?php

namespace Sitedigitalweb\Pagina\Http;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Sitedigitalweb\Gestion\CmsEmbudo;

class CountryController extends Controller
{
    protected ?string $tenantName = null;
protected bool $isTenant = false;

public function __construct()
{
 

    // ✅ Stancl Tenancy
    if (tenancy()->initialized) {
        $this->isTenant   = true;
        $this->tenantName = tenant('id');
    }
}
    private function resolveModel()
    {
        return $this->tenantName
            ? \Sitedigitalweb\Pagina\Tenant\Cms_Pais::class
            : \Sitedigitalweb\Pagina\Cms_Pais::class;
    }

    public function index()
    {
    $model = $this->resolveModel();
    $pais = $model::all();

      return view('pagina::country.index', compact('pais'));
    }

    public function create()
    {
        return view('pagina::country.create');
    }

    public function store(Request $request)
{
    // Validaciones
    $request->validate([
        'pais' => 'required|string|max:255',
    ]);

    // Resuelve el modelo según tenant o base principal
    $model = $this->resolveModel();

    // Crea el registro
    $model::create([
        'pais' => $request->pais,
    ]);

    // Redirecciona con mensaje de éxito
    return redirect('sd/country')->with('status', 'ok_create');
}

    public function edit($id)
    {
       $model = $this->resolveModel();
       $pais = $model::findOrFail($id); // devuelve 404 si no existe
       return view('pagina::country.edit', compact('pais'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'pais' => 'required|string|max:255',
        ]);
        $model = $this->resolveModel();
        $pais = $model::findOrFail($id);

        $pais->update([
        'pais' => $request->pais,
         ]);

        return redirect('sd/country')->with('success', 'Producto creado correctamente.');
    }

    public function destroy($id)
    {
         $model = $this->resolveModel();
         $pais = $model::findOrFail($id);
         $pais->delete();

        return redirect('sd/country')->with('status', 'ok_delete');
    }
}
