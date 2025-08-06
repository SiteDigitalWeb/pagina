<?php

namespace Sitedigitalweb\Pagina;

use Illuminate\Database\Eloquent\Model;

class Cms_Stadistics extends Model
{
	protected $table = 'cms_statistics';
    protected $fillable = [
        'ip',
        'host',
        'navegador',
        'referido',
        'ciudad',
        'pais',
        'pagina',
        'mes',
        'ano',
        'hora',
        'dia',
        'idioma',
        'cp',
        'longitud',
        'latitud',
        'fecha',
        'utm_medium',
        'utm_source',
        'utm_campana',
        'remember_token',
    ];
}