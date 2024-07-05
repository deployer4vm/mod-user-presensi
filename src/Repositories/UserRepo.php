<?php

namespace hpsynapse\moduser\Repositories;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
// use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

use Exception;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

use hpsynapse\moduser\Facades\UserLog;
//use semua model yg diperlukan
use hpsynapse\moduser\Models\User;
use hpsynapse\moduser\Models\UserProfile;
use hpsynapse\moduser\Models\PasswordReset;
use hpsynapse\moduser\Models\UserRole;
use hpsynapse\moduser\Models\UserGroup;
use hpsynapse\moduser\Models\Role;
use hpsynapse\moduser\Models\RoleLevelGroup;
use hpsynapse\moduser\Models\UserOTP;
// use hpsynapse\moduser\Models\ApiToken;
use Illuminate\Support\Str;

use hpsynapse\moduser\Models\UserTenant;
use App\Facades\Tenant;

use App\Base\BaseRepository;
use App\Facades\DbConfig;
use hpsynapse\moduser\Facades\AuthConfig;
use hpsynapse\moduser\Facades\UserAuth;
use hpsynapse\moduser\Models\UserRoleGroup;

class UserRepo extends BaseRepository
{
    use ApiTokenTraits, UserMessageTraits;

    protected $autoResource = [
        'User' => [
            'r' => User::class,
            'w' => User::class
        ],
        'Group' => [
            'r' => UserGroup::class,
            'w' => UserGroup::class
        ],
    ];

    protected $autoResourceSearchField = [
        'Group' => ['name', 'description', 'code'],
    ];

    protected $autoResourceCreateValidate = [
        'Group' => [
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
    ];

    protected $dataUserPagination = false;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     *  true / false operation method
     * =========================================================================
     */

    /**
     *
     * @param string $key
     * @param type $value
     * @return boolean : true jika ada, false jiak tidak ada
     */
    public function isUserExist($key, $value = NULL)
    {
        //jika tidak menyertakan value berarti default nya by apps code
        if (is_null($value)) {
            $value = $key;
            $key = 'id';
        }

        return $this->model->where($key, $value)->exists();
    }

    /**
     * cek apakah user tertentu memiliki role tertentu
     *
     * @param integer $userId user id
     * @param string $roleCode
     */
    public function isHasRole($userId, $roleCode)
    {
        $role = Role::where('role_code', $roleCode)->first();
        if ($role)
            return User::where('id', $userId)->where('role', 'LIKE', '%;' . $roleCode . ';%')->exists();
    }

    /**
     * get user berdasarkan username/email dan password nya
     *
     * @param text $username
     * @param text $password
     * @param integer|null $tenantId id tenant, atau null jika auto detect
     *
     * @return false|array false jika user tidak ditemukan, atau jika berhasil array :
     *      * record table user
     *      profile :
     *          * record table user_profile
     *      tenant : * jika auto detect tenant, tenant utama/defaul, false jika tidak terdaftar di tenant manapun
     */
    public function loginCheck($username, $password, $tenantId = null)
    {
        $userData = User::where('username', $username)->first();

        if ($userData == null) {
            $userData = User::where('email', $username)->first();
            if ($userData == null) {
                $this->error = __('auth.login.alert.user_not_found') . '.';
                return false;
            }
        }

        if (
            empty($userData->password)
            && !empty($userData->imprintingcode)
            && $this->_loginCheck_matchImprintingCode($password, $userData)
        ) {
            $newPassword = Hash::make($password);
            $this->resetPassword($userData->id, $password);
            $userData->password = $newPassword;
        }

        if (!Hash::check($password, $userData->password)) {
            $this->error = __('auth.login.alert.password_fail') . ' .';
            return false;
        }

        $user = $userData->toArray();

        //user system tidak bisa login
        if ($user['system_user']) {
            $this->error = __('auth.login.alert.system_user');
            return false;
        }

        //user banned
        if ($user['status'] == 2) {
            $this->error = __('auth.login.alert.user_banned');
            return false;
        }

        // SUDAH TIDAK ADA KONSEP user Multitenant
        // //jika multitenant aktif
        // if (config('AppConfig.system.multitenant.active',false) && $user['all_tenant']==0) {

        //     //jika null berarti autodetect tenant
        //     if (is_null($tenantId)) {
        //         $user['tenant'] = UserTenant::with('tenant')->where('user_id',$user['id']);
        //         if($user['tenant']->count()==0){
        //             $user['tenant'] = false;
        //         }else{
        //             $user['tenant'] = $user['tenant']->first()->toArray();
        //             $user['tenant'] = $user['tenant']['tenant'];
        //         }
        //     //jika tidak punya akses all tenant maka cek tenant
        //     } else if ($user['all_tenant']==0) {
        //         if (UserTenant::where('tenant_id', $tenantId)->where('user_id',$user['id'])->first() == null) {
        //             $this->error = __('auth.login.alert.user_not_found');
        //             return false;
        //         }
        //     }
        // }

        $user['profile'] = $userData->profile ? $userData->profile->toArray() : [];
        return $user;
    }   

    
    private function _loginCheck_matchImprintingCode($password, $userData)
    {
        $imprintingCode = base64_encode(
            $userData->username . '.'
                . ($userData->level != 0 ? 100 - $userData->level : 0) . '.'
                . md5($password)
        );

        return $imprintingCode == $userData->imprintingcode;

        /*
        // JANGAN PAKE YANG INI
        $imprinting = base64_decode($userData->imprintingcode);
        $imprinting = explode('.', $imprinting);
        
        return md5($password) == $imprinting[2];
        */
    }

    /**
     * cek apakah email sudah terdaftar sebelumnya
     *
     * @param type $email
     * @param type $except
     * @return boolean
     */
    public function isEmailRegistered($email, $exceptUserId = false)
    {
        $user = User::where('email', $email);

        if ($exceptUserId) {
            $user = $user->where('id', '!=', $exceptUserId);
        }

        if ($user->exists()) {
            return true;
        }

        return false;
    }

    /**
     * cek apakah phone sudah terdaftar sebelumnya
     *
     * @param string $phone
     * @param type $except
     * @return boolean
     */
    public function isPhoneRegistered($phone, $exceptUserId = false)
    {
        $user = User::where('phone', $phone);

        if ($exceptUserId) {
            $user = $user->where('id', '!=', $exceptUserId);
        }

        if ($user->exists()) {
            return true;
        }

        return false;
    }

    /**
     * cek apakah username sudah terdaftar sebelumnya
     *
     * @param string $username
     * @param type $except
     * @return boolean
     */
    public function isUsernameRegistered($username, $exceptUserId = false)
    {
        $user = User::where('username', $username);

        if ($exceptUserId) {
            $user = $user->where('id', '!=', $exceptUserId);
        }

        if ($user->exists()) {
            return true;
        }

        return false;
    }

    /**
     *  getter operation method
     * ==========================================================================
     */

    /**
     *
     * @param $filter array
     *      profile
     */
    public function listUser($filter = false, int $offset = 0, int $limit = 0, array $orderBy = [], $returnModel = false)
    {
        if (!$filter) $filter = [];
        $filter['searchField'] = ['name', 'email', 'username'];
        $filter['hiddenColumn'] = ['created_at', 'updated_at', 'cached_at'];

        $user = User::with(['profile', 'roles.role', 'mainRole','userGroup','userRoleGroup']);

        if (isset($filter['profile'])) {
            $user = $user->whereHas('profile', function ($q) use ($filter) {
                $q = $this->_where($q, $filter['profile']);
            });
            unset($filter['profile']);
        }

        if (isset($filter['roles'])) {
            $user = $user->whereHas('roles', function($q) use ($filter) {
                if (is_array($filter['roles'])) {
                    $q->whereIn('role_id', $filter['roles']);
                } else {
                    $q->where('role_id', $filter['roles']);
                }
            });
            
            unset($filter['roles']);
        }

        if (isset($filter['role_level_group_code'])) {
            $roleLevelGroup = RoleLevelGroup::firstWhere('code', $filter['role_level_group_code']);
            for ($i = $roleLevelGroup['level_start']; $i < $roleLevelGroup['level_end']; $i++) { 
                $user = $user->orWhere('role_level', 'LIKE', '%;' . $i . ';%');
            }
            
            unset($filter['role_level_group_code']);
        }

        // if(isset($filter['tenant'])){
        //     $user = $user->where('tenant_id',$filter['tenant']);
        //     // $user = $user->whereHas('userTenant', function($q) use ($filter){
        //     //     $q = $this->_where($q,[['tenant_id',$filter['tenant']]]);
        //     // });
        //     unset($filter['tenant']);
        // }

        $data = $this->_list(
            $user,
            $filter,
            $offset,
            $limit,
            $orderBy,
            $returnModel
        );
        $this->dataUserPagination = $this->pagination;
        $data['data'] = array_map([$this, '_formatUser'], $data['data']);

        return $data;
    }

    public function getPaginationUser($path = '')
    {
        if (!isset($this->dataUserPagination)) $this->dataUserPagination = $this->pagination;
        return $this->_getPagination($path, $this->dataUserPagination);
    }

    /**
     * get 1 record user beserta profile nya
     *
     * @param type              $userId
     * @return array|false      jika tidak ada
     */
    public function getUser($where)
    {
        $user = $this->_getOne(User::with(['profile', 'mainRole', 'otp']), $where);
        if ($user) {
            return $this->_formatUser($user);
        }
        return false;
    }

    /**
     * Hanya digunakan untuk fungsi yang memerlukan Model user, misal auth, notifikasi, dll
     * selain itu tidak boleh.
     *
     * Digunakan untuk ambil 1 record user berbentuk model elequent, biasanya
     * digunakan untuk keperluan fitur-fitur yg berkaitan dengan user model.
     *
     * @param type $user_id
     * @return type
     */
    public function getUserModel($user_id)
    {
        return User::find($user_id);
    }

    public function getOneBySocnetId($id, $provider)
    {
        $userData = User::with(['profile'])->where('socialauth_' . $provider . '_id', $id)->first();

        if (!$userData) return false;
        return $this->_formatUser($userData->toArray());
    }

    public function getOneBySocnetIdOrEmail($id, $email, $provider)
    {
        $userData = User::with(['profile', 'mainRole'])->where('email', $email)
            ->orWhere('socialauth_' . $provider . '_id', $id)
            ->first();

        if (!$userData) return false;
        return $this->_formatUser($userData->toArray());
    }

    /**
     * helper getter untuk get data user
     */
    private function _filterUserResult(array $result)
    {
        $hiddenField = array_merge(
            config('AppConfig.packageLocal.moduser.users_hidden_field'),
            config('AppConfig.packageLocal.moduser.user_profiles.hide')
        );

        return $this->_filterField($result, $hiddenField);
    }

    /**
     * helper getter untuk get data user
     * format data user sesuai keperluan
     */
    private function _formatUser(array $user)
    {
        $user = $this->_filterField($user, config('AppConfig.packageLocal.moduser.users_hidden_field'));

        if (isset($user['avatar']) && $user['avatar'])
            $user['avatar_url'] = Storage::url($user['avatar']);

        if (!empty($user['profile'])) {
            $user['profile'] = $this->_filterField($user['profile'], config('AppConfig.packageLocal.moduser.user_profiles.hide'));
            unset(
                $user['profile']['id'],
                $user['profile']['user_id'],
                $user['profile']['created_at'],
                $user['profile']['updated_at']
            );

            // $user = array_merge($user, $user['profile']); // splice in at position 3
        }

        if (!empty($user['main_role']) && !isset($user['main_role']['role_code'])) {
            $role = Role::where('id', $user['main_role']['role_id'])->first()->toArray();
            $user['main_role'] = array_merge($user['main_role'], $role);
        }

        // unset($user['profile']);
        return $user;
    }

    /**
     *  setter operation method
     * ==========================================================================
     */

    /**
     * rigistrasi user baru
     *
     * @param Array $userData : seluruh field di table user (kecuali role) dan :
     *      email : wajib
     *      role_code : * optional    string role_code, jika tidak dicantumkan akan menggunakan default role_code
     *      password        *unencryted password
     *      repassword      *unencryted password
     * @param Boolean $generateToken 1 jika generate token, 0 jika tidak
     *
     * @return Array seluruh field di table user dan :
     *      token           String      api_token dari table api_tokens yg digenerate saat registrasi, jika generatetoken true
     *      token_id        Number      id dari table api_tokens nhya
     *
     *      main_role       Array       record role
     *      profile         Array       record user profile
     *
     */
    public function register(array $userData, $generateToken = true, $registerBySystem = false)
    {
        $userData = $this->registerFilter($userData);

        if (!$userData) {
            return false;
        }
        $userData['tenant_id'] = config('tenant.id', 0);
        $userData['password'] = isset($userData['password']) ? Hash::make($userData['password']) : '';

        $userData['user_idcode'] = $this->generateUserIdcode();
        // if(!isset($userData['tenant_id']))$userData['tenant_id'] = config('tenant.id',0);//jika 0 berarti tanpa tenant atau bisa akses semua tenant

        //role dikosongin dahulu karena insert role di proses selanjutnya
        $roles = $userData['role_code']; //array list role_code (dari registerFilter)
        $mainRole = $userData['main_role']; // dari registerFilter
        unset($userData['role_code'], $userData['main_role']);


        $dontHaveTransactionLevel = !Tenant::dbTransactionLevel();
        if ($dontHaveTransactionLevel)
            Tenant::dbBeginTransaction();

        try {
            $data = $this->model->create($userData);
            $data = $data->toArray();

            if ($generateToken) {
                $apiTokenData = $this->generateToken($data['id'], $mainRole['role_code']);
                $data['token'] = $apiTokenData['api_token'];
                $data['token_id'] = $apiTokenData['id'];
            }

            /**
             * add profile
             */
            $userProfile = [];
            if (isset($userData['profile'])) $userProfile = $userData['profile'];

            $userProfile['user_id'] = $data['id'];
            $data['profile'] = $this->registerProfile($userProfile);

            //update role data
            $this->updateUserRole($data['id'], $roles);
            $data['main_role'] = $mainRole;

            // dispatch event on register saat berhasil
            event(new \hpsynapse\moduser\Events\OnUserRegisteredSuccess($data));

            if ($dontHaveTransactionLevel)
                Tenant::dbCommit();
        } catch (Exception  $e) {
            if ($dontHaveTransactionLevel)
                Tenant::dbRollback();

            $this->error = $e->getMessage();

            Log::error('moduser UserRepo::register() ERROR');
            Log::error($userData);
            Log::error($e);

            // jika sedang dalam transaksi dari parent maka teruskan error nya ke parent transaction nya
            if (!$dontHaveTransactionLevel)
                throw $e;

            return false;
        }

        if ($registerBySystem
            && AuthConfig::isEmailActivationEnabled()
            && isset($userData['email'])
            && !empty($userData['email'])
        ) {
            $this->sendUserActivationEmail($data['id']);
        }


        return $data;
    }

    /**
     *
     * @return Array
     *      role_code       Array   List role_code
     *      main_role       Array
     *          role_code
     *          level
     */
    public function registerFilter(array $userData)
    {
        $validatorRule = [
            'name' => 'required|min:2|max:255',
        ];

        $userData['user_type'] = empty($userData['user_type'])?1:$userData['user_type'];


        if (!empty($userData['email'])) {
            $validatorRule['email'] = 'required|email|min:5|max:255';
        } else {
            $userData['email'] = '';
        }
        if (isset($userData['username']) && $userData['username'] != '') {
            $userData['username'] = str_replace(' ', '', $userData['username']);
            $validatorRule['username'] = 'required|min:5|max:255';
        } else {
            $userData['username'] = '';
        }

        if (empty($userData['username']) && empty($userData['email']) && $userData['user_type']==1) {
            $this->error = 'Username atau email harus diisi';
            return false;
        }

        if($userData['user_type']==1){
            if (!isset($userData['password'])) {
                $userData['password'] = empty($userData['username']) ? $userData['email'] : $userData['username'];
            }
            $validatorRule['password'] = 'required|min:5|max:255';
            if (isset($userData['repassword'])) {
                $validatorRule['password'] .= '|same:repassword';
            } else {
                $validatorRule['password'] .= '|confirmed';
            }
        }else{
            $userData['password'] = '';
        }

        $validator = Validator::make($userData, $validatorRule);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $err[] = '<ul>';
            foreach ($errors->all() as $message) {
                $err[] = '<li>' . $message . '</li>';
            }
            $err[] = '</ul>';
            $this->error = implode('', $err);
            Log::error($userData);
            return false;
        }

        if (!empty($userData['phone'])) {
            $userData['phone'] = $this->phoneFormat($userData['phone']);
        }

        //jika tidak menyertakan role_code maka set default
        if (empty($userData['role_code'])) {
            $userData['role_code'] = [AuthConfig::getRegistrationDefaultRoleCode()];            
        }

        if (!is_array($userData['role_code'])) $userData['role_code'] = [$userData['role_code']];
        //pastikan rule nya ada
        $roles = Role::whereIn('role_code', $userData['role_code'])->orderBy('level', 'ASC')->get();
        if ($roles->count() <= 0) {
            $this->error = 'Roles not defined.';
            return false;
        }
        $userData['role_code'] = [];
        $isFirstRow = true;
        foreach ($roles as $val) {
            $userData['role_code'][] = $val->role_code;
            if ($isFirstRow) {
                $userData['main_role'] = ['role_code' => $val->role_code, 'level' => $val->level];
                $isFirstRow = false;
            }
        }

        $userData['level'] = $userData['main_role']['level'];

        if (!empty($userData['email']) && $this->isEmailRegistered($userData['email'])) {
            $this->error = 'Email already registered.';
            return false;
        }

        if (!empty($userData['phone']) && $this->isPhoneRegistered($userData['phone'])) {
            $this->error = 'Phone already registered.';
            return false;
        }
        if (!empty($userData['username']) && $this->isUsernameRegistered($userData['username'])) {
            $this->error = 'Username already registered.';
            return false;
        }

        //pastikan tidak ada parameter yang ksosong
        foreach ($userData as $key => $value) {
            if (empty($value)) unset($userData[$key]);
        }

        if (empty($userData['username'])) {
            $userData['username'] = '';
        }
        if (empty($userData['email'])) {
            $userData['email'] = '';
        }

        if (isset($userData['repassword'])) {
            unset($userData['repassword']);
        } elseif (isset($userData['password_confirmation'])) {
            unset($userData['password_confirmation']);
        }

        return $userData;
    }

    /**
     *
     * @param array $userData gabungan data users & user_profile
     */
    public function registerProfile(array $userData)
    {
        $userData = $this->registerProfileFilter($userData);
        if (!$userData) {
            return false;
        }
        if (!isset($userData['tenant_id'])) $userData['tenant_id'] = config('tenant.id', 0);
        return UserProfile::create($userData);
    }

    /**
     *
     * @param array $userData
     * @return boolean
     */
    public function registerProfileFilter(array $userData)
    {

        $validatorRule = [
            'user_id' => 'required',
        ];
        $validator = Validator::make($userData, $validatorRule);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $err[] = '<ul>';
            foreach ($errors->all() as $message) {
                $err[] = '<li>' . $message . '</li>';
            }
            $err[] = '</ul>';
            $this->error = implode('', $err);
            return false;
        }

        //pastikan tidak ada parameter yang ksosong
        foreach ($userData as $key => $value) {
            if (empty($value) || is_null($value)) unset($userData[$key]);
        }
        return $userData;
    }

    public function deleteUser($userId)
    {
        if (!$this->_exists(new User, [['id', $userId]])) {
            $this->error = 'user tidak ditemukan';
            return false;
        }

        if ($this->checkSystemUser($userId)) {
            $this->error = 'user ini adalah user system, tidak bisa dihapus disini';
            return false;
        }

        $this->_delete(new User, [['id', $userId]]);
        $this->_delete(new UserProfile, [['user_id', $userId]]);
        $this->_delete(new UserRole, [['user_id', $userId]]);
        $this->_delete(new UserRoleGroup(), [['user_id', $userId]]);
        return true;
    }
    /**
     *
     * @param integer           $userId user id user yang akan diupdate
     * @param array             $userData
     *      role_code       *optional string role_code, jika disertakan maka akan mengubah role utama
     *      main_role_code  *optional string role_code yang menjadi main role
     *      password        *unencrypted password
     *      pin             *unencrypted pin
     *      avatar
     * @return boolean
     */
    public function updateUser($userId, $userData, $runEvent = true)
    {
        $log = [];

        if (!($oldUser = $this->getUser($userId))) {
            $this->error = __('lang.data_attribute_not_found', ['attribute' => 'User']);
            return false;
        }

        if (isset($userData['email']) && $oldUser['email'] == $userData['email']) {
            unset($userData['email']);
        }

        if (isset($userData['phone']) && $oldUser['phone'] == $userData['phone']) {
            unset($userData['phone']);
        }

        if (isset($userData['role_group_is_integrated'])) unset($userData['role_group_is_integrated']);
        if (isset($userData['_token'])) unset($userData['_token']);
        if (isset($userData['_method'])) unset($userData['_method']);
        if (isset($userData['created_at'])) unset($userData['created_at']);
        if (isset($userData['updated_at'])) unset($userData['updated_at']);
        if (isset($userData['repassword'])) unset($userData['repassword']);
        if (isset($userData['password_confirmation'])) unset($userData['password_confirmation']);
        if (isset($userData['user_role'])) unset($userData['user_role']);
        if (isset($userData['main_role'])) unset($userData['main_role']);


        if (isset($userData['username']) && $this->isUsernameRegistered($userData['username'], $userId)) {
            $this->error = 'Username already registered.';
            return false;
        }

        if (isset($userData['email']) && $userData['email'] && $this->isEmailRegistered($userData['email'], $userId)) {
            $this->error = 'Email already registered.';
            return false;
        }

        if (isset($userData['phone']) && $userData['phone'] && $this->isPhoneRegistered($userData['phone'], $userId)) {
            $this->error = 'Phone already registered.';
            return false;
        }

        if (!empty($userData['pin'])) {
            $log['pin'] = $userData['pin'];
            $userData['pin'] = Hash::make($userData['pin']);
            $log['hashed_pin'] = $userData['pin'];
        }

        $dontHaveTransactionLevel = !Tenant::dbTransactionLevel();
        if ($dontHaveTransactionLevel)
            Tenant::dbBeginTransaction();

        $hasUploadAvatar = false;

        try {

            //jika menyertakan profile, maka proses update table profile
            if (isset($userData['profile'])) {
                $this->updateProfile($userId, $userData['profile']);
                unset($userData['profile']);
            }

            //pastikan tidak ada parameter yang ksosong
            // foreach ($userData as $key => $value) {
            //     if (empty($value)) unset($userData[$key]);
            // }

            //upload avatar jika menyertakan avatar
            if (isset($userData['avatar']) && !empty($userData['avatar']) && !is_string($userData['avatar'])) {
                $userData['avatar'] = $userData['avatar'][0]['filepath']->store('images/avatar/' . $oldUser['id']);
                $hasUploadAvatar = true;
                if (!empty($oldUser['avatar'])) {
                    Storage::delete($oldUser['avatar']);
                }
            }

            if (isset($userData['email'])) $this->updateEmail($userId, $userData);
            if (isset($userData['phone'])) $this->updatePhone($userId, $userData);

            //jika update role data
            if (isset($userData['role_code'])) {
                $this->updateUserRole($userId, $userData['role_code'], empty($userData['main_role_code'])?'':$userData['main_role_code']);
                unset($userData['role_code'], $userData['role'],$userData['main_role_code']);
            }

            if(!$this->_update(new User, $userId, $userData)){
                throw new Exception($this->errorFull());
            }

            if (isset($userData['password']) && $userData['password']) {
                $this->resetPassword($userId, $userData['password']);
            }

            if ($runEvent)
                event(new \hpsynapse\moduser\Events\OnUserUpdatedSuccess($oldUser, $this->getUser($userId)));

            if ($dontHaveTransactionLevel)
                Tenant::dbCommit();

            $log['updated'] = $userData;
            UserLog::addLog($userId, 'moduser_userrepo', 'update_user', $log);

            return true;
        } catch (Exception  $e) {
            if ($dontHaveTransactionLevel)
                Tenant::dbRollback();
            if ($hasUploadAvatar)

                $this->error = $e->getMessage();

            Log::info('moduser UserRepo::updateUser() ERROR');
            Log::error($e);

            // jika sedang dalam transaksi dari parent maka teruskan error nya ke parent transaction nya
            if (!$dontHaveTransactionLevel)
                throw $e;

            return false;
        }
    }

    /**
     * banned user
     */
    public function banUser($id, $banNote = '')
    {
        return User::find($id)->update(['status' => 2, 'banned_note' => $banNote, 'banned_at' => now()]);
    }

    /**
     * unbaned user
     */
    public function unbanUser($id)
    {
        return User::find($id)->update(['status' => 1]);
    }

    public function updateProfile($userId, $userData)
    {
        $userProfile = $this->_getOne(new UserProfile, ['user_id', $userId]);
        if (!$userProfile) {
            $this->error = 'User tidak ditemukan';
            return false;
        }

        $model = new UserProfile;
        $input = [];
        $userProfileField = Schema::getColumnListing($model->getTable());

        foreach ($userProfileField as $value) {
            if (!in_array($value, ['id', 'created_at', 'updated_at', 'user_id']) && isset($userData[$value]))
                $input[$value] = $userData[$value];
        }

        if ($input != []) {
            if ($this->_exists($model, ['user_id', $userId])) {
                $this->_update($model, ['user_id', $userId], $input);
            } else {
                $input['user_id'] = $userId;
                $input['tenant_id'] = config('tenant.id', 0);
                $this->_create($model, $input);
            }
        }
    }

    public function updatePhone($userId, $userData)
    {
        if (isset($userData['phone'])) {
            $input = User::find($userId);
            $input->phone = $this->phoneFormat($userData['phone']);
            $input->phone_verified_at = null;
            $input->update();
        }

        return true;
    }

    public function updateEmail($userId, $userData)
    {
        if (isset($userData['email'])) {
            $input = User::find($userId);
            $input->email = $userData['email'];
            $input->email_verified_at = null;
            $input->update();
        }
        return true;
    }

    /**
     * reset password user
     * @param type $userId
     * @param type $newPassword
     */
    public function resetPassword($userId, $newPassword)
    {
        $user = User::find($userId);
        if (!$user) {
            $this->error = __('User tidak ditemukan');
            return false;
        }
        User::where('id',$userId)->update(['password' => Hash::make($newPassword)]);
        return true;
    }

    public function activateUser($user_id)
    {
        $userData = $this->model->find($user_id);

        if (!$userData) {
            $this->error = 'User not found.';
            return false;
        }

        UserLog::addActivityLog($user_id, 'activate');

        return $userData->update(['status' => 1]);
    }

    /**
     * do email verification
     *
     * @param type $email
     * @param type $verifyCode
     * @return boolean
     */
    public function varifyEmail($email, $verifyCode)
    {
        if ($this->generateEmailVerfifyCode($email) == $verifyCode) {
            $user = User::where('email', $email);
            if (!$user->exists()) {
                $this->error = __('auth.emailverify_fail_mailnotfound');
                return false;
            }

            $user = $user->whereNull('email_verified_at')->first();
            if ($user) {
                $userId = $user->id;
                $user->{'email_verified_at'} = now()->toDateTimeString();
                $user->save();
                event(new \hpsynapse\moduser\Events\OnEmailVerifiedSuccess($this->getUser($userId)));
            }

            return true;
        }
        $this->error = __('auth.emailverify_fail_varificationcodeinvalid');
        return false;
    }

    /**
     * do phone verification with OTP
     *
     * @param type $phone
     * @param type $otpCode
     * @return boolean
     */
    public function varifyPhone($phone, $otpCode, $tenantId = false)
    {
        $tenantId = $tenantId?$tenantId:config('tenant.id',0);
        $user = User::where('phone', $phone)->where('tenant_id',$tenantId)->first();
        if ($this->isOTPValid($user->id, $otpCode, $tenantId)) {
            // $phoneField = 'phone';
            $user = User::where('phone', $phone)->where('tenant_id',$tenantId)->whereNull('phone_verified_at')->first();
            if (!$user) {
                $this->error = __('auth.phoneverify_fail_phonenotfound');
                return false;
            }
            $user->{'phone_verified_at'} = now()->toDateTimeString();
            $user->save();
            return true;
        }
        $this->error = __('auth.phoneverify_fail_verificationcodeinvalid');
        return false;
    }

    public function varifyResetPasswordToken($email, $verifyCode, $delete = false)
    {
        //jika match
        if ($this->generateEmailVerfifyCode($email) == $verifyCode) {

            $passwordReset = PasswordReset::where('tenant_id',config('tenant.id',0))
                ->where('email', $email)->where('token', $verifyCode)->first();
            if (!$passwordReset) {
                $this->error = __('auth.resetpassword_fail_mailnotfound');
                return false;
            }
            $return = $passwordReset->toArray(); 
            if ($delete) $passwordReset->delete();
            return $return;
        }
        $this->error = __('auth.resetpassword_fail_verificationcodeinvalid');
        return false;
    }

    
    public function deleteResetPasswordToken($email)
    {
        PasswordReset::where('tenant_id',config('tenant.id',0))
                ->where('email', $email)->first()->delete();
    }



    /**
     * general helper
     * =======================================================================
     */


    /**
     * update format nomor telepon menja
     * @param String $phone
     * @return String
     */
    public function phoneFormat($phone)
    {
        $phone = ltrim($phone, '0');
        if (empty($phone)) return '';
        //jika belum memasukan kode negara maka set indonesia
        if (strpos($phone, '+') === false) {
            $phone = '+62' . $phone;
        }
        return $phone;
    }
    /**
     * generate perkiraan user id selanjutanya
     *
     * @return int perkiraan user id selanjutnya
     */
    public function nextUserId()
    {
        $nextId = 0;
        $next = $this->model->select('id')->orderBy('id', 'DESC')->first();
        if ($next) {
            $nextId = $next->id + 1;
        }
        return $nextId;
    }
    /**
     * generate user_idcode
     * format YYYYMMDD[USER_ID][KARAKTER 0 HINGGA 8 DIGIT][NO URUT PENDAFTARAN HARI INI]
     *
     * @return int perkiraan user_idcode
     */
    public function generateUserIdcode()
    {
        $count = $this->model->whereDate('created_at', '=', Carbon::today()->toDateString())->count() + 1;
        $nextId = $this->nextUserId();

        $zero = strlen($count . $nextId)<9?str_repeat('0', 8 - strlen($count . $nextId)):'';

        $idcode = Carbon::today()->format('Ymd') . $nextId . $zero . $count;
        return $idcode;
    }


    /**
     * Manage USER ROLE
     * -------------------------------------------------------------------------
     */
    public function isMainUserRole($userId,$roleId)
    {
        return UserRole::where('user_id', $userId)
            ->where('role_id', $roleId)
            ->where('is_main_role',1)->exists();
    }

    /**
     *
     * @param String $userId    
     * 
     * @return boolean|array    list role user, format mirip data role di APPSSession 
     *                          plus data user role group nya jika ada
     */
    public function listUserRole($userId, $withoutTime = true, $mainRoleOnly = false, $filterByClient = true)
    {
        $response = [];
        $userRoleData = UserRole::where('user_id', $userId);
        if ($mainRoleOnly) {
            $userRoleData->where('is_main_role', 1);
        }
        $userRoleData = $userRoleData->get();
        if (!$userRoleData) return false;
        foreach ($userRoleData as $value) {
            $roleData = Role::with(['roleGroup'])->where('id', $value->role_id)->first();
            if ($roleData) {
                $roleData = $roleData->toArray();
                if (!UserAuth::isH2H() && $filterByClient) {
                    $roleData = $this->_listUserRole_filterByClient($roleData);
                }
                $roleData['is_main_role'] = $value->is_main_role;
                $roleData['has_auth_grant'] = $value->has_auth_grant;

                if ($withoutTime) {
                    unset($roleData['created_at'], $roleData['updated_at']);
                }

                // get additional identity data 
                if($roleData['role_group'] && $roleData['role_group']['has_model']){
                    $roleData['role_group']['data'] = $roleData['role_group']['model']::where('user_id',$userId)->first();
                }

                $response[$roleData['role_code']] = $roleData;
            }
        }

        // pastikan ada main role, jika tidak ada set satu teratas
        if(!UserRole::where('user_id', $userId)->where('is_main_role',1)->exists()){
            UserRole::where('user_id', $userId)->first()->update([
                'is_main_role'=>1
            ]);
        }

        return $response;
    }
    
    private function _listUserRole_filterByClient($roleData)
    {
        $clientData = UserAuth::getClient();
        if(empty($clientData))return $roleData;
        $clientRoles = $this->listUserRole($clientData['id'], true, true, false);
        $firstRole = reset($clientRoles);
        if (!is_array($firstRole['rule'])) {
            $firstRole['rule'] = json_decode($firstRole['rule'], true);
        }
        if (!empty($firstRole['rule'])) {
            $newRule = $roleData['rule'] ? array_intersect_key($firstRole['rule'], $roleData['rule']) : $firstRole['rule'];
            $roleData['rule'] = $newRule;
        }
        return $roleData;
    }

    /**
     * 
     */
    public function getRoleLevelGroup($where)
    {
        $model =  new RoleLevelGroup();
        $response = false;
        if(isset($where['level'])){
            if($response = $model->where('level_start','<=',$where['level'])->where('level_end','>=',$where['level'])->first()){
                return $response->toArray();
            }
        }else{
            return $this->_getOne($model, $where);
        }
        return false;
    }

    /**
     * Generate list role_code user aktif, untuk keperluan isi field 'role' di table user.
     * Dalam proses nya juga akan mendelete user role yg sudah ada.
     * 
     * @param type $userId
     */
    public function generateUserRole($userId)
    {
        $dataRole = UserRole::with(['role'])->where('user_id', $userId)->get()->toArray();
        if (!$dataRole) return '';

        $tmpRoleExist = [];
        $data = [];
        foreach ($dataRole as $value) {
            if(!empty($value['role']) && !isset($tmpRoleExist[$value['role']['role_code']])){
                $tmpRoleExist[$value['role']['role_code']] = $value['role']['role_code'];
            }else{
                // jika sudah ada berarti double, maka delete
                // atau jika role id sudah tidak ada maka delete juga
                UserRole::where('id',$value['id'])->delete();
                continue;
            }
            $data[] = $value['role']['role_code'];
        }
        return ';' . implode(';', $data) . ';';
    }

    /**
     * Generate list role_code user aktif, untuk keperluan isi field 'role_level' di table user.
     * Dalam proses nya juga akan mendelete user role yg sudah ada.
     * 
     * @param type $userId
     */
    public function generateUserRoleLevel($userId)
    {
        $dataRole = UserRole::with(['role'])->where('user_id', $userId)->get()->toArray();
        if (!$dataRole) return '';

        $tmpRoleLevelExist = [];
        $data = [];
        foreach ($dataRole as $value) {
            if (!empty($value['role'])) {
                $tmpRoleLevelExist[$value['role']['level']] = $value['role']['level'];
            }
            
            $data[] = $value['role']['level'];
        }

        return ';' . implode(';', $data) . ';';
    }

    /**
     * tambah/assign role baru ke user
     */
    public function addUserRole($userId, $roleCode, $isMainRole = 0, $hasAuthGrant = 0)
    {
        $role = $this->_getOne(Role::with('roleGroup'), ['role_code', $roleCode]);
        if (!$role){
            $this->error = 'Role code '.$roleCode.' tidak ditemukan';
            return false;
        } 

        //cek pastikan user role belum terdaftar, jika sudah terdaftar maka tolak
        $userRole = $this->_getOne(new UserRole, [['user_id', $userId], ['role_id', $role['id']]]);
        if ($userRole) {
            $this->error = 'Role '.$role['role_code'].' sudah terdaftar di user bersangkutan';
            return false;
        }

        $this->_create(new UserRole, [
            'tenant_id' => $role['tenant_id'],
            'user_id' => $userId,
            'role_id' => $role['id'],
            'is_main_role' => $isMainRole,
            'has_auth_grant' => $hasAuthGrant,
        ]);
        
        if($role['role_group']){
            $this->_create(new UserRoleGroup, [
                'tenant_id' => $role['tenant_id'],
                'user_id' => $userId,
                'role_group_id' => $role['role_group_id'],
                'role_group_code' => $role['role_group']['code'],
                'created_at'=>now(),
            ]);
        }

        //update role di table user
        $this->updateUser($userId, [
            'role' => $this->generateUserRole($userId),
            'role_level' => $this->generateUserRoleLevel($userId)
        ]);

        return $role;
    }

    /**
     * Update data user role berdasarkan list role code $newRoleCode
     */
    public function updateUserRole($userId, $newRoleCode, $mainRoleCode='', $hasAuthGrant = 0)
    {
        if (!is_array($newRoleCode)) $newRoleCode = [$newRoleCode];

        if(!in_array($mainRoleCode,$newRoleCode)){
            $mainRoleCode = '';
        }

        $roleIds = Role::whereIn('role_code', $newRoleCode)->orderBy('level', 'ASC')->get()->pluck('id');

        if (count($roleIds) <= 0) {
            $this->error = 'Role not defined.';
            return false;
        }

        //delete semua role yang tidak terpilih
        UserRole::where('user_id', $userId)->whereNotIn('role_id', $roleIds)->delete();

        $roles = Role::with(['roleGroup'])->whereIn('role_code', $newRoleCode)->orderBy('level', 'ASC')->get();
        $isMainRole = 1;
        $mainRoleSetted = false;
        $userRoleGroupIds = [];
        foreach ($roles as $role) {

            if(!empty($mainRoleCode))
                if($role->role_code == $mainRoleCode){
                    $isMainRole = 1;           
                }else{
                    $isMainRole = 0;
                }

            if (UserRole::where('user_id', $userId)->where('role_id', $role->id)->exists()) {
                $this->_update(new UserRole, [['user_id', $userId], ['role_id', $role->id]], [
                    'has_auth_grant' => $hasAuthGrant,
                    'is_main_role' => $isMainRole,
                ]);
            } else {
                $this->_create(new UserRole, [
                    'tenant_id' => config('tenant.id', 0),
                    'user_id' => $userId,
                    'role_id' => $role->id,
                    'has_auth_grant' => $hasAuthGrant,
                    'is_main_role' => $isMainRole,
                ]);
            }

            if($isMainRole)
                $mainRoleSetted = true; 

            $isMainRole = 0;

            if($role->roleGroup){
                if ($tmpUserRoleGroup = UserRoleGroup::where('user_id', $userId)->where('role_group_id', $role->role_group_id)->first()) {
                    $userRoleGroupIds[] = $tmpUserRoleGroup->id;
                } else {
                    $tmpUserRoleGroup = $this->_create(new UserRoleGroup, [
                        'tenant_id' => config('tenant.id', 0),
                        'user_id' => $userId,
                        'role_group_id' => $role->role_group_id,
                        'role_group_code' => $role->roleGroup->code,
                        'created_at'=>now(),
                    ]);
                    $userRoleGroupIds[] = $tmpUserRoleGroup['id'];
                }

            }
        }

        // jika tidak ada mainrole yg diset maka set role pertamanya
        if(!$mainRoleSetted){
            UserRole::where('user_id', $userId)->first()->update([
                'is_main_role'=>1
            ]);
        }
        
        //delete semua role group yang tidak terpilih
        if(empty($userRoleGroupIds)){
            UserRoleGroup::where('user_id', $userId)->delete();
        }else{
            UserRoleGroup::where('user_id', $userId)->whereNotIn('id', $userRoleGroupIds)->delete();
        }

        $roleUser = $this->generateUserRole($userId);
        $roleLevelUser = $this->generateUserRoleLevel($userId);
        
        User::where('id', $userId)->update([
            'role' => $roleUser,
            'role_level' => $roleLevelUser,
            'level' => $role->level
        ]);
    }
    
    /**
     * Un-assign role dari user
     */
    public function deleteUserRole($userId, $roleCode)
    {
        $role = $this->_getOne(Role::with('roleGroup'), ['role_code', $roleCode]);
        if (!$role) {
            $this->error = __('lang.data_attribute_not_found',['attribute'=>__('role.role.name')]);
            return false;
        }

        //delete role dari user role
        $roleData = $this->_delete(new UserRole, [
            ['user_id',$userId],
            ['role_id',$role['id']]
        ]);
        
        if($role['role_group']){
            $this->_delete(new UserRoleGroup, [
                ['user_id',$userId],
                ['role_group_id',$role['role_group_id']]
            ]);
        }

        //delete role di table user
        $this->updateUser($userId, [
            'role' => $this->generateUserRole($userId),
            'role_level' => $this->generateUserRoleLevel($userId)
        ]);

        return $roleData;
    }

    public function checkSystemUser($id)
    {
        $user = $this->_getOne(new User, ['id', $id]);
        if ($user['system_user']) {
            return true;
        }
        return false;
    }

    /**
     * belum dipakai
     */
    // public function updateUserRole($key,$data)
    // {
    //     //cek pastikan user role sudah terdaftar
    //     $userRole = $this->_getOne(new UserRole, $key);
    //     if(!$userRole)return false;
    //     if(!isset($data['role_code'])){
    //         $data['role_code'] = $userRole['role_code'];
    //     }

    //     $roleData = $this->_update(new UserRole,['user_id'=>$userRole['user_id'],'role_code'=>$userRole['role_code']], [
    //         'role_code' => $data['role_code'],
    //         'is_main_role' =>isset($data['is_main_role'])&&$data['is_main_role']?1:0,
    //         'has_auth_grant'=>isset($data['has_auth_grant'])&&$data['has_auth_grant']?1:0,
    //     ]);

    //     //update role di table user
    //     $this->updateUser($userRole['user_id'], ['role'=> $this->generateUserRole($userRole['user_id'])]);
    //     return $roleData;
    // }

    /**
     * Belum selesai
     */
    public function setAuthGrant($userId, $roleCode)
    {
        $user = User::find($userId);
        $user->roles()->where('has_auth_grant');
        return User::find($userId)->update(['status' => 2]);
    }

    /**
     * Belum selesai 
     * TO DO! LUPA BUAT APA, NANTI DELETE AJA KALO GA ADA ERROR
     */
    /*public function updateRole($userId, $userData = null)
    {

        if (isset($userData['role']) && is_array($userData['role'])) {
            $userRole = UserRole::where('user_id', $userId)->pluck('role_code')->toArray();

            $hasIsMainRole = 0;
            foreach ($userData['role'] as $value) {
                //simpan semua nama role yg dipilih untuk keperluan delete role yg tidak dipilih
                $role[] = $value['role_code'];
                if (isset($value['role_code']) && $value['role_code']) {
                    //tandai apakah ada is_main_role yg dipilih, jika tidak ada maka
                    //nanti di proses selanjutnya tambahkan is_main_role ke member
                    if (isset($value['is_main_role']) && $value['is_main_role']) {
                        $hasIsMainRole++;
                    }
                    //inputkan role baru yang dipipilih
                    if (!in_array($value['role_code'], $userRole)) {
                        $this->addUserRole(
                            $userId,
                            $value['role_code'],
                            isset($value['is_main_role']) && $value['is_main_role'] ? 1 : 0,
                            isset($value['has_auth_grant']) && $value['has_auth_grant'] ? 1 : 0,
                            false
                        );
                        //update role yang memang sebelumnya telah ada
                    } else {

                        $this->updateUserRole([
                            'user_id' => $userId,
                            'role_code' => $value['role_code']
                        ], [
                            'is_main_role' => isset($value['is_main_role']) && $value['is_main_role'] ? 1 : 0,
                            'has_auth_grant' => isset($value['has_auth_grant']) && $value['has_auth_grant'] ? 1 : 0
                        ]);
                    }
                }
            }

            foreach ($userRole as $value) {
                //hapus role lama yang tidak dipilih
                if (!in_array($value, $role)) {
                    $this->deleteUserRole($userId, $value);
                }
            }

            //jika tidak memilih main role atau yg dipilih lebih dari 1 maka
            //set members sebagai main role
            if (!$hasIsMainRole || $hasIsMainRole > 1) {

                $this->updateUserRole([
                    'user_id' => $userId,
                    'role_code' => 'member'
                ], ['is_main_role' => 1]);
            }
            unset($userData['role']);
        }

        //        if(isset($userData['is_main_role']) && is_array($userData['has_auth_grant'])){
        //            foreach ($userData['role_code'] as $key => $value) {
        //                if(!in_array($value, $userData['has_auth_grant'])){
        //                    UserRole::where(['user_id' => $userId,'role_code' => $value])->update(['has_auth_grant'=>0]);
        //                }
        //            }
        //
        //            foreach ($userData['has_auth_grant'] as $item) {
        //                if(in_array($item, $userData['role_code'])){
        //                    UserRole::where(['user_id' => $userId,'role_code' => $item])->update(['has_auth_grant'=>1]);
        //                }
        //            }
        //
        //            UserRole::where('user_id', $userId)->update(['is_main_role'=>0]);
        //            UserRole::where(['user_id' => $userId,'role_code' => $userData['is_main_role']])->update(['is_main_role'=>1]);
        //        }

    }*/

    /**
     * USER GROUP
     * -------------------------------------------------------------------------
     */

    /**
     * create user group
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
                'attribute' => __('user.user_group.data_group.field_name.code')
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

            Log::info('moduser/src/Repositories UserRepo::createGroup() ERROR');
            Log::error($e);

            // jika sedang dalam transaksi dari parent maka teruskan error nya ke parent transaction nya
            if (!$dontHaveTransactionLevel)
                throw $e;

            return false;
        }
    }

    /**
     * update user group
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
                'attribute' => __('user.user_group.data_group.name')
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
                'attribute' => __('user.user_group.data_group.field_name.code')
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

            Log::info('moduser/src/Repositories UserRepo::updateGroup() ERROR');
            Log::error($e);

            // jika sedang dalam transaksi dari parent maka teruskan error nya ke parent transaction nya
            if (!$dontHaveTransactionLevel)
                throw $e;

            return false;
        }
    }

    /**
     * delete user group
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
                'attribute' => __('user.user_group.data_group.name')
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
}
