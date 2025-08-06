<?php

namespace Sitedigitalweb\Pagina\Tenant;

use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
  
 use UsesTenantConnection;
 protected $table = 'cms_pages';

 protected $fillable = [
        'page', // Agrega esto junto con los demás campos asignables
        'slug',
        'title',
        'description',
        'keywords',
        'position',
        'menu_type',
        'visibility',
        'visibility_ecommerce',
        'visibility_blog',
        'content',
        'styles',
        'scripts',
        'language', 
        'pixel', 
        'follow', 
        'name',
        'page_id', // si tienes páginas padre
        // otros campos que permitas asignar masivamente
    ];

   protected $casts = [
        'content' => 'array',
        'styles' => 'array',
        'scripts' => 'array',
        'assets' => 'array', // scripts como texto plano (puede contener código JS)
    ];

  public $timestamps = true;

  public function subpaginas(){
   return $this->hasMany('Sitedigitalweb\Pagina\Page')->orderBy('position', 'desc');
  }

  // En tu modelo Page.php
public function children()
{
    return $this->hasMany(Page::class, 'page_id');
}

public function getIsProtectedAttribute()
{
    // Lista de páginas protegidas del sistema
    $protectedPages = ['home', 'contact', 'about'];
    
    return in_array($this->slug, $protectedPages);
}

// Asegurar que siempre se guarde como JSON válido
    public function setContentAttribute($value)
    {
        $this->attributes['content'] = is_array($value) ? json_encode($value) : $value;
    }
    
    public function setStylesAttribute($value)
    {
        $this->attributes['styles'] = is_array($value) ? json_encode($value) : $value;
    }

    // Opcional: Asegurar que scripts se almacenen como texto (puede venir como string ya)
    public function setScriptsAttribute($value)
    {
        $this->attributes['scripts'] = is_string($value) ? $value : json_encode($value);
    }

    // Opcional: Asegurar que scripts se almacenen como texto (puede venir como string ya)
    public function setAssetsAttribute($value)
    {
        $this->attributes['assets'] = is_string($value) ? $value : json_encode($value);
    }

}

