<?php

namespace Sitedigitalweb\Pagina;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Domain extends Model
{
    use BelongsToTenant;

    protected $table = 'domains';
    
    protected $fillable = [
        'domain',
        'tenant_id',
        'is_primary',
        'is_custom',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_custom' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }
}
