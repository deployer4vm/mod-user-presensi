<?php

namespace hpsynapse\moduser\Models;

use App\Base\BaseModel;
// use App\Base\Traits\ModelDataTenant;

class AuthFeature extends BaseModel
{
    // use ModelDataTenant;    
    // protected $connection = 'perTenant'; 
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'moduser_auth_features';
    
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id','created_at'];

    protected $casts = [
        'callback_system_user_id' => 'array'
    ];
}