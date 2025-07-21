<?php

namespace DigitalsiteSaaS\Pagina\Tenant;

use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class SavedComponent extends Model
{
    use UsesTenantConnection;

    protected $table = 'saved_components'; // nombre de la tabla en la BD
    protected $fillable = ['label', 'content', 'category'];
}
