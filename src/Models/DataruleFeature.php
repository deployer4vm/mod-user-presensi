<?php

namespace hpsynapse\moduser\Models;

use App\Base\BaseModel;

class DataruleFeature extends BaseModel
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'moduser_datarule_features';
    
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id','created_at'];
    
    protected $casts = [
        'config' => 'array'
    ];
}