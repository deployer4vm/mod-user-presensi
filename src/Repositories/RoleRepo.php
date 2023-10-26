<?php

namespace hpsynapse\moduser\Repositories;

// 1. Import level PHP

// 2. Import level Package Composer

// 3. Import level Laravel Core

// 4. Import level Synapse Core
use App\Base\BaseRepository;

// 5. Import level Synapse Module Package

// 6. Import level Synapse MainApp & Module MainApp

// 7. Import level Synapse - Current Module
// model
use hpsynapse\moduser\Models\Role;
use hpsynapse\moduser\Models\RoleGroup;
// class
use hpsynapse\moduser\Facades\UserRepo;


class RoleRepo extends BaseRepository implements \hpsynapse\moduser\Contracts\RoleRepo
{
    protected $autoResource = [
        'Group' => [
            'r' => RoleGroup::class,
            'w' => RoleGroup::class
        ],
    ];

    protected $autoResourceSearchField = [
        'Group' => ['name', 'description', 'code'],
    ];

    protected $autoResourceCreateValidate = [
        'Group' => [
            'tenant_id' => 'required',
            'name' => 'required',
            'code' => 'required'
        ],
    ];

    protected $autoResourceUpdateValidate = [
        'Group' => [
            'tenant_id' => false,
            'name' => 'required',
            'code' => 'required'
        ],
    ];
    
    public function __construct(Role $model)
    {        
        $this->model = $model;        
    }
    
    public function listRole($filter=false,int $offset=0,int $limit=0,array $orderBy=[])
    {
        if (!$filter) $filter = [];
        $filter['searchField'] = ['name'];
        $filter['hiddenColumn'] = ['created_at','updated_at'];
        $model = Role::with(['roleGroup']);//::with(['tenantGroup','tenant']);

        if (isset($filter['level']) && $filter['level']) {
            $model = $model->where('level', $filter['level']);
            unset($filter['level']);
        } 
        if (isset($filter['user_level']) && $filter['user_level']) {
            $model = $model->where('level','not like', $filter['user_level']);
            unset($filter['user_level']);
        }

        $data = $this->_list(
            $model, $filter, $offset, $limit, $orderBy
        );  
        return $data;
    }

    public function getRole($where)
    {
        return $this->_getOne(new Role,$where);
    }

    public function createRole($data)
    {
        // if(isset($data['rule']) && is_array($data['rule']))
        //     $data['rule'] = json_encode($data['rule'],JSON_PRETTY_PRINT);
        if(!isset($data['tenant_id']))
            $data['tenant_id'] = config('tenant.id',0);
        return $this->_create(new Role, $data);
    }

    public function updateRole($where, $data)
    {
        // if(isset($data['rule']) && is_array($data['rule']))
        //     $data['rule'] = json_encode($data['rule'],JSON_PRETTY_PRINT);
        if ($this->checkSystemRole($where)){
            $this->error = 'Cannot update system role in here';
            return false;
        }

        return $this->_update(new Role, $where, $data);
    }
    public function deleteRole($id)
    {
        if ($this->checkSystemRole($id)){
            $this->error = 'System role cannot be deleted in here';
            return false;
        }

        return $this->_delete(new Role, ['id', $id]);
    }

    public function checkSystemRole($id){
        $role = $this->_getOne(new Role,['id',$id]);
        if($role['system_role']){
            return true;
        }
        return false;
    }
   
    
}