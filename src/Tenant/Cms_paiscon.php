<?php

namespace Sitedigitalweb\Pagina\Tenant;
use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class Cms_paiscon extends Model{
	  use UsesTenantConnection;
	protected $table = 'cms_paises';
	public $timestamps = false;
}


