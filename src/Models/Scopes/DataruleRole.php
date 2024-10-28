<?php
 
 namespace hpsynapse\moduser\Models\Scopes;
 
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

use hpsynapse\moduser\Facades\UserAuth;

/**
 * Berdasarkan role code - scope untuk datarule type = 2, subtype = 3
 * 
 * data yang dimaksud harus berelasi ke user dengan nama relasi 'user'
 */
class DataruleRole implements Scope
{
    protected $roleCode='';
    protected $userRelationName='user';
    
    public function __construct($roleCode,$userRelationName=false)
    {
        $this->roleCode=$roleCode;
        $this->userRelationName=$userRelationName?$userRelationName:'user';
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereHas($this->userRelationName.'.roles.role',function($m){
            if(is_array($this->roleCode)){
                $m->whereIn('role_group_code', $this->roleCode);
            }else{
                $m->where('role_group_code', $this->roleCode);
            }
        });
    }
}