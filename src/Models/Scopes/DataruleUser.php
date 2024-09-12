<?php
 
 namespace hpsynapse\moduser\Models\Scopes;
 
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

use hpsynapse\moduser\Facades\UserAuth;

/**
 * filter berdasarkan user id - scope untuk datarule type = 1 dan (type = 2, subtype = 1)
 * 
 * di-assign ke model bersangkutan
 */
class DataruleUser implements Scope
{
    protected $userId=0;
    protected $userIdField='user_id';

    public function __construct($userId=0,$userIdField=false)
    {
        $this->userId=$userId?$userId:UserAuth::user('id');
        $this->userIdField=$userIdField?$userIdField:'user_id';
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if(is_array($this->userId)){
            $builder->whereIn($this->userIdField, $this->userId);
        }else{
            $builder->where($this->userIdField, $this->userId);
        }
    }
}