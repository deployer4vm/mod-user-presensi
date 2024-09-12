<?php

namespace hpsynapse\moduser\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Log;

use App\Base\Traits\ResCacheTrait;

use hpsynapse\moduser\Facades\UserRepo;
use hpsynapse\moduser\Facades\UserLog;
use hpsynapse\moduser\Facades\RoleRepo;

use hpsynapse\moduser\Models\ApiToken;
use hpsynapse\moduser\Models\User;
use hpsynapse\moduser\Models\DataruleFeature;


/**
 * Library untuk akses SSO service
 * 
 * SSOSession structure :
 *      
 */
class UserAuth
{
    use ResCacheTrait;
    protected $isLogin = false;
    protected $isApiCall = false;
    protected $isHost2Host = false;
    protected $token = '';
    protected $clientData; //record user system dari koneksi (H2H nya)
    // 
    protected $userData;
    protected $userRoleList;
    protected $userRole;
    protected $userRoleCode;

    public function setInit($isApiCall = false, $userData = false, $userRole = false)
    {
        $this->cacheActive = true;
        $this->isApiCall = $isApiCall;
        if(Auth::check())
            if ($isApiCall) {
                if (Auth::user()->user_id){
                    if(Auth::user()->session_data){
                        $this->setInitApi();
                    }else{
                        $this->setInitApiFromDb();
                    }      
                    if($this->isDataruleActive())          
                        $this->hasDatarule = $this->userRole[$this->userRoleCode]['datarule']?true:false;
                }
                
            } else {
                $this->setInitWeb();
            }

    }

    private function setInitApi()
    {
        $this->userData = Auth::user()->session_data['user'];
        $this->userRole = Auth::user()->session_data['role'];
        $this->userRoleCode = Auth::user()->active_role_code;
        if($this->userRole)
            $this->userData['level'] = $this->userRole[$this->userRoleCode]['level'];
        $this->token = Auth::user()->api_token;        
    }
    
    private function setInitApiFromDb()
    {
        $this->userData = UserRepo::getUser(Auth::user()->user_id);
        $this->userRole = UserRepo::listUserRole(Auth::user()->user_id);
        $this->userRoleCode = Auth::user()->active_role_code;
        if($this->userRole)
            $this->userData['level'] = $this->userRole[$this->userRoleCode]['level'];
        $this->token = Auth::user()->api_token;        
    }

    private function setInitWeb()
    {
        $this->userData = $this->getUserSessionData();
        $this->userRole = $this->getUserSessionRole();
        $this->userRoleCode = $this->getActiveUserRoleCode();
        if($this->userRole)
            $this->userData['level'] = $this->userRole[$this->userRoleCode]['level'];
        $this->token = $this->getSesionToken();   
        
        $this->hasDatarule = $this->userRole[$this->userRoleCode]['datarule']?true:false;     
    }

    /**
     * generate current timestamps
     * 
     * @return type
     */
    public function getCurTimeStamp()
    {
        $now = now();
        $data['lastUpdate'] = $now->toDateTimeString();
        $data['validUntil'] = $now->addMinutes(config('session.lifetime'))->toDateTimeString();
        return $data;
    }

    public function getToken($field = false)
    {
        return $this->token;
    }

    /**
     * KHUSUS WEB ACCESS
     */
    public function getSesionToken()
    {
        return session('APPSSession.token');
    }

    /**
     * KHUSUS WEB ACCESS
     */
    public function getUserSessionData($field = false)
    {
        if ($field && session('APPSSession.user.' . $field)) {
            $data = session('APPSSession.user.' . $field);
        } else {
            $data = session('APPSSession.user');
        }
        return $data;
    }

    /**
     * KHUSUS WEB ACCESS
     */
    public function getUserSessionRole($field = false)
    {
        if ($field && session('APPSSession.role.' . $field)) {
            $data = session('APPSSession.role.' . $field);
        } else {
            $data = session('APPSSession.role');
        }
        return $data;
    }

    /**
     * KHUSUS WEB ACCESS
     */
    public function getActiveUserRoleCode()
    {
        if($this->isApiCall)
            return Auth::user()->active_role_code;
        return session('APPSSession.role_code');
    }

    public function getSessionLastUpdate()
    {
        if($this->isApiCall)
            return Auth::user()->updated_at;
        return session('APPSSession.lastUpdate');
    }

    /**
     * KHUSUS WEB ACCESS
     */
    public function getSessionValidUntil()
    {
        return session('APPSSession.validUntil');
    }

    /**
     * set session saat login
     * 
     * @param type $userId
     */
    public function setUser($userId, $apiTokenData = false)
    {
        $sessionData = $this->getCurTimeStamp();
        $userData = UserRepo::getUser($userId);
        $role = UserRepo::listUserRole($userData['id']);

        foreach ($role as $key => $val) {
            $roleCode = $key;
            if ($val['is_main_role']) {
                $roleCode = $key;
                break;
            }
        }

        if (!$apiTokenData) {
            $apiTokenData = UserRepo::generateToken($userId, $roleCode);
            $apiTokenData = $apiTokenData['api_token'];
        }

        if($role)
            $userData['level'] = $role[$roleCode]['level'];
        
        $this->setSession([
            'user' => $userData,//record user
            'role' => $role,//record role
            'role_code' => $roleCode,//string
            //
            'token' => $apiTokenData,
            'lastUpdate' => $sessionData['lastUpdate'],
            'validUntil' => $sessionData['validUntil']
        ]);
    }

    /**
     * ganti role user yang sedang login
     */
    public function setActiveRole($roleCode)
    {
        if ($this->role($roleCode)) {
            if ($this->isApiCall) {
                Auth::user()->update(['active_role_code' => $roleCode]);
            } else {
                $this->setSession([
                    'role_code' => $roleCode
                ]);
            }
            return true;
        }
        return false;
    }

    /**
     * KHUSUS WEB ACCESS
     * unset session saat logout
     * 
     * @param type $userId
     */
    public function unsetUser()
    {
        $sessionData = $this->getCurTimeStamp();
        $this->setSession([
            'user' => '',
            'role' => '',
            'role_code' => '',
            //
            'token' => '',
            'lastUpdate' => $sessionData['lastUpdate'],
            'validUntil' => $sessionData['validUntil'],
        ]);
    }

    /**
     * KHUSUS WEB ACCESS
     * set session saat pertama kali dapet dari account center
     * @param array $apiData variable session SSO yg akan diubah
     */
    public function setSession(array $data)
    {
        if(!$this->isApiCall)return false;

        foreach ($data as $key => $value) {
            session()->put('APPSSession.' . $key, $value);
        }
        session()->save();
    }

    /**
     * =========================================================================
     */

    public function logoutAllExeptMe()
    {
        //delete semua token kecuali yg loign
        if ($this->getToken()){
            ApiToken::where('api_token', '!=', $this->getToken())
                ->where('is_permanent',0)->delete();
        }
    }

    public function lockLoginExeptMe()
    {
        $config = [
            'allow_login' => 0,
            'allow_login_exept' => [],
            'allow_login_only' => [$this->user('id')]
        ];
        $this->_saveCache('generalconfig', 'accesss', $config);
    }

    public function unlockLogin()
    {
        $config = [
            'allow_login' => 1,
            'allow_login_exept' => [],
            'allow_login_only' => []
        ];
        $this->_saveCache('generalconfig', 'accesss', $config);
    }

    public function getAccessConfig()
    {
        if (!($config = $this->_getCache('generalconfig', 'accesss'))) {
            $config = [
                'allow_login' => 1,
                'allow_login_exept' => [],
                'allow_login_only' => []
            ];
            $this->_saveCache('generalconfig', 'accesss', $config);
        }
        return $config;
    }

    /**
     * =========================================================================
     */

    public function isLogin()
    {
        return Auth::check() && is_array($this->userData);
    }

    /**
     * GET data user dari Account Center
     * @param type $field
     * @return type
     */
    public function user($field = false, $default = false)
    {
        if ($field == false) return $this->userData;
        return isset($this->userData[$field]) ? $this->userData[$field] : $default;
    }

    /**
     * GET data list role user yang sedang login
     * @param type $field
     * @return type
     */
    public function role($field = false, $default = false)
    {
        if ($field == false) return $this->userRole;
        return isset($this->userRole[$field]) ? $this->userRole[$field] : $default;
    }

    public function isPhoneVerified()
    {
        if (isset($this->userData['phone_verified_at']) && $this->userData['phone_verified_at']) {
            return true;
        }
        return false;
    }

    public function isEmailVerified()
    {
        if (isset($this->userData['email_verified_at']) && $this->userData['email_verified_at']) {
            return true;
        }
        return false;
    }

    /**
     * cek status user apakah posisi tidak aktif / banned
     * 
     * @return boolean
     */
    public function isBanned()
    {
        if ($this->userData['status'] == 0) {
            return true;
        }
        return false;
    }

    /**
     * @param String $pin unencrypted pin
     */
    public function isPinValid($pin,$userId=false)
    {
        $tmpUserId = $userId;
        $userId = $userId?$userId:$this->userData['id'];
        $tmpUser = User::select(['pin','id'])->where('id',$userId)->first();
        
        if(Hash::check($pin,$tmpUser->pin)){
            return true;
        }
        UserLog::addLog(UserAuth::user('id'),'USERAUTH','PIN_INVALID',[
            'pin'=>$pin,
            'hashed_pin'=>$tmpUser->pin,
            'is_h2h_token'=>$this->isH2H(),
            'userId'=>$tmpUserId,
            'userData'=>$this->userData,
            'userPin'=>$tmpUser?$tmpUser->toArray():[],
        ]);
        return false;
    }

    /**
     * ROLE CHECK
     * =========================================================================
     */

    /**
     * cek apakah user yang online memiliki akses role "rolde_code"
     * 
     * @param type $roleCode
     * @return boolean
     */
    public function hasRole($roleCode)
    {
        if (isset($this->userData['role']) && strpos($this->userData['role'], ';' . $roleCode . ';') !== false) {
            return true;
        }
        return false;
    }

    /**
     * cek apakah user yang online adalah sebagai "role_code"
     * 
     * @param type $roleCode
     * @return boolean
     */
    public function is($roleCode)
    {
        if (!empty($this->userRoleCode) && $this->userRoleCode == $roleCode) {
            return true;
        }
        return false;
    }

    /**
     * cek apakah user adalah webdev
     *
     * @return boolean
     */
    public function isWebDev()
    {
        return $this->isLogin() && $this->is('webdev');
    }

    public function hasAccess(
        $key,
        $subKey = 'has_access',
        $defaultAccess = true,
        $checkWebDev = false
    ) {
        if ($checkWebDev === true && $this->isWebDev()) {
            return true;
        }
        if (isset($this->userRole[$this->userRoleCode])) {
            if (isset($this->userRole[$this->userRoleCode]['rule'][$key][$subKey])) {
                return $this->userRole[$this->userRoleCode]['rule'][$key][$subKey] == 1 ? true : false;
            }
        }
        return $defaultAccess;
    }

    /**
     * cek apakah user yang login memiliki grant access ke role_code
     * 
     * @param string $roleCode role_code yang dicek nya
     */
    public function isGranted($roleCode = false)
    {
        $data = UserRepo::listUserRole($this->userData['id']);

        if (!is_array($roleCode)) $roleCode = [$roleCode];
        foreach ($data as $role) {
            if (in_array($role['role_code'], $roleCode) && $role['has_auth_grant'] == 1)
                return true;
        }
        return false;
    }

    /**
     * DATARULE CHECK
     * =========================================================================
     */

    protected $tmpDatarule = [];
    protected $tmpDataruleFeature = [];
    protected $hasDatarule = false;

    /**
     * 
     * @return False|Array False jika datarule tidak aktif atau jika all access
     *
     *      datarule_type
     *      datarule_subtype
     *      datarule_type_value
     * 
     *      feature
     *      feature_config isi config fitur
     * 
     *      filter
     */
    public function initDatarule($code,$defaultData=[],$autoCreate=false)
    {
        $return = false;
        if($this->isDataruleActive() && $this->getDataruleType()){
            $return = $this->userRole[$this->userRoleCode]['datarule_active'];
            
            $return['feature'] = $this->getDataruleFeature($code,$defaultData,$autoCreate);

            // jika ada datarule tp selain all access dan user login
            if($this->getDataruleType()==1){
                $return['feature_config'] = 
                    isset($return['feature']['config'][1])
                    ?$return['feature']['config'][1]
                    :'';
                // $return['filter'] = [
                //     $return['feature']['config']
                // ];

            // jika
            }else{
                $return['feature_config']=
                    isset($return['feature']['config'][$this->datarule()['type']]) && isset($return['feature']['config'][$this->datarule()['type']][$this->datarule()['subtype']])
                    ?$return['feature']['config'][$this->datarule()['type']][$this->datarule()['subtype']]
                    :'';

                // $return['filter'] = [
                //     $return['feature']['config']
                // ];
            }
        }

        return $return;
    }

    /**
     * do filter datarule
     */
    public function filterDatarule($model,$activeDataRule)
    {        
        // custom
        if($activeDataRule['datarule_type']==3){
            $model = $this->filterDataruleCustom($model,$activeDataRule);
        // user id / user login
        }else if($activeDataRule['datarule_type']==1 || ($activeDataRule['datarule_type']==2 && $activeDataRule['datarule_subtype']==1)){
            $model = $model->addGlobalScope(
                new \hpsynapse\moduser\Models\Scopes\DataruleUser(
                    $activeDataRule['datarule_type_value'],
                    $activeDataRule['feature_config']?$activeDataRule['feature_config']:false
                )
            );
        // jika tipe 2 (moduser) lainnya selain yg subtype 1
        }else if($activeDataRule['datarule_type']==2){
            $model = $this->filterDataruleUser($model,$activeDataRule);
        }

        return $model;
    }

    private function filterDataruleUser($model,$activeDataRule)
    {
        // berdasarkan user group id
        if($activeDataRule['datarule_subtype']==2){
            $model = $model->addGlobalScope(
                new \hpsynapse\moduser\Models\Scopes\DataruleUserGroup(
                    $activeDataRule['datarule_type_value'],
                    $activeDataRule['feature_config']?$activeDataRule['feature_config']:false
                )
            );
        // berdasarkan role id
        }else if($activeDataRule['datarule_subtype']==3){
            $model = $model->addGlobalScope(
                new \hpsynapse\moduser\Models\Scopes\DataruleRole(
                    $activeDataRule['datarule_type_value'],
                    $activeDataRule['feature_config']?$activeDataRule['feature_config']:false
                )
            );
        // berdasarkan role group id
        }else if($activeDataRule['datarule_subtype']==4){
            $model = $model->addGlobalScope(
                new \hpsynapse\moduser\Models\Scopes\DataruleRoleGroup(
                    $activeDataRule['datarule_type_value'],
                    $activeDataRule['feature_config']?$activeDataRule['feature_config']:false
                )
            );
        }

        return $model;
    }
    
    private function filterDataruleCustom($model,$activeDataRule)
    {
        $scopeClass = config('AppConfig.packageLocal.moduser.datarule.custom.'.$activeDataRule['datarule_subtype'].'.scope');
        $model = $model->addGlobalScope(
            new $scopeClass(
                $activeDataRule['datarule_type_value'],
                $activeDataRule['feature_config']?$activeDataRule['feature_config']:false
            )
        );
        return $model;
    }

    /**
     * get record feature
     * @return False|Array
     */
    public function getDataruleFeature($code,$defaultData=[],$autoCreate=false)
    {
        if(isset($this->tmpDataruleFeature[$code]))
            return $this->tmpDataruleFeature[$code];

        $tmpFeature = DataruleFeature::where('code',$code)->first();
        if($tmpFeature){
            $this->tmpDataruleFeature[$code] = $tmpFeature->toArray();
        }else{
            if($autoCreate){
                $defaultData['code'] = $code;
                $defaultData['tenant_id'] = config('tenant.id',0);
                DataruleFeature::create($defaultData);
                $this->tmpDataruleFeature[$code] = DataruleFeature::where('code',$code)->first()->toArray();
            }else{
                return false;
            }
        }

        return $this->tmpDataruleFeature[$code];
    }

    public function isDataruleActive()
    {
        return config('AppConfig.packageLocal.moduser.datarule.enable',0)?true:false;
    }

    public function datarule()
    {
        return $this->userRole[$this->userRoleCode]['datarule'];
    }

    public function getDataruleType()
    {
        return $this->hasDatarule?$this->datarule()['type']:config('AppConfig.packageLocal.moduser.datarule.default_datarule_type', 0);
    }

    /**
     * USER SYSTEM
     * =========================================================================
     */

    public function setClient($clientData)
    {
        $this->clientData = $clientData;
    }

    public function getClient()
    {
        return $this->clientData;
    }

    public function setH2H()
    {
        $this->isHost2Host = true;
    }

    public function isH2H()
    {
        return $this->isHost2Host;
    }

    private function getEncrypter($customKey = false)
    {
        $clientData = $this->getClient();
        $customKey = $customKey ?: $clientData['secret_key'];
        return new Encrypter(base64_decode($customKey), 'aes-256-cbc');
    }

    public function encryptCredential($baseString, $customKey = false)
    {
        $encrypter = $this->getEncrypter($customKey);

        try {
            $encrypted = $encrypter->encryptString($baseString);
        } catch (\Throwable $th) {
            //throw $th;
            return false;
        }

        return $encrypted;
    }

    public function decryptCredential($encrypted, $customKey = false)
    {
        $encrypter = $this->getEncrypter($customKey);

        try {
            $decrypted = $encrypter->decryptString($encrypted);
        } catch (\Throwable $th) {
            //throw $th;
            return false;
        }

        return $decrypted;
    }
}
