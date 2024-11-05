<?php

namespace hpsynapse\moduser\Models;

use App\Base\BaseModel;
use App\Facades\DbConfig;
use App\Base\Traits\ModelDataTenant;

class Role extends BaseModel
{
    use ModelDataTenant;
    protected $connection = 'perTenant';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'moduser_roles';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'created_at'];

    protected $casts = [
        'rule' => 'array'
    ];

    public function tenantGroup()
    {
        return $this->belongsTo('App\Models\TenantGroup', 'tenant_group_id');
    }

    public function tenant()
    {
        return $this->belongsTo('App\Models\Tenant', 'tenant_id');
    }

    public function roleGroup()
    {
        return $this->belongsTo(RoleGroup::class, 'role_group_id');
    }
    
    // default data rule
    public function datarule()
    {
        return $this->belongsTo(Datarule::class, 'datarule_id');
    }

    
    /**
     * dashboard : 
     * 
     * @return Array dashboard aktif
     */
    public function getDashboardAttribute()
    {
        // jika menggunakan config role group
        if($this->dashboard_type==0){
            $dashboardId = $this->role_group_id?$this->roleGroup()->first()->dashboard_type:1;
        }else{
            $dashboardId = $this->dashboard_type;
        }
        $dashboardId=$dashboardId?$dashboardId:1;
        return DbConfig::getGlobalConfig('dashboard.config.item','item.'.$dashboardId,[
            'id'=>$dashboardId,
            'name'=>'Default Dashboard',
            'description'=>'',
            'tenant'=>[],
            'template_code'=>'moduser_defaultBlank',
            'feature'=>[],
            'content'=>[]
        ], $dashboardId==1?true:false, true);
    }

    
    /**
     * global : array, apakah user ini teringrasi dengan
     * data eksternal lain, jika ya maka ini bisa digunakan sebagai penanda apakah
     * user bisa dimanage via fitur manage user atau tidak.
     * 
     * @return Array 
     */
    public function getGlobalAttribute()
    {            
        $return = [
            [
                'tenant_id'=>0,
                'global_bypass_rule'=>$this->global_bypass_rule,
                'global_bypass_datarule'=>$this->global_bypass_datarule,
                'global_bypass_dashboard'=>$this->global_bypass_dashboard,
                'global_bypass_notification'=>$this->global_bypass_notification,
            ]
        ];

        if($this->is_global){
            $tmpConfig = DbConfig::listGlobalConfig('role.config.global.item.'.$this->role_code,true,true);
            if($tmpConfig)
                foreach ($tmpConfig as $value) {
                    $return[] = $value;
                }
        }

        return $return;
    }
}
