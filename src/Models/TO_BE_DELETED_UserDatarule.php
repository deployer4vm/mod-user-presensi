<?php

namespace hpsynapse\moduser\Models;

use App\Base\BaseModel;
use App\Base\Traits\ModelDataTenant;

/**
 * TIDAK JADI DIGUNAKAN
 */
class TO_BE_DELETED_UserDatarule extends BaseModel
{
    use ModelDataTenant;    
    protected $connection = 'perTenant'; 

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'moduser_user_datarules';
    
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id','created_at'];
    
    public function datarule()
    {
        return $this->belongsTo(Datarule::class,'datarule_id');
    }   
}