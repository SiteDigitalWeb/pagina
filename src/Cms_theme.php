<?php

namespace Sitedigitalweb\Pagina;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Cms_theme extends Model
{
    use BelongsToTenant;

    protected $table = 'cms_themes';

    protected $fillable = [
        'tenant_id',
        'template_id',
        'theme',
        'color_1', 'color_2', 'color_3', 'color_4',
        'var_color_1', 'var_color_2', 'var_color_3', 'var_color_4',
        'font_h1', 'size_h1', 'var_font_h1', 'var_size_h1',
        'font_h2', 'size_h2', 'var_font_h2', 'var_size_h2',
        'font_h3', 'size_h3', 'var_font_h3', 'var_size_h3',
        'font_h4', 'size_h4', 'var_font_h4', 'var_size_h4',
        'font_h5', 'size_h5', 'var_font_h5', 'var_size_h5',
    ];
}