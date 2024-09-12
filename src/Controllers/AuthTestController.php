<?php

namespace hpsynapse\moduser\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use hpsynapse\moduser\Facades\UserRepo;
use hpsynapse\moduser\Facades\AuthConfig;
use hpsynapse\moduser\Facades\UserAuth;

use App\Base\BaseController;
use App\Facades\Export;

use hpsynapse\moduser\Facades\ExportUserFormater;

class AuthTestController extends BaseController
{
    protected $accessRuleKey = 'moduser.user.authCheck';

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
        // $this->forceApiOutput();
    }

    public function checkUser(Request $request)
    {
        dd(UserAuth::getActiveUserRoleCode());
    }
}