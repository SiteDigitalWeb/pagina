<?php

namespace Sitedigitalweb\Pagina\Http;
use App\Http\Controllers\Controller;

 use Sitedigitalweb\Pagina\Cms_Utm;
use Illuminate\Http\Request;

class UtmController extends Controller
{
    public function index()
    {
        $utms = Cms_Utm::latest()->get();
        return view('pagina::utm.index', compact('utms'));
    }

    public function create()
    {
        return view('pagina::utm.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'campaign_name' => 'required',
            'source' => 'required',
            'medium' => 'required',
            'term' => 'nullable',
            'content' => 'nullable',
            'final_url' => 'required|url',
        ]);

        $base = $request->final_url;
        $params = http_build_query([
            'utm_source' => $request->source,
            'utm_medium' => $request->medium,
            'utm_campaign' => $request->campaign_name,
            'utm_term' => $request->term,
            'utm_content' => $request->content,
        ]);

        $utm_full_url = $base . (str_contains($base, '?') ? '&' : '?') . $params;

        $utm = Cms_Utm::create([
            'campaign_name' => $request->campaign_name,
            'source' => $request->source,
            'medium' => $request->medium,
            'term' => $request->term,
            'content' => $request->content,
            'final_url' => $utm_full_url,
        ]);

        return redirect()->route('utm.index')->with('success', 'UTM creada correctamente.');
    }

    public function show(Cms_Utm $utm)
    {
        return view('utm.show', compact('utm'));
    }

    public function destroy(Cms_Utm $utm)
    {
        $utm->delete();
        return redirect()->route('utm.index')->with('success', 'UTM eliminada.');
    }
}