<?php

namespace Sitedigitalweb\Pagina;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cms_SavedComponent extends Model
{

    protected $table = 'cms_components'; // nombre de la tabla en la BD
    protected $fillable = ['label', 'content', 'category'];
}
