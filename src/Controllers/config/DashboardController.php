<?php

namespace hpsynapse\moduser\Controllers\config;

use Illuminate\Http\Request;

use hpsynapse\moduser\Facades\UserLog;
use hpsynapse\moduser\Facades\UserAuth;
use hpsynapse\moduser\Facades\RoleRepo;

use App\Base\BaseController;
use App\Facades\DbConfig;
use hpsynapse\modcoop\Facades\Master\Config;

class DashboardController extends BaseController
{
    public function __construct()
    {
        $this->forceApiOutput();
    }
    
    /**
     * get - /api/user/config/dashboard
     * get konfigurasi dashboard
     * 
     * @param Request $request
     */
    public function getConfigDashboard(Request $request)
    {
        
    }
    
    /**
     * PUT/POST - /api/user/config/dashboard
     * update konfigurasi dashboard
     * 
     * @param Request $request
     */
    public function updateConfigDashboard(Request $request)
    {
        $input = $request->all();
        
        if (!$input['role']) {
            $this->setError('Role harus dipilih');
            return $this->done();
        }

        $setKey = 'role_'.$input['role'];
        $setValue = [];
        foreach ($input['features'] as $key => $value) {
            $setValue[$key] = $value;
        }
        $setValue = json_encode($setValue);

        DbConfig::setConfig('coop_dashboard', $setKey, $setValue);

        $this->output['message'] = __('alert.update_success',['attribute'=>__('coop_master.form_config_dashboard.name')]);    
        return $this->done();
    }

    
    /**
     * GET - /api/user/config/dashboard
     * list
     *
     * @param \Illuminate\Http\Request $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function readList(Request $request)
    {
        // if (!$this->accessCheck('r')) {
        //     return $this->done();
        // }

        $this->buildParams();

        $this->setData([
            'data'=>DbConfig::listGlobalConfig('dashboard.config.item',true)
        ]);

        return $this->done();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (!$this->accessCheck('c')) {
            return $this->done();
        }

        $input = $request->all();
        $input['tenant_id'] = config('tenant.id',0);
        $input['created_by'] = UserAuth::user('id');

        $data = RoleRepo::createGroup($input);
        if ($data) {
            $this->setData($data)
                ->setMessage(__('alert.update_success', [
                    'attribute' => __('role.group.data_group.name')
                ]));
        } else {
            $this->setError(
                RoleRepo::error(),
                RoleRepo::errorValidator()
            );
        }

        return $this->done();
    }

    /**
     * Display the specified resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * 
     * @return \Illuminate\Http\Response
     */
    public function readOne(Request $request, int $id)
    {
        if (!$this->accessCheck('r')) {
            return $this->done();
        }
        
        $this->buildParams();
        $this->output['params']['filter'][] = ['id', $id];

        $data = RoleRepo::getGroup($this->output['params']['filter']);
        if (!$data) {
            $this->setError(__('lang.data_not_found'));
        } else {
            $this->setData($data);
        }

        return $this->done();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * 
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id)
    {
        if (!$this->accessCheck('u')) {
            return $this->done();
        }

        $input = $request->all();
        $input['updated_by'] = UserAuth::user('id');

        $data = RoleRepo::updateGroup(['id', $id], $input);
        if ($data) {
            $this->setData($data)
                ->setMessage(__('alert.update_success', [
                    'attribute' => __('role.group.data_group.name')
                ]));
        } else {
            $this->setError(
                RoleRepo::error(),
                RoleRepo::errorValidator()
            );
        }

        return $this->done();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * 
     * @return \Illuminate\Http\Response
     */
    public function delete(int $id)
    {
        if (!$this->accessCheck('d')) {
            return $this->done();
        }

        if (RoleRepo::deleteGroup($id)) {
            $this->setMessage(__('alert.delete_success', [
                'attribute' => __('role.group.data_group.name')
            ]));
        } else {
            $this->setError(RoleRepo::error());
        }

        return $this->done();
    }

}
