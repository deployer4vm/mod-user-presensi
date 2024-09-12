<?php
 
 namespace hpsynapse\moduser\Models\Scopes;
 
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

use hpsynapse\moduser\Facades\UserAuth;

/**
 * Berdasarkan role group id - scope untuk datarule type = 2, subtype = 4
 * 
 * data yang dimaksud harus berelasi ke user dengan nama relasi 'user'
 */
class DataruleRoleGroup implements Scope
{
    protected $roleGroupId=0;
    protected $userRelationName='user';
    
    public function __construct($roleGroupId,$userRelationName=false)
    {
        $this->roleGroupId=$roleGroupId;
        $this->userRelationName=$userRelationName?$userRelationName:'user';
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereHas($this->userRelationName.'.roles',function($m){
            if(is_array($this->roleGroupId)){
                $m->whereIn('role_group_id', $this->roleGroupId);
            }else{
                $m->where('role_group_id', $this->roleGroupId);
            }
        });
    }
}