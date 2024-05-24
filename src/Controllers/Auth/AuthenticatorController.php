<?php

namespace hpsynapse\moduser\Controllers\Auth;

// 1. Import level PHP

// 2. Import level Package Composer

// 3. Import level Laravel Core
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

// 4. Import level Synapse Core
use App\Base\BaseController;

// 5. Import level Synapse Module Package
use hpsynapse\moduser\Facades\UserAuth;
use hpsynapse\moduser\Facades\AuthConfig;
use hpsynapse\moduser\Facades\Authenticator;

// 6. Import level Synapse MainApp & Module MainApp

// 7. Import level Synapse - Current Module
use hpsynapse\modscsalestore\Facades\Store;
use hpsynapse\modscsalestore\Facades\Cashier;

/**
 * @SuppressWarnings(PHPMD.ShortMethodNames)
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class AuthenticatorController extends BaseController
{
    
    /**
     * GET - /api/user/authenticator-request
     * 
     * get list client yang melakukan request verifikasi, berdasarkan user yg diminta autorisasi
     */
    public function authRequestGet(Request $request)
    {
        if(!AuthConfig::isAuthenticatorEnabled()){
            $this->setError('Authenticator Disabled'); 
            return $this->done();
        }

        if($data = Authenticator::listActiveGrantRequest(UserAuth::user('id'))){
            $this->setData($data);//->setMessage('List', 'success');
        }else{
            $this->setError(Authenticator::error());
        }
        return $this->done();
    }
    
    /**
     * PUT/POST - /api/user/authenticator-request
     * 
     * create auth request
     * 
     * @param \Illuminate\Http\Request $request
     *      user_id         *optional, id user yg dimintai grant/authorisasi
     *      feature_code
     *      description     *optional
     *      expired_time     *optional
     * @return synapse return
     *      
     */
    public function authRequestCreate(Request $request)
    {
        if (!AuthConfig::isAuthenticatorEnabled()) {
            $this->setError('Authenticator Disabled'); 
            return $this->done();
        }

        $input = $request->only([
            'user_id',
            'request_user_id',
            'grant_user_id',
            'feature_code',
            'description',
            'expired_time'
        ]);

        if (!isset($input['user_id'])) $input['user_id'] = UserAuth::user('id');

        if ($data = Authenticator::createAuthRequest($input)){
            $this->setData($data)->setMessage('Auth Request berhasil dibuat', 'success');
        } else {
            $this->setError(Authenticator::error());
        }
        
        return $this->done();
    }

    /**
     * POST/PUT - /api/user/authenticator-grant/{featureCode}/{requestCode}
     * 
     * grant access
     * 
     * @param \Illuminate\Http\Request $request
     * @param  $featureCode
     * @param  $requestCode
     * 
     */
    public function authRequestGrant(Request $request, $featureCode, $requestCode)
    {
        if(!AuthConfig::isAuthenticatorEnabled()){
            $this->setError('Authenticator Disabled'); 
            return $this->done();
        }

        if(Authenticator::grantAuthRequest($featureCode,$requestCode,[
            'user_id'=>UserAuth::user('id'),
            'auth_code'=>$request->input('auth_code')
        ])){
            $this->setMessage('Auth Request Granted', 'success');
        }else{
            $this->setError(Authenticator::error());            
        }

        return $this->done();
    }

    
    /**
     * DELETE - /api/user/authenticator-request/{featureCode}/{requestCode}
     * 
     * grant access
     * 
     * @param \Illuminate\Http\Request $request
     *      code
     * @param  $featureCode
     * @param  $requestCode
     * 
     */
    public function authRequestReject(Request $request, $featureCode, $requestCode)
    {
        if(!AuthConfig::isAuthenticatorEnabled()){
            $this->setError('Authenticator Disabled'); 
            return $this->done();
        }

        if(Authenticator::rejectAuthRequest($featureCode,$requestCode,['user_id'=>UserAuth::user('id')])){
            $this->setMessage('Auth Request Rejected', 'success');
        }else{
            $this->setError(Authenticator::error());            
        }

        return $this->done();
    }

    
    /**
     * GET - /api/user/authenticator-request/{featureCode}/{requestCode}
     * 
     * verifikasi request
     * 
     * @param \Illuminate\Http\Request $request
     * @param  $featureCode
     * @param  $requestCode
     * 
     * @return
     */
    public function authGrantVerify(Request $request, $featureCode, $requestCode)
    {
        if(!AuthConfig::isAuthenticatorEnabled()){
            $this->setError('Authenticator Disabled'); 
            return $this->done();
        }

        if(($status = Authenticator::verifyRequest($featureCode,$requestCode)) !== false){
            $this->setData([
                'status' => $status,
            ])->setMessage('Auth Request Found', 'success');
        }else{
            $this->setError(Authenticator::error());            
        }

        return $this->done();
    }
}