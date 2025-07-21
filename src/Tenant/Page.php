<?php

namespace DigitalsiteSaaS\Pagina\Tenant;

use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;


class Page extends Model

{

	use UsesTenantConnection;

	protected $table = 'cms_pages';



	protected $fillable = [
        'page', // Agrega esto junto con los demás campos asignables
        'title',
        'content',
        'slug',
        'page_id',
        'position', // si tienes páginas padre
        // otros campos que permitas asignar masivamente
    ];

    public $timestamps = true;

		public function products(){
	return $this->hasMany('DigitalsiteSaaS\Carrito\Product')->orderby('position', 'asc');

	}

		public function blogs(){
	return $this->hasMany('DigitalsiteSaaS\Pagina\Bloguero')->orderby('created_at', 'desc');

	}

		public function fichas(){
	return $this->hasMany('DigitalsiteSaaS\Pagina\Fichaje');

	}


	 public function subpaginas(){

     	return $this->hasMany('DigitalsiteSaaS\Pagina\Page')->orderBy('posta', 'desc');
     }

 public function banners(){

     	return $this->hasMany('DigitalsiteSaaS\Pagina\Baner');
     }

}



