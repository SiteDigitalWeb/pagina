<?php

namespace Sitedigitalweb\Pagina\Tenant;


use Illuminate\Database\Eloquent\Model;

class Cms_Pais extends Model{

 protected $table = 'cms_paises';
 public $timestamps = false;
  protected $fillable = [
        'pais',
    ];
}
