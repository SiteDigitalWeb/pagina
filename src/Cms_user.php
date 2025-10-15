<?php

namespace Sitedigitalweb\Pagina;

use Illuminate\Database\Eloquent\Model;

class Cms_user extends Model
{
    protected $table = 'cms_users';

    protected $fillable = [
        // Datos básicos
        'name', 'last_name', 'empresa', 'address', 'slug', 'nit',
        'email', 'phone', 'interes', 'mes',

        // Relaciones / FK
        'sector_id', 'cantidad_id', 'referido_id', 
        'country_id', 'city_id', 'tipo', 'funel_id',

        // Tracking
        'utm_source', 'utm_medium', 'utm_campaign',

        // Otros campos
        'message', 'fecha',

        // Campos dinámicos
        'campo1', 'campo2', 'campo3', 'campo4', 'campo5',
        'campo6', 'campo7', 'campo8', 'campo9', 'campo10',
        'campo11', 'campo12', 'campo13', 'campo14', 'campo15',
        'campo16', 'campo17', 'campo18', 'campo19', 'campo20',
    ];
}
