<?php

namespace hpsynapse\moduser\Repositories;

// 1. Import level PHP
use Exception;

// 2. Import level Package Composer

// 3. Import level Laravel Core
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// 4. Import level Synapse Core
use App\Base\BaseRepository;
use App\Facades\Tenant;
use App\Facades\DbConfig;

// 5. Import level Synapse Module Package

// 6. Import level Synapse MainApp & Module MainApp

// 7. Import level Synapse - Current Module
// model
use hpsynapse\moduser\Models\Role;
use hpsynapse\moduser\Models\RoleGroup;
use hpsynapse\moduser\Models\RoleLevelGroup;
use hpsynapse\moduser\Models\Datarule;
// class
use hpsynapse\moduser\Facades\UserRepo;
use hpsynapse\moduser\Facades\UserAuth;

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
        'Datarule' => [
            'r' => Datarule::class,
            'w' => Datarule::class
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

    /**
     * 
     * @param Array $data
     * 
     *      role_code       WAJIB
     * 
     *      rule            WAJIB
     *      notification    TIDAK DIINPUT SAAT CREATE, JADI JANGAN DIPASSING
     * 
     *      --- isian khusus di tenant manager dan is_global=1 :
     *      is_global       *optional
     * 
     *      bypass_rule     def 0, bypass config khusus untuk global_bypass_rule != 0,
     *                      kalo global_bypass_rule 0 maka otomatis rule akan
     *                      sama dengan value di tenant manager, tidak bisa diedit
     * 
     *                      0 tidak bypass, jika data sudah ada maka tidak akan
     *                          ngereplace
     *                      1 bypass, jika data sudah ada maka akan direplace
     *                          dengan value dari rule tenant manager
     *                      
     *      bypass_datarule     def 0, sama seperti bypass_rule tapi untuk bypass datarule
     *      bypass_dashboard    def 0, sama seperti bypass_rule tapi untuk bypass dashboard
     * 
     *      global                              *optional Array
     *          tenant_id                       tenant id
     *          global_bypass_rule              d
     *          global_bypass_datarule          d
     *          global_bypass_dashboard         d
     *          global_bypass_notification      d
     */
    public function createRole($data)
    {
        
        if(!isset($data['tenant_id']))
            $data['tenant_id'] = config('tenant.id',0);

        $checkRole = Role::where('tenant_id', $data['tenant_id'])
            ->where('role_code', $data['role_code'])
            ->count();

        if ($checkRole > 0) {
            $this->error = 'Role code already exists';
            return false;
        }
        $data['rule'] = $this->formatRule($data['rule']);     

        $createOnTenant = true;
        // jika multi tenant dan di tenant manager
        if (config('AppConfig.system.multitenant.active') && !config('tenant.id',0)) {
            if(isset($data['is_global']) && $data['is_global'] && isset($data['global'])){
                
                $global = $data['global'];
                $tmpBypass = [
                    'bypass_rule'=>isset($data['bypass_rule'])&&$data['bypass_rule']?1:0,
                    'bypass_datarule'=>isset($data['bypass_datarule'])&&$data['bypass_datarule']?1:0,
                    'bypass_dashboard'=>isset($data['bypass_dashboard'])&&$data['bypass_dashboard']?1:0,
                ];
                unset($data['global'],$data['bypass_rule'],$data['bypass_datarule'],$data['bypass_dashboard']);
                    
                // set default config
                $data['global_bypass_rule'] = $global[0]['global_bypass_rule'];
                $data['global_bypass_datarule'] = $global[0]['global_bypass_datarule'];
                $data['global_bypass_dashboard'] = $global[0]['global_bypass_dashboard'];
                $data['global_bypass_notification'] = $global[0]['global_bypass_notification'];                    
                unset($global[0]);
                
                // create main role (tenant manager role)
                $return = $this->_create(new Role, $data);
                if(!$return)
                    return false;//\Exception($this->errorFull()); 

                // crate global per tenant
                $this->updateGlobalRole($data,$global,false,$tmpBypass);

                $createOnTenant = false;                 
                
            }
        }

        // jika bukan global role berarti create role seperti di tenant biasa
        if($createOnTenant){
            unset($data['global']);
            $data['is_global'] = 0;
            $data['global_bypass_rule'] = 0;
            $data['global_bypass_datarule'] = 0;
            $data['global_bypass_dashboard'] = 0;
            $data['global_bypass_notification'] = 0;
            
            $return = $this->_create(new Role, $data);
            if(!$return)
                return false;//throw new \Exception($this->errorFull()); 
        }                       

        return $return;
    }

    /**
     * Pastikan data yang diinput telah difilter sesuai ACL user, karena ACL
     * diproses di level controller
     * 
     * @param Array $where
     * @param Array @data
     * 
     *      role_code       jika
     *      rule            jika ada
     *      datarule_id
     *      role_group_id
     * 
     *      --- isian khusus di tenant manager dan is_global=1 :
     *      is_global       *optional,
     *      global          *optional, jika tidak diedit maka jangan dipassing
     * 
     *      bypass_rule     def 0, bypass config khusus untuk global_bypass_rule != 0,
     *                      kalo global_bypass_rule 0 maka otomatis rule akan
     *                      sama dengan value di tenant manager, tidak bisa diedit
     *                      --dari form role cuma bisa 2 opsi :
     *                      0 tidak bypass, jika data sudah ada maka tidak akan
     *                          ngereplace
     *                      1 full bypass, jika data sudah ada maka akan direplace
     *                          dengan value dari rule tm
     *                      --[NEXT DEV] dari form khusus rule nambah 3 opsi :
     *                      2 mode add, hanya menambah acl (has_accss,c,r,u,d) 
     *                          yg diceklis
     *                      3 mode uncek, hanya me-uncek acl (has_accss,c,r,u,d) 
     *                          yg diceklis
     *                      4 mode update, hanya mereplace acl yg dicek dan uncek
     *                          yg dicek baru akan dianggp menambah, yg diuncek
     *                          akan dianggap mengurangi
     * 
     *      bypass_datarule     0/1, def 0, akan bypass role di seluruh tenant atau tidak
     *      bypass_dashboard    0/1, def 0, akan bypass role di seluruh tenant atau tidak
     * 
     *      global          *optional Array, 
     *          tenant_id                       tenant id
     *          global_bypass_rule
     *          global_bypass_datarule
     *          global_bypass_dashboard
     *          global_bypass_notification
     */
    public function updateRole($where, $data)
    {
        $oldRole = $this->getRole($where);

        if ($this->checkSystemRole($where)){
            $this->error = 'Cannot update system role in here';
            return false;
        }

        // jika ganti role_code
        $isRoleCodeChanged = isset($data['role_code']) && $data['role_code'] != $oldRole['role_code'];

        if (!isset($data['tenant_id']))
            $data['tenant_id'] = config('tenant.id',0);

        $checkRole = Role::where('tenant_id', $data['tenant_id'])->where('role_code', $data['role_code'])->count();
        if ($isRoleCodeChanged && $checkRole > 0) {
            $this->error = 'Role code already exists';
            return false;
        }

        $updateOnTenant = true;

        // jika multi tenant
        if (config('AppConfig.system.multitenant.active')){
            // jika di tenant maka tidak boleh edit global config
            if(config('tenant.id',0)) {
                // jika role global, batasi editan2 sesuai config global
                if($oldRole['is_global']){                    

                    if($oldRole['global_bypass_rule']==0)
                        unset($data['rule']);  
                    if($oldRole['global_bypass_datarule']==0)
                        unset($data['datarule_id'],$data['datarule_code']);
                    if($oldRole['global_bypass_dashboard']==0)
                        unset($data['dashboard_type']);
                }

            // jika di tenant manager
            }else{          
                // jika global role, use case global role :
                // - tidak edit global config
                // - edit global config
                // - set pertama kali dari bukan global menjadi global
                // - unset dari global role jadi role biasa
                if($oldRole['is_global'] || (array_key_exists('is_global',$data) && $data['is_global'])){

                    $return = $this->updateRoleSetGlobalConfig($data,$oldRole);
                    $updateOnTenant = false;                 
                    
                }
            }
        }

        // jika bukan global role atau jika di tenant
        if($updateOnTenant){
            
            if(array_key_exists('rule',$data))
                $data['rule'] = $this->formatRule($data['rule']);

            unset(
                $data['global'],
                $data['is_global'],
                $data['global_bypass_rule'],
                $data['global_bypass_datarule'],
                $data['global_bypass_dashboard'],
                $data['global_bypass_notification']
            );

            $return = $this->_update(new Role, $where, $data);
            if(!$return)
                return false;//throw new \Exception($this->errorFull()); 
        }

        // jika ganti role code maka ubah disemua
        if($isRoleCodeChanged){
            UserRepo::changeUsersRoleCode($oldRole['role_code'],$data['role_code']);
        }
            
        return $return;
    }

    /**
     * untuk proses update role global di tenant manager.
     * 
     * untuk update global role, use case global role :
     * - tidak edit global config
     * - edit global config
     * - set pertama kali dari bukan global menjadi global
     * - unset dari global role jadi role biasa
     */
    private function updateRoleSetGlobalConfig($data,$oldRole)
    {        
        $tmpBypass = [
            'bypass_rule'=>isset($data['bypass_rule'])&&$data['bypass_rule']?1:0,
            'bypass_datarule'=>isset($data['bypass_datarule'])&&$data['bypass_datarule']?1:0,
            'bypass_dashboard'=>isset($data['bypass_dashboard'])&&$data['bypass_dashboard']?1:0,
            'bypass_notification'=>isset($data['bypass_notification'])&&$data['bypass_notification']?1:0,
        ];
        unset($data['bypass_rule'],$data['bypass_datarule'],$data['bypass_dashboard'],$data['bypass_notification']);
        $isUnsetGlobal = false;

        // jika sebelumnya memang role global, maka use case :
        // - tidak edit global config
        // - edit global config
        // - unset dari global role jadi role biasa
        if($oldRole['is_global']){
                        
            // jika unset global (mengubah role global jadi tidak global)
            if(array_key_exists('is_global',$data) && $data['is_global']==0){
                $data['global'] = [
                    [
                        'tenant_id'=>0,
                        'global_bypass_rule'=>0,
                        'global_bypass_datarule'=>0,
                        'global_bypass_dashboard'=>0,
                        'global_bypass_notification'=>0,
                    ]
                ];
                $isUnsetGlobal = true;
            }               

        // jika sebelumnya bukan global role maka ini :
        // - set role global baru
        }else{
            // jika tidak menyertakan config global maka set default, karena harus menyertakan, minimal untuk default
            if(!array_key_exists('global',$data)){
                $data['global'] = [
                    [
                        'tenant_id'=>0,
                        'global_bypass_rule'=>0,
                        'global_bypass_datarule'=>0,
                        'global_bypass_dashboard'=>0,
                        'global_bypass_notification'=>0,
                    ]
                ];
            }
        }


        if(array_key_exists('global',$data)){
            if(empty($data['global']))
                $data['global'] = [
                    [
                        'tenant_id'=>0,
                        'global_bypass_rule'=>0,
                        'global_bypass_datarule'=>0,
                        'global_bypass_dashboard'=>0,
                        'global_bypass_notification'=>0,
                    ]
                ];

            $global = $data['global'];
            // set default config
            $data['global_bypass_rule'] = $global[0]['global_bypass_rule'];
            $data['global_bypass_datarule'] = $global[0]['global_bypass_datarule'];
            $data['global_bypass_dashboard'] = $global[0]['global_bypass_dashboard'];
            $data['global_bypass_notification'] = $global[0]['global_bypass_notification'];                    
            unset($data['global'],$global[0]);
            if(empty($global))$global=[];

        // jika tidak edit global config
        }else{
            $data['global_bypass_rule'] = $oldRole['global_bypass_rule'];
            $data['global_bypass_datarule'] = $oldRole['global_bypass_datarule'];
            $data['global_bypass_dashboard'] = $oldRole['global_bypass_dashboard'];
            $data['global_bypass_notification'] = $oldRole['global_bypass_notification']; 
            $global = false;
        }
        
        if(array_key_exists('rule',$data) && $tmpBypass['bypass_rule']<=2)
            $data['rule'] = $this->formatRule($data['rule']);
        
        // update main role (tenant manager role)
        $return = $this->_update(new Role, ['id',$oldRole['id']], $data);
        if(!$return)
            return false;//throw new \Exception($this->errorFull()); 

        if($isUnsetGlobal){
            $this->updateUnGlobalRole($oldRole['role_code']);
        }else{
            // create global per tenant
            $this->updateGlobalRole($data,$global,$oldRole,$tmpBypass);
        }

        return $return;
    }

    /**
     * ubah global role menjadi bukan global role
     */
    private function updateUnGlobalRole($roleCode)
    {
        
        $tenantList = Tenant::listTenant([],0,0);

        foreach ($tenantList['data'] as $value) {

            $curRole = (new Role())->setTenantId($value['id'])
                ->where('role_code',$roleCode)
                ->update([                    
                    'is_global' => 0,
                    'global_type' => 0,
                    'global_bypass_rule'=>0,
                    'global_bypass_datarule'=>0,
                    'global_bypass_dashboard'=>0,
                    'global_bypass_notification'=>0,
                    
                ]);
        }
        
        DbConfig::deleteGlobalConfig(
            'role.config.global.item.'.$roleCode
        );
    }

    /**
     * Update Role-Role global di tenant
     * 
     * @param Array         $mainRole       record role di Tenant manager yang diupdate
     * @param Array|False   $globalConfig   false jika tidak edit global, 
     *                                      empty array jika unset
     * @param Array|False   $oldRole        false jika create role baru
     *                                      record role di tenant manager sebelum diupdate
     * @param Array         $baypassConfig  Array config bypass, tiap
     *          bypass_rule
     *          bypass_datarule
     *          bypass_dashboard
     *          bypass_notification
     */
    private function updateGlobalRole($mainRole,$globalConfig,$oldRole=false,$bypassConfig=[])
    {

        $isRoleCodeChanged = false;
        $isEditGlobal = is_array($globalConfig);

        // jika update role existing di tenant manager
        if($oldRole){
            
            $isNewData = false;            

            // jika ganti role code
            if($mainRole['role_code'] != $oldRole['role_code']){
                $roleCode = $oldRole['role_code'];
                $isRoleCodeChanged = true;

            // jika tidak ganti role code
            }else{
                $roleCode = $mainRole['role_code'];

            }            

            // get config lama
            $oldConfig = DbConfig::listGlobalConfig('role.config.global.item.'.$oldRole['role_code'],true,true);
            
        // jika create new role
        }else{

            $isNewData = true;
            
            $oldConfig = [];
            $roleCode = $mainRole['role_code'];

        }

        $tenantList = Tenant::listTenant([],0,0);

        // ubah format global config jadi global per tenant
        $globalPerTenant = [];
        if(!empty($globalConfig))
            foreach ($globalConfig as $key => $value)
                if($value['tenant_id'])
                    $globalPerTenant[$value['tenant_id']] = $value;
        
        foreach ($tenantList['data'] as $value) {
            if(!Tenant::dbExists($value['id']))continue;
            
            $curRole = (new Role())->setTenantId($value['id'])
                ->where('role_code',$roleCode)
                ->first();

            $data = $mainRole;

            $data['tenant_id'] = $value['id'];

            if($isEditGlobal){
                // jika ada custom config global per tenant
                if(isset($globalPerTenant[$value['id']])){
                    $data['global_bypass_rule'] = $globalPerTenant[$value['id']]['global_bypass_rule'];
                    $data['global_bypass_datarule'] = $globalPerTenant[$value['id']]['global_bypass_datarule'];
                    $data['global_bypass_dashboard'] = $globalPerTenant[$value['id']]['global_bypass_dashboard'];
                    $data['global_bypass_notification'] = $globalPerTenant[$value['id']]['global_bypass_notification'];  

                    // tambah ke DbConfig role
                    DbConfig::setGlobalConfig(
                        'role.config.global.item.'.$roleCode,
                        $value['id'],
                        $globalPerTenant[$value['id']]
                    );

                // jika tidak dicustom config maka cek apakah sebelumnya custom, 
                // kalo iya maka hapus
                }else if(isset($oldConfig[$value['id']])){
                    
                    DbConfig::deleteGlobalConfig(
                        'role.config.global.item.'.$oldRole['role_code'],
                        $value['id']
                    );
                }
            }

            // jika role di tenant sudah ada
            if($curRole){

                // TO DO : SOON - cek mode bypass rule 2,3 dan 4
                
                // jika rule tidak di-bypass/replace
                if($bypassConfig['bypass_rule']==0)
                    unset($data['rule']);
                
                // jika datarule tidak di-bypass/replace
                if($bypassConfig['bypass_datarule']==0)
                    unset($data['datarule_id'],$data['datarule_code']);

                // jika dashboard tidak di bypass/replace
                if($bypassConfig['bypass_dashboard']==0)
                    unset($data['dashboard_type']);

                $tmpRole = (new Role())->setTenantId($value['id'])
                    ->where('id',$curRole->id)
                    ->update($data);
                    
                if($isRoleCodeChanged)
                    UserRepo::changeUsersRoleCode(
                        $curRole->role_code,
                        $data['role_code'],
                        $value['id']
                    );

                
            // jika role di tenant belum ada maka langsung create saja
            }else{

                $tmpRole = (new Role())->setTenantId($value['id'])
                    ->create($data);
            }
            
        }
        
    }

    /**
     * memastikan isian rule nya sesuai
     */
    public function formatRule($rule)
    {
        $listacl = config('AppConfig.acl');
        foreach ($listacl as $aclPerModule) {
            foreach ($aclPerModule['children'] as $key2 => $acl) {
                // jika config crud acl nya ada salah satu yang terisi berarti tidak ada pilihan has_access
                if(isset($rule[$key2]) && array_key_exists('c',$rule[$key2]) && ($acl['crud']['c'] || $acl['crud']['r'] || $acl['crud']['u'] || $acl['crud']['d'])){
                    if($rule[$key2]['c']==0 && $rule[$key2]['r']==0 && $rule[$key2]['u']==0 && $rule[$key2]['d']==0){
                        $rule[$key2]['has_access']=0;
                    }
                }
            }
        }
        
        return $rule;
    }

    /**
     * Update config notification
     * @param Array $where
     * @param Array @data
     *      notification    Array, config notifikasi
     * 
     *      --- isian khusus di tenant manager dan is_global=1 :
     *      bypass_notification     def 0, bypass config khusus untuk global_bypass_rule != 0,
     *                              kalo global_bypass_rule 0 maka otomatis rule akan
     *                              sama dengan value di tenant manager, tidak bisa diedit
     *                              --dari form role cuma bisa 2 opsi :
     *                              0 tidak bypass, jika data sudah ada maka tidak akan
     *                                  ngereplace
     *                              1 full bypass, jika data sudah ada maka akan direplace
     *                                  dengan value dari rule tm
     *                              [SOON, NEXT DEV]--dari form khusus rule nambah 3 opsi :
     *                              2 mode add, hanya menambah acl (has_accss,c,r,u,d) 
     *                                  yg diceklis
     *                              3 mode uncek, hanya me-uncek acl (has_accss,c,r,u,d) 
     *                                  yg diceklis
     *                              4 mode update, hanya mereplace acl yg dicek dan uncek
     *                                  yg dicek baru akan dianggp menambah, yg diuncek
     *                                  akan dianggap mengurangi
     * 
     */
    public function updateRoleNotificationConfig($where, $data)
    {

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