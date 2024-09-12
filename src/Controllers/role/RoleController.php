<?php

namespace hpsynapse\moduser\Controllers\role;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use hpsynapse\moduser\Facades\RoleRepo;
use hpsynapse\moduser\Facades\UserAuth;
use hpsynapse\moduser\Facades\UserLog;

use App\Base\BaseController;

class RoleController extends BaseController
{
    protected $accessRuleKey = 'moduser.role';

    private function accessCheck($rw = 'r')
    {
        $access = (UserAuth::hasAccess($this->accessRuleKey, $rw)
            || UserAuth::isWebDev());

        $hasAccess = true;
        if (!$access) {
            $hasAccess = false;
            $this->setError(__('alert.access_denied'), false, 403);
        }

        return $hasAccess;
    }

    public function __construct()
    {
        $this->forceApiOutput();
    }

    /**
     * GET /api/user/role
     * 
     * list role (selain role system)
     * 
     * @param Request $request
     * 
     */
    public function readList(Request $request)
    {
        if (!(UserAuth::hasAccess('moduser.user', 'r') || $this->accessCheck('r'))) {
            $this->setError(__('alert.access_denied'), false, 403);
            return $this->done();
        }

        // dd(config('database'));
        $orderBy = [];
        $filter = [];

        if ($request->input('q', false))
            $filter['q'] = $request->input('q');

        //jika menyertakan status
        if ($request->input('status', false))
            $filter[] = ['status', $request->input('status')];

        $filter[] = ['system_role', 0];

        if (UserAuth::isLogin()) {
            $roles = explode(';', trim(UserAuth::user('role'), ';'));
            foreach ($roles as $role) {
                $filter[] = ['role_code', '!=', $role];
            }
            $filter[] = ['level', '>', UserAuth::user('level')];
        }

        //jika multitenant aktif dan bukan dari aplikasi owner maka filter berdasarkan tenant nya
        if (config('AppConfig.system.multitenant.active', false)){

            // jika di tenant
            if(config('tenant.id', 0) > 1) {
                $filter[] = ['tenant_id', config('tenant.id')];

            //     // 
            //     if(
            //         UserAuth::hasAccess('moduser.role.can_edit_global_role') 
            //         || UserAuth::isWebDev()
            //     ){

            //     }
            // // jika di tenant manager
            // }else{
            //     if(
            //         UserAuth::hasAccess('moduser.role.can_edit_global_role') 
            //         || UserAuth::isWebDev()
            //     ){

            //     }
            }
        }

        //jika menyertakan order by
        if ($request->input('orderBy', false)) {
            $orderBy = $request->input('orderBy');

            if ($orderBy == 'tenant') {
                $orderBy = 'tenant_id';
            } else if ($orderBy == 'tenant_group') {
                $orderBy = 'tenant_group_id';
            }

            $orderBy = [$request->input('orderBy'), $request->input('orderType', 'ASC')];
        }

        $limit['offset'] = $request->input('offset', 0);
        $limit['limit'] = $request->input('limit', 0);
        $this->output['data'] = RoleRepo::listRole(
            $filter,
            $limit['offset'],
            $limit['limit'],
            $orderBy
        );

        return $this->done();
    }

    /**
     * GET /api/user/role/{id}
     * 
     * @param \Illuminate\Http\Request $request
     * @param int $id   Route Param {id}
     */
    public function readOne(Request $request, $id)
    {
        if (!UserAuth::hasAccess($this->accessRuleKey, 'r')) {
            $this->setError(__('alert.access_denied'), false, 403);
            return $this->done();
        } 
        
        $this->buildParams();
        $this->output['params']['filter'][] = ['id', $id];
        $data = RoleRepo::getRole($this->output['params']['filter']);

        if (!$data) {
            $this->setError(__('lang.data_not_found'));
        } else {
            $this->setData($data);
        }
        return $this->done();
    }

    /**
     * POST - /api/user/role
     * 
     * Create new role
     * 
     * @param Request $request 
     *      role_code   String
     *      level       Integer 
     *      name        String
     *      rule        Array rule format
     * 
     *      --- isian khusus di tenant manager dan is_global=1 :
     *      is_global       tinyint     1/0
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
     *      global          Array       List config global (key nya index urutan)
     *          tenant_id
     *          global_bypass_rule
     *                                  0 rule hanya bisa diedit di tenant manager
     *                                  1 bisa dibypass dari tenant
     *                                  2 bisa dibypass tapi oleh user smartcoop (ada rule tambahnnya, acl moduser.role.can_bypass_global)
     *          global_bypass_datarule
     *          global_bypass_dashboard
     *          global_bypass_notification
     */
    public function create(Request $request)
    {
        if (!UserAuth::hasAccess($this->accessRuleKey, 'c')) {
            $this->setError(__('alert.access_denied'), false, 403);
            return $this->done();
        }

        // $input = $request->all(); //$request->only(['name', 'email', 'password']);
        $input = $request->except([
            'tenant_id',
            'tenant_group_id',
            'notification',
            'is_global',
            'global',
            'notification',// tidak diset disini
            //
            'global_bypass_rule',
            'global_bypass_datarule',
            'global_bypass_dashboard',
            'global_bypass_notification',
            //
            'bypass_rule',
            'bypass_datarule',
            'bypass_dashboard',
            'bypass_notification'
        ]);

        $validator = [
            'role_code' => 'required|min:3|max:191',
            'name' => 'required|min:3|max:191',
            'rule' => 'required'
        ];

        $validator = Validator::make($input, $validator);

        if (isset($input['id'])) unset($input['id']);

        if (config('AppConfig.system.multitenant.active')) {
            // jika di tenant tidak bisa ubah tenant_id
            if (config('tenant.id',0)) {
                $input['tenant_id'] = config('tenant.id');
                
            // jika di tenant manager 
            } else {
                $input['tenant_id'] = 0;

                // jika set role sebagai role global
                if($request->input('is_global')){
                    $input['is_global'] = $request->input('is_global');
                    if($request->input('global'))
                        $input['global'] = $request->input('global');

                    $input['bypass_rule'] = $request->input('bypass_rule',0);
                    $input['bypass_datarule'] = $request->input('bypass_datarule',0);
                    $input['bypass_dashboard'] = $request->input('bypass_dashboard',0);
                }
            }
        } else {
            $input['tenant_id'] = 0;
        }

        if ($validator->fails()) {
            $this->setError(__('validation.inputerror'), $validator->messages());
            return $this->done();
        }

        //jika berhasil
        if ($newRole = RoleRepo::createRole($input)) {
            UserLog::addLog(UserAuth::user('id'), 'moduser_role', 'create_role', [
                'new_role' => $newRole
            ]);
            $this->setAlert('Data Inserted successfully', 'success');
        } else {
            $this->setError(RoleRepo::error(), 'success');
        }

        return $this->done();
    }

    /**
     * POST/PUT - /api/user/role/{roleId}
     * 
     * update role
     * 
     * @param Request $request 
     *      role_code           String
     *      level               Integer 
     *      name                String
     *      rule                Array rule format
     * 
     *      --- isian khusus di tenant manager dan is_global=1 :
     *      is_global       tinyint     1/0
     *      bypass_rule     def 0, bypass config khusus untuk global_bypass_rule != 0,
     *                      kalo global_bypass_rule 0 maka otomatis rule akan
     *                      sama dengan value di tenant manager, tidak bisa diedit
     *                      --dari form role cuma bisa 2 opsi :
     *                      0 tidak bypass, jika data sudah ada maka tidak akan
     *                          ngereplace
     *                      1 full bypass, jika data sudah ada maka akan direplace
     *                          dengan value dari rule tm
     *                      [SOON, NEXT DEV]--dari form khusus rule nambah 3 opsi :
     *                      2 mode add, hanya menambah acl (has_accss,c,r,u,d) 
     *                          yg diceklis
     *                      3 mode uncek, hanya me-uncek acl (has_accss,c,r,u,d) 
     *                          yg diceklis
     *                      4 mode update, hanya mereplace acl yg dicek dan uncek
     *                          yg dicek baru akan dianggp menambah, yg diuncek
     *                          akan dianggap mengurangi
     * 
     *      bypass_datarule         0/1, def 0, akan bypass role di seluruh tenant atau tidak
     *      bypass_dashboard        0/1, def 0, akan bypass role di seluruh tenant atau tidak
     * 
     *      global          Array       List config global (key nya index urutan)
     *          tenant_id
     *          global_bypass_rule
     *                                  0 rule hanya bisa diedit di tenant manager
     *                                  1 bisa dibypass dari tenant
     *                                  2 bisa dibypass tapi oleh user smartcoop (ada rule tambahnnya, acl moduser.role.can_bypass_global)
     *          global_bypass_datarule
     *          global_bypass_dashboard
     *          global_bypass_notification
     */
    public function update(Request $request,$id)
    {

        if (!UserAuth::hasAccess($this->accessRuleKey, 'u')) {
            $this->setError(__('alert.access_denied'), false, 403);
            return $this->done();
        }

        // $id = $request->route('id');
        // $input = $request->all();
        $exept = [
            'id',
            'tenant_id',// tidak bisa diedit jadi jangan diterima
            'tenant_group_id',// tidak bisa diedit jadi jangan diterima
            'notification',// tidak diset disini
            // 'is_global',
            // 'global',
            'global_bypass_rule',
            'global_bypass_datarule',
            'global_bypass_dashboard',
            'global_bypass_notification',
            'bypass_rule',
            'bypass_datarule',
            'bypass_dashboard',
            'bypass_notification'
        ];
        $exept = $this->updateFilterInputByACL($exept);
        $input = $request->except($exept);

        $logData = ['old_role'=>RoleRepo::getRole(['id', $id])];

        // jika multi tenant
        if (config('AppConfig.system.multitenant.active')) {

            // jika di tenant
            if (config('tenant.id',0)) {

                // tidak boleh edit global config di tenant
                unset($input['global'],$input['is_global']);

                // jika global rule maka pastikan inputan sesuai config bypass global nya
                if($logData['old_role']['is_global']){
                    $input = $this->updateGlobalRuleUpdateConfig($logData,$input);
                }

            // jika di tenant manager 
            } else {

                // jika set role sebagai role global atau memang sudah role global
                if($logData['old_role']['is_global'] || (array_key_exists('is_global',$input) && $input['is_global']) ){
                        
                    $input['bypass_rule'] = $request->input('bypass_rule',0);
                    $input['bypass_datarule'] = $request->input('bypass_datarule',0);
                    $input['bypass_dashboard'] = $request->input('bypass_dashboard',0);
                }
                
            }
        }

        $this->output['message'] = 'Data berhasil diupdate';
        $this->output['data'] = RoleRepo::updateRole(['id', $id], $input);

        $logData['new_role'] = RoleRepo::getRole(['id', $id]);

        if (!$this->output['data'])
            $this->setError('Update failed : ' . RoleRepo::error());
        
        UserLog::addLog(
            UserAuth::user('id'), 
            'moduser_role', 
            'update_role', 
            $logData
        );

        return $this->done();
    }

    private function updateFilterInputByACL($exept)
    {        
        if (!UserAuth::hasAccess($this->accessRuleKey . '.can_edit_role_code'))
            $exept[] = 'role_code';

        if (!UserAuth::hasAccess($this->accessRuleKey . '.can_edit_role_type'))
            $exept[] = 'role_type';

        if (!UserAuth::hasAccess($this->accessRuleKey . '.can_edit_role_group_id')){
            $exept[] = 'role_group_id';
            $exept[] = 'role_group_code';
        }

        if (!UserAuth::hasAccess($this->accessRuleKey . '.can_edit_level'))
            $exept[] = 'level';

        if (!UserAuth::hasAccess($this->accessRuleKey . '.can_edit_rule'))
            $exept[] = 'rule';
        
        // if (!UserAuth::hasAccess($this->accessRuleKey . '.can_edit_notification_config'))
        //     $exept[] = 'notification';
        
        if (!UserAuth::hasAccess($this->accessRuleKey . '.can_edit_global_role')){
            $exept[] = 'is_global';
            $exept[] = 'global';
        }
        

        return $exept;
    }

    private function updateGlobalRuleUpdateConfig($logData,$data)
    {

        //----DETEK RULE CONFIG
        // 0 rule hanya bisa diedit di tenant manager
        if($logData['old_role']['global_bypass_rule']==0){
            unset($data['rule']);                
        // 2 bisa dibypass tapi oleh user smartcoop (ada rule tambahnnya, acl moduser.role.can_bypass_global)
        }else if(
            $logData['old_role']['global_bypass_rule']==2 
            && !UserAuth::hasAccess($this->accessRuleKey . '.can_bypass_global')
        ){
            unset($data['rule']); 
        }
        
        //----DETEK DATARULE CONFIG
        // 0 datarule hanya bisa diedit di tenant manager
        if($logData['old_role']['global_bypass_datarule']==0){
            unset($data['datarule_id'],$data['datarule_code']);                
        // 2 bisa dibypass tapi oleh user smartcoop (ada rule tambahnnya, acl moduser.role.can_bypass_global)
        }else if(
            $logData['old_role']['global_bypass_datarule']==2 
            && !UserAuth::hasAccess($this->accessRuleKey . '.can_bypass_global')
        ){
            unset($data['datarule_id'],$data['datarule_code']); 
        }                    

        //----DETEK DASHBOARD CONFIG
        // 0 dashboard hanya bisa diedit di tenant manager
        if($logData['old_role']['global_bypass_dashboard']==0){
            unset($data['dashboard_type']);                
        // 2 bisa dibypass tapi oleh user smartcoop (ada rule tambahnnya, acl moduser.role.can_bypass_global)
        }else if(
            $logData['old_role']['global_bypass_dashboard']==2 
            && !UserAuth::hasAccess($this->accessRuleKey . '.can_bypass_global')
        ){
            unset($data['dashboard_type']); 
        }
        
        // //----DETEK NOTIFICATION CONFIG
        // // 0 notification hanya bisa diedit di tenant manager
        // if($logData['old_role']['global_bypass_notification']==0){
        //     unset($data['notification']);                
        // // 2 bisa dibypass tapi oleh user smartcoop (ada rule tambahnnya, acl moduser.role.can_bypass_global)
        // }else if(
        //     $logData['old_role']['global_bypass_notification']==2 
        //     && !UserAuth::hasAccess($this->accessRuleKey . '.can_bypass_global')
        // ){
        //     unset($data['notification']); 
        // }
        return $data;
    }

    /**
     * NEXT DEV
     * POST/PUT - /api/user/role/{roleId}/rule
     * 
     * update rule, khusus di tenant manager
     * 
     * @param Request $request 
     *      rule                Array rule format
     * 
     *      --- isian khusus di tenant manager dan is_global=1 :
     *      bypass_rule     def 0, bypass config khusus untuk global_bypass_rule != 0,
     *                      kalo global_bypass_rule 0 maka otomatis rule akan
     *                      sama dengan value di tenant manager, tidak bisa diedit
     *                      --dari form role cuma bisa 2 opsi :
     *                      0 tidak bypass, jika data sudah ada maka tidak akan
     *                          ngereplace
     *                      1 full bypass, jika data sudah ada maka akan direplace
     *                          dengan value dari rule tm
     *                      [SOON, NEXT DEV]--dari form khusus rule nambah 3 opsi :
     *                      2 mode add, hanya menambah acl (has_accss,c,r,u,d) 
     *                          yg diceklis
     *                      3 mode uncek, hanya me-uncek acl (has_accss,c,r,u,d) 
     *                          yg diceklis
     *                      4 mode update, hanya mereplace acl yg dicek dan uncek
     *                          yg dicek baru akan dianggp menambah, yg diuncek
     *                          akan dianggap mengurangi
     * 
     */
    public function updateRule(Request $request,$id)
    {
        if (!(
                UserAuth::hasAccess($this->accessRuleKey, 'u') 
                && UserAuth::hasAccess($this->accessRuleKey . '.can_edit_rule')
        )) {
            $this->setError(__('alert.access_denied'), false, 403);
            return $this->done();
        }

    }

    /**
     * NEXT DEV
     * POST/PUT - /api/user/role/{roleId}/notification
     * 
     * update config notifikasi
     * 
     * @param Request $request 
     *      notification        Array
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
    public function updateNotification(Request $request,$id)
    {
        if (!(
                UserAuth::hasAccess($this->accessRuleKey, 'u') 
                && UserAuth::hasAccess($this->accessRuleKey . '.can_edit_notification_config')
        )) {
            $this->setError(__('alert.access_denied'), false, 403);
            return $this->done();
        }

        $logData = ['old_role'=>RoleRepo::getRole(['id', $id])];

        if(
            $logData['old_role']['global_bypass_notification']==2 
            && !UserAuth::hasAccess($this->accessRuleKey . '.can_bypass_global')
        ){
            $this->setError(__('alert.access_denied'), false, 403);
            return $this->done();
        }

    }

    /**
     * DELETE - /api/user/role/{roleId}
     * 
     * delete role
     */
    public function delete(Request $request)
    {

        if (!UserAuth::hasAccess($this->accessRuleKey, 'u')) {
            $this->setError(__('alert.access_denied'), false, 403);
            return $this->done();
        }

        $id = $request->route('id');

        if (UserAuth::isLogin()) { //} && $id != UserAuth::user('id')){
            // $filter[] = ['id', $id];
            $filter[] = ['level', '>', UserAuth::user('level')];
            $data = RoleRepo::listRole($filter);
            if ($data['count'] <= 0) {
                $this->setError('Permission denied');
                return $this->done();;
            }
        }

        $logData = ['deleted_role'=>RoleRepo::getRole(['id', $id])];
        if (!RoleRepo::deleteRole($id)) {
            $this->setError('Error : ' . RoleRepo::error());
        }        
        UserLog::addLog(UserAuth::user('id'), 'moduser_role', 'delete_role', $logData);

        return $this->done();
    }
}
