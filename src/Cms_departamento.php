<?php

namespace Sitedigitalweb\Pagina;

use Illuminate\Database\Eloquent\Model;

class Cms_departamento extends Model{
  protected $table = 'cms_departamentos';
  public $timestamps = false;
   protected $fillable = [
        'departamento',
        'pais_id',
    ];
}