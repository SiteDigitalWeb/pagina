<?php

namespace Sitedigitalweb\Pagina\Http;

use App\Http\Controllers\Controller;
use Sitedigitalweb\Pagina\Cms_Recaptcha;
use Illuminate\Http\Request;

class RecaptchaSettingController extends Controller
{
    public function index()
    {
        $settings = Cms_Recaptcha::find(1);
        return view('pagina::recaptcha.index', compact('settings'));
    }

    public function create()
    {
        return view('pagina::recaptcha.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'site_key' => 'required|string',
            'secret_key' => 'required|string',
        ]);

        Cms_Recaptcha::create($request->all());
        return redirect()->route('recaptcha.index')->with('success', 'Configuración creada correctamente.');
    }

    public function edit(Cms_Recaptcha $recaptcha)
    {
        return view('pagina::recaptcha.edit', compact('recaptcha'));
    }



    public function update(Request $request, $id)
{
    $setting = Cms_Recaptcha::findOrFail($id);

    $setting->site_key = $request->input('publickey');
    $setting->secret_key = $request->input('privatekey');
    $setting->save();

    return redirect()->route('recaptcha.index')->with('status', 'ok_update');
}

    public function destroy(Cms_Recaptcha $recaptcha)
    {
        $recaptcha->delete();
        return redirect()->route('recaptcha.index')->with('success', 'Configuración eliminada.');
    }
}