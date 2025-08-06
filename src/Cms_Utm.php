<?php

namespace Sitedigitalweb\Pagina;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cms_Utm extends Model
{
    use HasFactory;
    protected $table = 'cms_utms';
    protected $fillable = [
        'campaign_name',
        'source',
        'medium',
        'term',
        'content',
        'final_url',
    ];
}

