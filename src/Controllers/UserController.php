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

class UserController extends BaseController
{
    protected $accessRuleKey = 'moduser.user';

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

    /**
     * GET - /api/user
     * List user standard
     * 
     * @param \Illuminate\Http\Request $request
     */
    public function readList(Request $request)
    {
        if (!$this->accessCheck('r')) {
            return $this->done();
        }

        $this->buildParams(true, [
            'system_user',
            'role',
            'role_level_group_code',
            'level',
            'level_except'
        ]);

        $this->output['params']['filter'][] = ['system_user', false];

        if (UserAuth::isLogin()) {
            $this->output['params']['filter'][] = ['id', '!=', UserAuth::user('id')];
            $this->output['params']['filter'][] = ['level', '>', UserAuth::user('level')];
        }

        //jika menyertakan status
        if ($request->input('role', false))
            $this->output['params']['filter'][] = ['role', 'LIKE', '%;' . $request->input('role') . ';%'];
        if ($request->input('role_level_group_code', false))
            $this->output['params']['filter']['role_level_group_code'] = $request->input('role_level_group_code');

        if ($request->input('level', false)) {
            $this->output['params']['filter'][] = ['level', '!=', $request->input('level')];
        } else if ($request->input('level_except', false)) {
            $this->output['params']['filter'][] = ['level', '!=', $request->input('level_except')];
        }

        //jika multitenant aktif dan bukan dari aplikasi owner maka filter berdasarkan tenant nya
        if (config('AppConfig.system.multitenant.active', false) && config('tenant.id', 0) > 1) {
            $this->output['params']['filter'][] = ['tenant_id', config('tenant.id')];
        }

        $this->output['data'] = UserRepo::listUser(
            $this->output['params']['filter'],
            $this->output['params']['query']['offset'],
            $this->output['params']['query']['limit'],
            $this->output['params']['orderBy'],
        );

        return $this->done();
    }

    /**
     * GET - /api/user/{id}
     *
     * @param \Illuminate\Http\Request $request
     * @param String $id        Route param user id
     * 
     * @return Array default synapse api return
     *      data
     *          ...all user record
     *          profile Array record user_proflie
     *          user_role
     *          main_role Array record role utama user
     *
     */
    public function readOne(Request $request, $id)
    {
        if (!$this->accessCheck('r')) {
            return $this->done();
        }

        $this->buildParams();
        $this->output['params']['filter'][] = ['id', $id];

        if(!($this->output['data'] = UserRepo::getUser($this->output['params']['filter']))){
            $this->setError(__('lang.data_not_found'));
            return $this->done();
        }

        if (!(UserAuth::hasAccess($this->accessRuleKey, 'r') || $this->output['data']['id'] == UserAuth::user('id'))) {
            $this->setError(__('alert.access_denied'), false, 403);
            return $this->done();
        }

        $this->output['data']['user_role'] = UserRepo::listUserRole($this->output['data']['id']);

        foreach ($this->output['data']['user_role'] as $key => $val) {
            if ($val['is_main_role']) {
                $this->output['data']['role_code'] = $key;
            }
        }

        return $this->done();
    }

    /**
     * POST - /api/user/
     *
     * @param \Illuminate\Http\Request $request
     *      name
     *      email
     *      username
     *      password
     *      role_code
     */
    public function create(Request $request)
    {
        if (!$this->accessCheck('c')) {
            return $this->done();
        }

        $userData = $request->all(); //$request->only(['name', 'email', 'password']);
        $userData['password'] = UserAuth::decryptCredential($userData['password']);
        $userData['repassword'] = UserAuth::decryptCredential($userData['repassword']);

        $validator = [
            'name' => 'required|min:3|max:255',
            'password' => 'required|min:5|max:255|same:repassword',
        ];

        if (isset($userData['username']) && $userData['username'] != '') {
            $validator['username'] = 'required|min:3|max:255';
        }

        if (isset($userData['email']) && $userData['email'] != '') {
            $validator['email'] = 'required|email|max:255';
        }

        if (!isset($validator['username']) && !isset($validator['email'])) {
            $this->setError('Username atau email harus diisi');
            return $this->done();
        }

        $validator = Validator::make($userData, $validator);

        if ($validator->fails()) {
            $this->setError('Data keliru', $validator->messages());
            return $this->done();
        }

        //jika berhasil
        if ($user = UserRepo::register($userData, false, true)) {
            $this->setAlert('Data Inserted successfully', 'success');
        } else {
            $this->setError(UserRepo::error());
        }

        return $this->done();
    }

    /**
     * POST - /api/user/{userId}
     * 
     * Update user
     * 
     * @param \Illuminate\Http\Request $request
     * @param String $id
     */
    public function update(Request $request, $id)
    {
        if (!$this->accessCheck('u')) {
            return $this->done();
        }

        $input = $request->all();

        $validator = [];

        if (isset($input['username'])) {
            $validator['username'] = 'required|min:3|max:255';
        }
        if (isset($input['name'])) {
            $validator['name'] = 'required|min:3|max:255';
        }
        if (!empty($input['email'])) {
            $validator['email'] = 'required|email|min:3|max:255';
        }
        if (!empty($input['phone'])) {
            $validator['phone'] = 'required|min:3|max:255';
        }
        if (!empty($input['pin'])) {
            $input['pin'] = UserAuth::decryptCredential($input['pin']);
            $validator['pin'] = 'digits:6';
        }
        if (!empty($input['password'])) {
            $input['password'] = UserAuth::decryptCredential($input['password']);
            $validator['password'] = 'min:6';
            if (isset($input['repassword'])) {
                $input['repassword'] = UserAuth::decryptCredential($input['repassword']);
                $validator['password'] .= '|same:repassword';
            } else {
                $input['password_confirmation'] = UserAuth::decryptCredential($input['password_confirmation']);
                $validator['password'] .= '|confirmed';
            }
        }

        if (!empty($validator)) {
            $validator = Validator::make($input, $validator);
            if ($validator->fails()) {
                $this->setError('Input Error :', $validator->messages(), 400, 400, true);
                return $this->done();
            }
        }

        if (empty($input['banned_note'])) {
            unset($input['banned_note']);
        }

        if ($request->file('avatar', false))
            $input['avatar'] = $request->file('avatar');

        if (isset($input['repassword'])) {
            unset($input['repassword']);
        } elseif (isset($input['password_confirmation'])) {
            unset($input['password_confirmation']);
        }

        if (UserRepo::updateUser($id, $input)) {
            $this->setAlert('Data Updated successfully', 'success');
        } else {
            $this->setError(UserRepo::error());
        }

        return $this->done();
    }


    /**
     * upload avatar
     *
     * @param Request $request
     *      avatar
     */
    public function uploadAvatar(Request $request)
    {
        // if(!UserAuth::hasAccess($this->accessRuleKey,'u')){
        //     $this->setError(__('alert.access_denied'),false,403);
        //     return $this->done();
        // }

        if ($request->file('avatar', false) == false) {
            $this->setError(__('validation.required', ['attribute' => 'Avatar']));
            return $this->done();
        }

        $id = $request->route('id');
        if (UserRepo::updateUser($id, [
            'avatar' => $request->file('avatar')
        ])) {
            $this->setAlert(__('alert.update_success', ['attribute' => 'Avatar']), 'success');
        } else {
            $this->setError(UserRepo::error(), UserRepo::errorValidator());
        }
        return $this->done();
    }

    public function deleteAvatar(Request $request)
    {
        // if(!UserAuth::hasAccess($this->accessRuleKey,'u')){
        //     $this->setError(__('alert.access_denied'),false,403);
        //     return $this->done();
        // }

        $id = $request->route('id');

        if (UserRepo::deleteAvatar($id)) {
            $this->setAlert(__('alert.delete_success', ['attribute' => 'Avatar']), 'success');
        } else {
            $this->setError(UserRepo::error(), UserRepo::errorValidator());
        }

        return $this->done();
    }

    /**
     * update password di my profile
     *
     * @param Request $request
     *      password
     *      password_confirmatin
     */
    public function updatePassword(Request $request)
    {
        $id = UserAuth::user('id'); //$request->route('id');
        $userData = $request->only(['password', 'password_confirmation']);
        $userData['password'] = UserAuth::decryptCredential($userData['password']);
        $userData['password_confirmation'] = UserAuth::decryptCredential($userData['password_confirmation']);

        $validator = Validator::make($userData, [
            'password' => 'required|min:8|max:255|confirmed',
            'password_confirmation' => 'required|min:8|max:255'
        ]);

        if ($validator->fails()) {
            $this->setError('Input Error :', $validator->messages(), 400, 400, true);
            return $this->done();
        }

        // unset($userData['password_confirmation']);

        $change = UserRepo::resetPassword($id, $userData['password']);
        if (!$change) {
            return $this->done();
        }

        $this->setAlert('Password Updated successfully', 'success');
        return $this->done();
    }

    public function ban(Request $request)
    {
        if (!UserAuth::hasAccess($this->accessRuleKey, 'u')) {
            $this->setError(__('alert.access_denied'), false, 403);
            return $this->done();
        }

        $id = $request->route('id');
        UserRepo::banUser($id, $request->input('banned_note', ''));
        $this->setAlert('User banned successfully', 'success');
        return $this->done();
    }

    public function unban(Request $request)
    {
        if (!UserAuth::hasAccess($this->accessRuleKey, 'u')) {
            $this->setError(__('alert.access_denied'), false, 403);
            return $this->done();
        }

        $id = $request->route('id');
        UserRepo::unbanUser($id);
        $this->setAlert('User unbanned successfully', 'success');
        return $this->done();
    }

    public function resentVerificationMail(Request $request)
    {
        // if(!UserAuth::hasAccess($this->accessRuleKey,'u')){
        //     $this->setError(__('alert.access_denied'),false,403);
        //     return $this->done();
        // }

        $id = $request->route('id');
        if (($userData = UserRepo::getUser(['id', $id])) != false) {
            if (isset($userData['email']) && $userData['email']) {
                // $to_email = $userData['email'];
                // $data = ['message' => 'Ini adalah pesan uji dari Laravel.'];

                // Mail::to($to_email)->send(new SampleMail($data));

                UserRepo::sendUserActivationEmail($id);
                $this->setAlert('Email sent', 'success');
            } else {
                $this->setError(__('lang.data_attribute_not_found', ['attribute' => 'Email']));
            }
        } else {
            $this->setError(__('lang.data_attribute_not_found', ['attribute' => 'User Id ' . $id]));
        }
        return $this->done();
    }

    public function delete(Request $request)
    {
        if (!UserAuth::hasAccess($this->accessRuleKey, 'd')) {
            $this->setError(__('alert.access_denied', false, 403));
            return $this->done();
        }

        $id = $request->route('id');

        if (UserAuth::isLogin() && $id != UserAuth::user('id')) {
            $filter[] = ['id', $id];
            $filter[] = ['level', '>', UserAuth::user('level')];
            $data = UserRepo::listUser($filter);
            if ($data['count'] <= 0) {
                $this->setError('Permission denied');
                return $this->done();;
            }
        }

        if (!UserRepo::deleteUser($id)) {
            $this->setError('Error : ' . UserRepo::error());
        }
        return $this->done();
    }


    /**
     * PROFILE
     * =================================================================
     */

    public function profile(Request $request)
    {
        $this->response = 'user.profile';

        $this->output['data'] = UserAuth::user();
        $this->output['data']['user_role'] = UserRepo::listUserRole($this->output['data']['id']);

        foreach ($this->output['data']['user_role'] as $key => $val) {
            if ($val['is_main_role']) {
                $this->output['data']['role_code'] = $key;
            }
        }

        return $this->done();
    }

    public function validatePin(Request $request, $encryptedPin)
    {
        if(!AuthConfig::isPINEnabled()){
            $this->setError('PIN Disabled'); 
            return $this->done();
        }

        $pin = UserAuth::decryptCredential($encryptedPin);

        if (UserAuth::isPinValid($pin)) {
            $this->setMessage('PIN Sesuai', 'success');
        } else {
            $this->setError('PIN keliru');
        }

        return $this->done();
    }

    /**
     * POST - /api/user/profile
     */
    public function updateProfile(Request $request)
    {
        if (UserAuth::isLogin()) {
            $id = UserAuth::user('id');
        } else {
            $this->setError('User belum login');
            return $this->done();
        }

        $input = $request->all();

        if (isset($input['id'])) unset($input['id']);
        if (isset($input['created_at'])) unset($input['created_at']);
        if (isset($input['updated_at'])) unset($input['updated_at']);

        $validator = [];

        if (isset($input['username'])) {
            $validator['username'] = 'required|min:3|max:255';
        }
        if (isset($input['name'])) {
            $validator['name'] = 'required|min:3|max:255';
        }
        if (isset($input['email'])) {
            $validator['email'] = 'required|email|min:3|max:255';
        }
        if (isset($input['phone'])) {
            $validator['phone'] = 'required|min:3|max:255';
        }

        if (AuthConfig::isPINEnabled() && !empty($input['pin'])) {
            $input['pin'] = UserAuth::decryptCredential($input['pin']);
            $validator['pin'] = 'digits:6';
        }

        $validator = Validator::make($input, $validator);
        if ($validator->fails()) {
            $this->setError('Input Error :', $validator->messages(), 400, 400, true);
            return $this->done();
        }

        if (isset($input['role_code'])) unset($input['role_code']);
        if (isset($input['status'])) unset($input['status']);

        if ($request->file('avatar', false))
            $input['avatar'] = $request->file('avatar');
            
        if (AuthConfig::isPINEnabled() && AuthConfig::isOTPEnabled() && !empty($input['pin'])) {

            if (UserRepo::isOTPValid(UserAuth::user('id'),$input['token'])==false) {
                $this->setError('OTP Keliru');
                return $this->done();
            }
        }

        unset($input['token']);

        if (UserRepo::updateUser($id, $input)) {
            $this->setMessage('Data Updated successfully', 'success');
        } else {
            $this->setAlert(UserRepo::error(), 'danger');
            $this->setError(UserRepo::error());
        }        

        return $this->done();
    }

    public function sendOtp()
    {
        if(!AuthConfig::isOTPEnabled()){
            $this->setError('OTP Disabled'); 
            return $this->done();
        }

        if($timeout = UserRepo::sendOTP(UserAuth::user('id'))){
            $this->setData([
                'timeout'=>$timeout
            ])->setMessage('OTP berhasil dikirim', 'success');
        }else{
            $this->setError(UserRepo::error());            
        }
        return $this->done();
    }

    /**
     * POST - /api/user/validateOtp
     * 
     * cek apakah otp valid
     * 
     * @param \Illuminate\Http\Request $request
     *      token
     */
    public function validateOtp(Request $request)
    {
        if(!AuthConfig::isOTPEnabled()){
            $this->setError('OTP Disabled'); 
            return $this->done();
        }

        if(UserRepo::isOTPValid(UserAuth::user('id'),$request->input('token'))){
            $this->setMessage('OTP valid', 'success');
        }else{
            $this->setError(UserRepo::error());            
        }

        return $this->done();
    }

    // kirim kode otp ke wa untuk mengubah pin
    // public function sendOtp()
    // {
    //     $otp = str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);

    //     $userTenantId = UserAuth::user('tenant_id');
    //     $userId = UserAuth::user('id');
    //     $userPhone = UserAuth::user('phone');

    //     UserOTP::updateOrInsert(
    //         ['tenant_id' => $userTenantId, 'user_id' => $userId, 'phone' => $userPhone],
    //         ['token' => $otp]
    //     );

    //     Mail::to(UserAuth::user('email'))->send(new sendMailOtp($otp));

    //     return true;
    // }

    // update data pin
    // BELUM DIGUNAKAN
    // public function updateProfilePin(Request $request)
    // {
    //     if (UserAuth::isLogin()) {
    //         $id = UserAuth::user('id');
    //     } else {
    //         $this->setError('User belum login');
    //         return $this->done();
    //     }

    //     $input = $request->all();

    //     if (isset($input['id'])) unset($input['id']);
    //     if (isset($input['created_at'])) unset($input['created_at']);
    //     if (isset($input['updated_at'])) unset($input['updated_at']);

    //     $validator = [];
    //     if (!empty($input['pin'])) {
    //         $input['pin'] = UserAuth::decryptCredential($input['pin']);
    //         $validator['pin'] = 'digits:6';
    //     }

    //     if (!empty($validator)) {
    //         $validator = Validator::make($input, $validator);
    //         if ($validator->fails()) {
    //             $this->setError('Input Error :', $validator->messages(), 400, 400, true);
    //             return $this->done();
    //         }
    //     }

    //     if (isset($input['role_code'])) unset($input['role_code']);
    //     if (isset($input['status'])) unset($input['status']);

    //     if ($request->file('avatar', false))
    //         $input['avatar'] = $request->file('avatar');

    //     if (UserRepo::updateUser($id, $input)) {
    //         $this->setAlert('Data Updated successfully', 'success');
    //     } else {
    //         $this->setAlert(UserRepo::error(), 'danger');
    //         $this->setError(UserRepo::error());
    //     }

    //     return $this->done();
    // }

    /**
     * GET
     *      /auth/change_role/ROLE_CODE
     *      /api/auth/change_role/ROLE_CODE
     */
    public function changeRole(Request $request)
    {
        $roleCode = $request->route('role_code');
        $backLink = $request->input('backlink', false);
        $this->response = $backLink ? redirect($backLink) : back();

        if (UserAuth::setActiveRole($roleCode)) {
            $roleName = UserAuth::role($roleCode)['name'];
            $this->setAlert('Role <b>' . $roleName . '</b> berhasil diaktifkan', 'success');
        } else {
            $this->setAlert('Role tidak ditemukan', 'danger');
        }

        return $this->done();
    }

    /**
     * Export User Data
     *
     */
    public function downloadDataUser(Request $request)
    {
        $this->buildParams();

        /**
         * Start Download Init
         * ---------------------------------------------------------------------
         */
        $cacheKey = 'exportdatauser.' . UserAuth::user('id');

        // proses generate download
        if ($this->output['data'] = Export::createExport(
            $cacheKey,
            [ExportUserFormater::class, 'downloadUserData'],
            [
                'filter' => $this->output['params']['filter'],
            ],
            UserAuth::user('id'),
            config('tenant.id'),
            'spout' // bisa isi 'phpspreadsheet' atau 'spout', tapi jika tidak disertakan maka akan otomatis 'spout'
        )) {
            // set paramter tambahan untuk digunakan diproses joobs jika diperlukan
            Export::setAddsParam($cacheKey, []);

            // set nama kolom dari kolom sebelah kiri ke kolom sebelah kanan
            Export::setColumn($cacheKey, ExportUserFormater::columnDownload());

            // set formater untuk row data yang diinsert
            Export::setCoreRowFormater($cacheKey, ExportUserFormater::class, 'dataUserCoreRowFormater');

            // set formater saat proses export selesai

            if (!($this->output['data'] = Export::dispatchExport($cacheKey))) {
                $this->setError(Export::error() ? Export::error() : 'Dispatch job error');
            }
        } else {
            $this->setError(Export::error() ? Export::error() : 'Create export error');
        }

        return $this->done();
    }

    public function downloadDataUserStatus(Request $request)
    {
        $cacheKey = 'exportdatauser.' . UserAuth::user('id');
        $this->output['data'] = Export::getExport($cacheKey);
        return $this->done();
    }
}
