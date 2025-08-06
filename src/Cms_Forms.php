<?php

namespace Sitedigitalweb\Pagina;

use Illuminate\Database\Eloquent\Model;


class Cms_Forms extends Model

{
    protected $table = 'cms_forms';
    protected $fillable = [
        'name', 'last_name', 'phone', 'address', 'message', 'city',
        'campo1', 'campo2', 'campo3', 'campo4', 'campo5',
        'campo6', 'campo7', 'campo8', 'campo9', 'campo10',
        'campo11', 'campo12', 'campo13', 'campo14', 'campo15',
        'campo16', 'campo17', 'campo18', 'campo19', 'campo20',
    ];
        
}
