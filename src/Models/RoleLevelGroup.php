<?php

namespace hpsynapse\moduser\Models;

use App\Base\BaseModel;
use App\Base\Traits\ModelDataTenant;

class RoleLevelGroup extends BaseModel
{
    use ModelDataTenant;    
    protected $connection = 'perTenant'; 

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'moduser_role_level_groups';
    
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id','created_at'];
    
    protected $casts = [
        // 'rule' => 'array'
    ];
    
    public function tenant()
    {
        return $this->belongsTo('App\Models\Tenant','tenant_id');
    }   
}