<?php
namespace Sitedigitalweb\Pagina;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cms_theme extends Model
{
    use HasFactory;

    protected $table = 'cms_themes';

   protected $fillable = [
        'template_id',
        'theme',
        
        // Colores
        'color_1', 'color_2', 'color_3', 'color_4',
        'var_color_1', 'var_color_2', 'var_color_3', 'var_color_4',

        // Tipografía H1-H5
        'font_h1', 'font_h2', 'font_h3', 'font_h4', 'font_h5',
        'size_h1', 'size_h2', 'size_h3', 'size_h4', 'size_h5',
        'var_font_h1', 'var_font_h2', 'var_font_h3', 'var_font_h4', 'var_font_h5',
        'var_size_h1', 'var_size_h2', 'var_size_h3', 'var_size_h4', 'var_size_h5',
    ];
   }
