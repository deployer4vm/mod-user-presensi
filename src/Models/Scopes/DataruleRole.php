<?php
 
 namespace hpsynapse\moduser\Models\Scopes;
 
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

use hpsynapse\moduser\Facades\UserAuth;

/**
 * Berdasarkan role id - scope untuk datarule type = 2, subtype = 3
 * 
 * data yang dimaksud harus berelasi ke user dengan nama relasi 'user'
 */
class DataruleRole implements Scope
{
    protected $roleId=0;
    protected $userRelationName='user';
    
    public function __construct($roleId,$userRelationName=false)
    {
        $this->roleId=$roleId;
        $this->userRelationName=$userRelationName?$userRelationName:'user';
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereHas($this->userRelationName.'.roles',function($m){
            if(is_array($this->roleId)){
                $m->whereIn('role_id', $this->roleId);
            }else{
                $m->where('role_id', $this->roleId);
            }
        });
    }
}