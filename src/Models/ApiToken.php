<?php

namespace hpsynapse\moduser\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

use App\Base\Traits\ModelDataTenant;

class ApiToken extends Authenticatable
{
    use ModelDataTenant;
    protected $connection = 'perTenant';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'moduser_api_tokens';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'created_at'];
    
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'session_data' => 'array',
        'device_config' => 'array',
    ];
}
