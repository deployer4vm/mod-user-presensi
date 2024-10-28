<?php
 
 namespace hpsynapse\moduser\Models\Scopes;
 
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

use hpsynapse\moduser\Facades\UserAuth;

/**
 * BELUM DIGUNAKAN
 * Berdasarkan role level group id - scope untuk datarule type = 2, subtype = 5
 */
class DataruleRoleLevelGroup implements Scope
{
    protected $userGroupId=0;
    protected $userGroupIdField='user_group_id';
    
    public function __construct($userGroupId,$userGroupIdField='user_group_id')
    {
        $this->userGroupId=$userGroupId;
        $this->userGroupIdField=$userGroupIdField;
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereHas('user',function($m){
            if(is_array($this->userGroupId)){
                $m->whereIn($this->userGroupIdField, $this->userGroupId);
            }else{
                $m->where($this->userGroupIdField, $this->userGroupId);
            }
        });
    }
}