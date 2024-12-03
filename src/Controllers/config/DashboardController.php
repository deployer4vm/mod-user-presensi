<?php

namespace hpsynapse\moduser\Controllers\config;

use Illuminate\Http\Request;

use hpsynapse\moduser\Facades\UserLog;
use hpsynapse\moduser\Facades\UserAuth;
use hpsynapse\moduser\Facades\RoleRepo;

use App\Base\BaseController;
use App\Facades\DbConfig;
use hpsynapse\modcoop\Facades\Master\Config;
use Illuminate\Support\Facades\File;

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

        $data = [];
        $dataConfig = DbConfig::listGlobalConfig('dashboard.config.item',true,true);
        // filter sesuai config tenant nya
        foreach ($dataConfig as $value) {
            if(
                empty($value['tenant']) 
                || $value['id']==1 
                || config('tenant.id',0)==0 
                || in_array(config('tenant.id'),$value['tenant'])
            ){
                $data[] = $value;
            }
        }

        $this->setData([
            'data'=>$data
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
        // if (!$this->accessCheck('c')) {
        //     return $this->done();
        // }
    
        $input = $request->all();
        $input['tenant_id'] = config('tenant.id', 0);
        $input['created_by'] = UserAuth::user('id');

        $getLastConfig = DbConfig::listGlobalConfig('dashboard.config.item', true, true);

        $lastItem = end($getLastConfig);
        $lastId = $lastItem ? $lastItem['id'] : 0;
        $nextId = $lastId + 1;
    
        $setKey = 'item.' . $nextId;

        DbConfig::setGlobalConfig('dashboard.config.item', $setKey, [
            'id' => $nextId,
            'name' => $input['name'],
            'description' => $input['description'],
            'tenant' => $input['tenant'] ? $input['tenant'] : [],
            'template_code' => $input['template_code'],
            'feature' => $input['feature'] ? $input['feature'] : [],
            'content' => $input['content'] ? $input['content'] : [],
        ]);
    
        $this->setData($input)
            ->setMessage(__('alert.create_success', [
                'attribute' => __('coop_master.form_config_dashboard.name')
            ]));
    
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
        // if (!$this->accessCheck('r')) {
        //     return $this->done();
        // }
        
        $this->buildParams();
        $this->output['params']['filter'][] = ['id', $id];

        $data = DbConfig::getGlobalConfig('dashboard.config.item', 'item.'.$id);
        if (!$data) {
            $this->setError(__('lang.data_not_found'));
        } else {
            if(is_string($data)) 
                $data = json_decode($data, true);
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
        // if (!$this->accessCheck('u')) {
        //     return $this->done();
        // }

        $input = $request->all();
        $input['updated_by'] = UserAuth::user('id');

        $key = 'item.' . $id;
        DbConfig::setGlobalConfig('dashboard.config.item', $key, [
            'id' => $id,
            'name' => $input['name'],
            'description' => $input['description'],
            'tenant' => $input['tenant'] ?? [],
            'template_code' => $input['template_code'],
            'feature' => $input['feature'] ?? [],
            'content' => $input['content'] ?? [],
        ]);

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
        // if (!$this->accessCheck('d')) {
        //     return $this->done();
        // }

        $configKey = 'dashboard.config.item';
        $itemKey = 'item.' . $id;

        $data = DbConfig::getGlobalConfig($configKey, $itemKey);
        if ($data) {
            DbConfig::deleteGlobalConfig($configKey, $itemKey);
            $this->setMessage(__('alert.delete_success', [
                'attribute' => __('coop_master.form_config_dashboard.name')
            ]));
        } else {
            $this->setError(__('lang.data_not_found'));
        }

        return $this->done();
    }

    /**
     * get - /api/user/config/dashboard/template
     * get template dashboard
     * 
     * @param Request $request
     */
    public function getTemplateDashboard(Request $request)
    {
        $getTemplate = File::exists(base_path('App/MainApp/config/_dashboard.json')) 
            ? File::get(base_path('App/MainApp/config/_dashboard.json')) 
            : '[]';

        $data = json_decode($getTemplate, true);
        $this->setData($data);

        return $this->done();
    }


}
