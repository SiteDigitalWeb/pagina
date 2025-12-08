<?php

namespace Sitedigitalweb\Pagina;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cms_user extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cms_users';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'last_name',
        'empresa',
        'address',
        'slug',
        'nit',
        'email',
        'phone',
        'interes',
        'mes',
        'sector_id',
        'cantidad_id',
        'referido_id',
        'country_id',
        'city_id',
        'tipo',
        'funel_id',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'message',
        'fecha',
        'campo1',
        'campo2',
        'campo3',
        'campo4',
        'campo5',
        'campo6',
        'campo7',
        'campo8',
        'campo9',
        'campo10',
        'campo11',
        'campo12',
        'campo13',
        'campo14',
        'campo15',
        'campo16',
        'campo17',
        'campo18',
        'campo19',
        'campo20',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;
}
