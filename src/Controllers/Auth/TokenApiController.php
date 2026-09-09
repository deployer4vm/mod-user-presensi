<?php

namespace hpsynapse\moduser\Controllers\Auth;

use hpsynapse\moduser\Facades\UserNotifRepo;

use hpsynapse\moduser\Facades\UserRepo;
use hpsynapse\moduser\Facades\RoleRepo;
use hpsynapse\moduser\Facades\UserAuth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Base\BaseController;

class TokenApiController extends BaseController
{
    
    /**
     * API OUTPUT ONLY
     * /api/auth/token/validate
     * 
     * validate token and get user data
     * 
     * @param Request $request
     */
    public function validateToken(Request $request)
    {
        $apiToken = UserRepo::getToken(UserAuth::getToken());
        if($apiToken==false){
            $this->setError(__('lang.data_attribute_not_found',['attribute'=>'Token']));
            return $this->done();
        }
                                
        $this->output['message'] = 'Token Valid';
        $this->output['data'] = UserAuth::getCurTimeStamp();
        
        $this->output['data']['user'] = UserRepo::getUser($apiToken['user_id']);
        $this->output['data']['role'] = UserRepo::listUserRole($apiToken['user_id']);
        foreach ($this->output['data']['role'] as $key => $val) {
            if ($val['is_main_role']) {
                $this->output['data']['role_code'] = $key;
            }
        }
        $this->output['data']['token'] = $apiToken['api_token'];
        $notifToken = $request->input('pushNotifToken',false);
        //jika menyertakan update token notif
        if($notifToken){
            //jika token berubah maka subscribe ulang
            if($apiToken['push_token'] = $notifToken){
                $notifChannel[] = 'all';
                // if(isset($response['data']['role'][9]))$notifChannel[] = 'member';         
                
                UserNotifRepo::subscribeToChannel($notifChannel,$notifToken);
                UserNotifRepo::unsubscribeFromChannel($apiToken['push_token'],$notifToken);
                
                UserNotifRepo::changePushToken($apiToken['push_token'],$notifToken);
            }
        }
        
        UserRepo::setSingleTokenLastUpdate($apiToken['api_token'],$this->output['data']['lastUpdate']);
                
        return $this->done();
    }
    
    
}
