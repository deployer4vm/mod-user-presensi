<?php

namespace hpsynapse\moduser\Models;

use App\Base\BaseModel;
use App\Base\Traits\ModelDataTenant;

class UserRole extends BaseModel
{
    use ModelDataTenant;    
    protected $connection = 'perTenant'; 

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'moduser_user_roles';
    
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id','created_at'];
    
    public function role()
    {
        return $this->belongsTo('hpsynapse\moduser\Models\Role','role_id');
    }   
    
    public function datarule()
    {
        return $this->belongsTo(Datarule::class,'datarule_id');
    }   
    
    public function roleGroup()
    {
        return $this->belongsTo(RoleGroup::class,'role_group_id');
    }  
}