<?php

namespace hpsynapse\moduser\Repositories;

// 1. Import level PHP

// 2. Import level Package Composer

// 3. Import level Laravel Core

// 4. Import level Synapse Core
use App\Base\BaseRepository;
use App\Facades\Tenant;
use Exception;
use hpsynapse\moduser\Facades\UserAuth;
// 5. Import level Synapse Module Package

// 6. Import level Synapse MainApp & Module MainApp

// 7. Import level Synapse - Current Module
// model
use hpsynapse\moduser\Models\Role;
use hpsynapse\moduser\Models\RoleGroup;
// class
use hpsynapse\moduser\Facades\UserRepo;
use hpsynapse\moduser\Models\RoleLevelGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoleRepo extends BaseRepository implements \hpsynapse\moduser\Contracts\RoleRepo
{
    protected $autoResource = [
        'Group' => [
            'r' => RoleGroup::class,
            'w' => RoleGroup::class
        ],
        'LevelGroup' => [
            'r' => RoleLevelGroup::class,
            'w' => RoleLevelGroup::class
        ],
    ];

    protected $autoResourceSearchField = [
        'Group' => ['name', 'description', 'code'],
        'LevelGroup' => ['code', 'name', 'description'],
    ];

    protected $autoResourceCreateValidate = [
        'Group' => [
            'tenant_id' => 'required',
            'name' => 'required',
            'code' => 'required'
        ],
        'LevelGroup' => [
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
        'LevelGroup' => [
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
   
    /**
     * ROLE GROUP
     * -------------------------------------------------------------------------
     */

    /**
     * create role group
     * 
     * @param array $input
     * 
     * @return bool
     */
    public function createGroup(array $input = []) : bool
    {
        // cek kode
        $checkCode = $this->groupExists(['code', $input['code']]);
        if ($checkCode) {
            $this->error = __('validation.unique', [
                'attribute' => __('role.group.data_group.field_name.code')
            ]);
            return false;
        }

        // wrap fungsi utama dalam db transaction agar bisa rollback
        $dontHaveTransactionLevel = !Tenant::dbTransactionLevel();
        if ($dontHaveTransactionLevel)
            Tenant::dbBeginTransaction();

        try {

            $create = $this->_autoResourceCreate('createGroup', [$input]);
            if ($create == false) {
                throw new Exception($this->errorFull());
                return false;
            }

            if ($dontHaveTransactionLevel)
                Tenant::dbCommit();

            return true;
            
        } catch (Exception $e) {

            if ($dontHaveTransactionLevel)
                Tenant::dbRollback();

            if (!$this->error) $this->error = $e->getMessage();

            Log::info('moduser/src/Repositories RoleRepo::createGroup() ERROR');
            Log::error($e);

            // jika sedang dalam transaksi dari parent maka teruskan error nya ke parent transaction nya
            if (!$dontHaveTransactionLevel)
                throw $e;

            return false;
        }
    }

    /**
     * update role group
     * 
     * @param int|array $where
     * @param array $input
     * 
     * @return bool
     */
    public function updateGroup($where, array $input = []) : bool 
    {
        $oldGroup = $this->getGroup($where);
        if (!$oldGroup) {
            $this->error =  __('lang.data_attribute_not_found', [
                'attribute' => __('role.group.data_group.name')
            ]);
            return false;
        }

        if (!UserAuth::user()['level'] == 0 && $oldGroup['locked_data_mode'] == 2) {
            $this->error = 'Data tidak bisa diedit';
            return false;
        }

        // cek kode
        $checkCode = $this->groupExists(['code', $input['code']]);
        if ($input['code'] != $oldGroup['code'] && $checkCode) {
            $this->error = __('validation.unique', [
                'attribute' => __('role.group.data_group.field_name.code')
            ]);
            return false;
        }

        // wrap fungsi utama dalam db transaction agar bisa rollback
        $dontHaveTransactionLevel = !Tenant::dbTransactionLevel();
        if ($dontHaveTransactionLevel)
            Tenant::dbBeginTransaction();

        try {

            $update = $this->_autoResourceUpdate('updateGroup', [$where, $input]);
            if ($update == false) {
                throw new Exception($this->errorFull());
                return false;
            }

            if ($dontHaveTransactionLevel)
                Tenant::dbCommit();

            return true;
            
        } catch (Exception $e) {

            if ($dontHaveTransactionLevel)
                Tenant::dbRollback();

            if (!$this->error) $this->error = $e->getMessage();

            Log::info('moduser/src/Repositories RoleGroup::updateGroup() ERROR');
            Log::error($e);

            // jika sedang dalam transaksi dari parent maka teruskan error nya ke parent transaction nya
            if (!$dontHaveTransactionLevel)
                throw $e;

            return false;
        }
    }

    /**
     * delete role group
     * 
     * @param int|array $where
     * @param array $input
     * 
     * @return bool
     */
    public function deleteGroup($where) : bool 
    {
        $oldData = $this->getGroup($where);
        if (!$oldData) {
            $this->error = __('lang.data_attribute_not_found', [
                'attribute' => __('role.group.data_group.name')
            ]);
            return false;
        }

        if (!UserAuth::user()['level'] == 0 && $oldData['locked_data_mode'] != 0) {
            $this->error = 'Data tidak bisa didelete';
            return false;
        }

        // wrap fungsi utama dalam db transaction agar bisa rollback
        $dontHaveTransactionLevel = !Tenant::dbTransactionLevel();
        if ($dontHaveTransactionLevel)
            Tenant::dbBeginTransaction();

        try {

            if (!$this->_autoResourceDelete('deleteGroup', [$where])) {
                throw new \Exception($this->errorFull());
            }

            if ($dontHaveTransactionLevel)
                Tenant::dbCommit();

            return true;

        } catch (Exception $e) {

            if ($dontHaveTransactionLevel)
                Tenant::dbRollback();

            if (!$this->error) $this->error = $e->getMessage();

            Log::info('moduser/src/Repositories UserRepo::deleteGroup() ERROR');
            Log::error($e);

            // jika sedang dalam transaksi dari parent maka teruskan error nya ke parent transaction nya
            if (!$dontHaveTransactionLevel)
                throw $e;

            return false;
        }
    }

    /**
     * ROLE LEVEL GROUP
     * -------------------------------------------------------------------------
     */

    /**
     * create role level group
     * 
     * @param array $input
     * 
     * @return bool
     */
    public function createLevelGroup(array $input = []) : bool
    {
        // cek kode
        $checkCode = $this->levelGroupExists(['code', $input['code']]);
        if ($checkCode) {
            $this->error = __('validation.unique', [
                'attribute' => __('role.level_group.data_level.field_name.code')
            ]);
            return false;
        }

        // cek level
        $minMaxValues = RoleLevelGroup::select(
            DB::raw('MIN(level_start) as level_start'),
            DB::raw('MAX(level_end) as level_end')
        )->first();
        
        $minValue = $minMaxValues->level_start;
        $maxValue = $minMaxValues->level_end;
        $range = range($minValue, $maxValue);

        // start
        if (in_array($input['level_start'], $range) ) {
            $this->error = 'Level awal sudah digunakan';
            return false;
        }

        // end
        if (in_array($input['level_end'], $range) ) {
            $this->error = 'Level akhir sudah digunakan';
            return false;
        }

        // wrap fungsi utama dalam db transaction agar bisa rollback
        $dontHaveTransactionLevel = !Tenant::dbTransactionLevel();
        if ($dontHaveTransactionLevel)
            Tenant::dbBeginTransaction();

        try {

            $create = $this->_autoResourceCreate('createLevelGroup', [$input]);
            if ($create == false) {
                throw new Exception($this->errorFull());
                return false;
            }

            if ($dontHaveTransactionLevel)
                Tenant::dbCommit();

            return true;
            
        } catch (Exception $e) {

            if ($dontHaveTransactionLevel)
                Tenant::dbRollback();

            if (!$this->error) $this->error = $e->getMessage();

            Log::info('moduser/src/Repositories RoleRepo::createLevelGroup() ERROR');
            Log::error($e);

            // jika sedang dalam transaksi dari parent maka teruskan error nya ke parent transaction nya
            if (!$dontHaveTransactionLevel)
                throw $e;

            return false;
        }
    }

    /**
     * update role level group
     * 
     * @param int|array $where
     * @param array $input
     * 
     * @return bool
     */
    public function updateLevelGroup($where, array $input = []) : bool 
    {
        $oldGroup = $this->getGroup($where);
        if (!$oldGroup) {
            $this->error =  __('lang.data_attribute_not_found', [
                'attribute' => __('role.level_group.data_level.name')
            ]);
            return false;
        }

        if (!UserAuth::user()['level'] == 0 && $oldGroup['locked_data_mode'] == 2) {
            $this->error = 'Data tidak bisa diedit';
            return false;
        }

        // cek kode
        $checkCode = $this->levelGroupExists(['code', $input['code']]);
        if ($input['code'] != $oldGroup['code'] && $checkCode) {
            $this->error = __('validation.unique', [
                'attribute' => __('role.level_group.data_level.field_name.code')
            ]);
            return false;
        }

        // cek level
        $minMaxValues = RoleLevelGroup::select(
            DB::raw('MIN(level_start) as level_start'),
            DB::raw('MAX(level_end) as level_end')
        )->first();
        
        $minValue = $minMaxValues->level_start;
        $maxValue = $minMaxValues->level_end;
        $range = range($minValue, $maxValue);

        // start
        if ($input['level_start'] != $oldGroup && in_array($input['level_start'], $range) ) {
            $this->error = 'Level awal sudah digunakan';
            return false;
        }

        // end
        if ($input['level_end'] != $oldGroup && in_array($input['level_end'], $range) ) {
            $this->error = 'Level akhir sudah digunakan';
            return false;
        }

        // wrap fungsi utama dalam db transaction agar bisa rollback
        $dontHaveTransactionLevel = !Tenant::dbTransactionLevel();
        if ($dontHaveTransactionLevel)
            Tenant::dbBeginTransaction();

        try {

            $update = $this->_autoResourceUpdate('updateLevelGroup', [$where, $input]);
            if ($update == false) {
                throw new Exception($this->errorFull());
                return false;
            }

            if ($dontHaveTransactionLevel)
                Tenant::dbCommit();

            return true;
            
        } catch (Exception $e) {

            if ($dontHaveTransactionLevel)
                Tenant::dbRollback();

            if (!$this->error) $this->error = $e->getMessage();

            Log::info('moduser/src/Repositories RoleGroup::updateLevelGroup() ERROR');
            Log::error($e);

            // jika sedang dalam transaksi dari parent maka teruskan error nya ke parent transaction nya
            if (!$dontHaveTransactionLevel)
                throw $e;

            return false;
        }
    }

    /**
     * delete role level group
     * 
     * @param int|array $where
     * @param array $input
     * 
     * @return bool
     */
    public function deleteLevelGroup($where) : bool 
    {
        $oldData = $this->getGroup($where);
        if (!$oldData) {
            $this->error = __('lang.data_attribute_not_found', [
                'attribute' => __('role.level_group.data_level.name')
            ]);
            return false;
        }

        if (!UserAuth::user()['level'] == 0 && $oldData['locked_data_mode'] != 0) {
            $this->error = 'Data tidak bisa didelete';
            return false;
        }

        // wrap fungsi utama dalam db transaction agar bisa rollback
        $dontHaveTransactionLevel = !Tenant::dbTransactionLevel();
        if ($dontHaveTransactionLevel)
            Tenant::dbBeginTransaction();

        try {

            if (!$this->_autoResourceDelete('deleteLevelGroup', [$where])) {
                throw new \Exception($this->errorFull());
            }

            if ($dontHaveTransactionLevel)
                Tenant::dbCommit();

            return true;

        } catch (Exception $e) {

            if ($dontHaveTransactionLevel)
                Tenant::dbRollback();

            if (!$this->error) $this->error = $e->getMessage();

            Log::info('moduser/src/Repositories UserRepo::deleteLevelGroup() ERROR');
            Log::error($e);

            // jika sedang dalam transaksi dari parent maka teruskan error nya ke parent transaction nya
            if (!$dontHaveTransactionLevel)
                throw $e;

            return false;
        }
    }
}