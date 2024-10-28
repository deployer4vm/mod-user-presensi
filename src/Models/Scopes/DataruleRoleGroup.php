<?php
 
 namespace hpsynapse\moduser\Models\Scopes;
 
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

use hpsynapse\moduser\Facades\UserAuth;

/**
 * Berdasarkan role group code - scope untuk datarule type = 2, subtype = 4
 * 
 * data yang dimaksud harus berelasi ke user dengan nama relasi 'user'
 */
class DataruleRoleGroup implements Scope
{
    protected $roleGroupCode='';
    protected $userRelationName='user';
    
    public function __construct($roleGroupCode,$userRelationName=false)
    {
        $this->roleGroupCode=$roleGroupCode;
        $this->userRelationName=$userRelationName?$userRelationName:'user';
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereHas($this->userRelationName.'.roles.role',function($m){
            if(is_array($this->roleGroupCode)){
                $m->whereIn('role_group_code', $this->roleGroupCode);
            }else{
                $m->where('role_group_code', $this->roleGroupCode);
            }
        });
    }
}