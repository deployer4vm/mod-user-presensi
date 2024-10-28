<?php

namespace hpsynapse\moduser\Models;

use App\Base\BaseModel;
use App\Base\Traits\ModelDataTenant;

class TO_BE_DELETED_UserRoleGroup extends BaseModel
{
    use ModelDataTenant;    
    protected $connection = 'perTenant'; 

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'moduser_user_role_groups';
    
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
    
    public function roleGroup()
    {
        return $this->belongsTo(RoleGroup::class,'role_group_id');
    }   
}