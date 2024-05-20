<?php

namespace hpsynapse\moduser\Models;

use App\Base\BaseModel;
use App\Base\Traits\ModelDataTenant;

class AuthRequest extends BaseModel
{
    use ModelDataTenant;    
    protected $connection = 'perTenant'; 
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'moduser_auth_requests';
    
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id','created_at'];

    protected $casts = [
        'data' => 'array'
    ];
    
    public function feature()
    {
        return $this->hasOne('hpsynapse\moduser\Models\AuthFeature', 'id', 'feature_id');
    }
}