<?php

namespace hpsynapse\moduser\Controllers\config;

use Illuminate\Http\Request;

use hpsynapse\moduser\Facades\UserLog;
use hpsynapse\moduser\Facades\UserAuth;

use App\Base\BaseController;
use App\Facades\DbConfig;
use App\Facades\Tenant;
use hpsynapse\modcoop\Facades\Master\Config;

class DataMasterController extends BaseController
{
    public function __construct()
    {
        $this->forceApiOutput();
    }

    /**
     * GET - /api/user/config/tenant
     * 
     * list tenant
     *
     * @param \Illuminate\Http\Request $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function tenantList(Request $request)
    {
        // if (!$this->accessCheck('r')) {
        //     return $this->done();
        // }

        $this->buildParams();
        $this->output['params']['query']['limit'] = $request->input('limit', 0);
        $this->output['params']['filter'][] = [
            'status',1
        ];
        
        $this->setData(Tenant::listTenant(
            $this->output['params']['filter'],
            $this->output['params']['query']['offset'],
            $this->output['params']['query']['limit'],
            $this->output['params']['orderBy'],
        ));

        return $this->done();
    }
}