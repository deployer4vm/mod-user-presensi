<?php
 
 namespace hpsynapse\moduser\Models\Scopes;
 
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

use hpsynapse\moduser\Facades\UserAuth;

/**
 * Berdasarkan user group code - scope untuk datarule type = 2, subtype = 2 (user group code)
 * 
 * data yang dimaksud harus berelasi ke user dengan nama relasi 'user'
 */
class DataruleUserGroup implements Scope
{
    protected $userGroupCode='';
    protected $userRelationName='user';
    
    public function __construct($userGroupCode,$userRelationName=false)
    {
        $this->userGroupCode=$userGroupCode;
        $this->userRelationName=$userRelationName?$userRelationName:'user';
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereHas($this->userRelationName,function($m){
            if(is_array($this->userGroupCode)){
                $m->whereIn('user_group_code', $this->userGroupCode);
            }else{
                $m->where('user_group_code', $this->userGroupCode);
            }
        });
    }
}