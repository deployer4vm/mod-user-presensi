<?php

namespace hpsynapse\moduser\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use hpsynapse\moduser\Controllers\Auth\ThrottlesLogins;
use Illuminate\Support\Facades\Log;

use hpsynapse\moduser\Facades\UserRepo;
use hpsynapse\moduser\Facades\UserNotifRepo;
use hpsynapse\moduser\Facades\RoleRepo;
use hpsynapse\moduser\Facades\UserLog;

use hpsynapse\moduser\Facades\UserAuth;
use hpsynapse\moduser\Facades\AuthConfig;

//use Carbon\Carbon;

use App\Base\BaseController;

class LoginController extends BaseController
{

    use ThrottlesLogins,
        AuthResponseTraits;

    public function __construct()
    {
        $this->middleware('guest')->except(['logout', 'revalidate']);
    }

    // public function username()
    // {
    //     return 'email';
    // }

    /**
     * halaman login WEB, baik SSO dan non SSO
     */
    public function login(Request $request)
    {
        $data['backlink'] = $request->input('backlink');
        $data['registerEnabled'] = AuthConfig::isSelfRegistrationEnabled();
        $data['forgotpasswordEnabled'] = AuthConfig::isLoginForgotPasswordEnabled();
        $data['remembermeEnabled'] = AuthConfig::isLoginRemembermeEnabled();

        return view('auth.login', $data);
    }

    /**
     * halaman login WEB, baik SSO dan non SSO
     */
    public function doLogin(Request $request)
    {
        $returnParam = [];
        $returnParam['backlink'] = $response['backlink'] = $request->input('backlink', '');

        // jika menyertakan appCode berarti SSO
        if ($request->route('appCode')) {
            $returnParam['appCode'] = $request->route('appCode');
            // get app Data
            // ...
            $appData = ['url_home' => '', 'url_get_session' => ''];

            $backLink = $appData['url_get_session']; // ke halaman get session applikasi menggunakan SSO ini
        } else {
            $backLink = $request->input('backlink', route('dashboard'));
        }


        $response['reff'] = 'login';

        $request->validate([
            'username' => 'required|max:255',
            'password' => 'required|min:3|max:255'
        ]);

        $authData = $request->only('username', 'password');
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            Log::info('Login Failed ! user : "' . $authData['username'] . '" - password : "' . $authData['password'] . '"');
            Log::info('Too many login attemp');
            return $this->sendLockoutResponse($request);
        }

        $tenantId = config('tenant.id');
        if (config('AppConfig.system.multitenant.autodetect_login') == 1) $tenantId = null;

        if ($user = UserRepo::loginCheck($authData['username'], $authData['password'], $tenantId)) {
            if (Auth::attempt(
                ['username' => $user['username'], 'password' => $authData['password']],
                $request->filled('remember')
            ) || Auth::attempt(
                ['email' => $user['email'], 'password' => $authData['password']],
                $request->filled('remember')
            )) {

                //$request->session()->regenerate();
                $this->clearLoginAttempts($request);
                $userData = Auth::user();

                //jika di banned
                if ($userData->status == 2) {
                    return redirect()->route('auth.login', $returnParam)->with('alert', ['type' => 'danger', 'message' => __('auth.login.alert.user_banned')]);
                    //jika pertama kali aktifikasi
                } else if ($userData->status == 0) {
                    UserRepo::updateUser($userData->id, ['status' => 1]);
                    UserRepo::activateUser($userData->id);
                }

                UserAuth::setUser($userData->id);

                $response['isLogin'] = 1;
                $response['token'] = UserAuth::getToken();

                return redirect()->away($backLink . '?' . http_build_query($response));
            }
        }

        $this->incrementLoginAttempts($request);

        return redirect()->route('auth.login', $returnParam)->with('alert', ['type' => 'warning', 'message' => 'Login Failed.']);
    }

    public function logout(Request $request)
    {

        UserAuth::unsetUser();
        Auth::logout();

        //$request->session()->invalidate();
        $response['backlink'] = $request->input('backlink') ? $request->input('backlink') : config('cur_apps.home_url');
        $response['reff'] = 'logout';
        $response['isLogin'] = 0;

        return $this->authDone($response, route('auth.login'));
    }

    /**
     * halaman yang diakses pertama oleh applikasi pengguna SSO untuk detect session
     * user saat ini
     */
    public function revalidate(Request $request)
    {
        $returnParam = [];
        $returnParam['backlink'] = $response['backlink'] = $request->input('backlink', '');

        if ($request->route('appCode')) {
            $returnParam['appCode'] = $request->route('appCode');
            // get app Data
            // ...
            $appData = ['url_home' => '', 'url_get_session' => ''];

            $backLink = $appData['url_get_session']; // ke halaman get session applikasi menggunakan SSO ini
        } else {
            // jika tidak menyertakan appCode maka redirect ke halaman login jika sudah login 
            // dan ke home jika belum login
            $this->response = redirect();
        }

        return $this->done();
    }

    /**
     * API
     * =========================================================================
     */

    /**
     * API OUTPUT ONLY
     * create token user
     * 
     * @param Request $request
     *      auth            encrypted JSON string {"username":"username","password":"encrypted_password"}
     *                      jika menyertakan "auth" maka tidak perlu menyertakan username dan password
     * 
     *      username
     *      password
     *      role_code       *optional, string role code yg diset sebagai 
     *                      role code active di session ini
     * 
     * 
     *      deviceId
     *      pushNotifToken
     *      
     * @return Array default synapse api return
     *      data
     *          user                Array record
     *          tenant
     *          role                Array list role yg dimiliki user
     *          role_code           String main role code
     *          role_group          Array list role group yg dimiliki user
     *          role_group_code     String role group code yg aktif (sesuai role yg aktifnya)
     *          role_level_group    Array record role level group yg aktif
     * 
     *          token       string
     * 
     *          lastUpdate
     *          validUntil
     */
    public function apiLogin(Request $request)
    {
        $this->forceApiOutput();
        
        $authParam = $request->only('username', 'password','role_code', 'auth');

        
        // detek auto login, untuk auto login tidak perlu di masukan ke system blockir
        $notFromAuth = true;
        if(!empty($authParam['auth'])){            
            $tmpAuth = json_decode(UserAuth::decryptCredential($authParam['auth']),true);
            if (!isset($tmpAuth['username']) || !isset($tmpAuth['password'])) {
                $this->setError(__('alert.incorect_parameter'));
                $authParam['decrypted_auth'] = $tmpAuth;
                // login api failed
                UserLog::addLog(UserAuth::user('id'), 'user_auth', 'api_login_failed', [
                    'ip'=>request()->ip(),
                    'error'=>'incorect_parameter',
                    'params'=>$authParam,
                ]);
                return $this->done();
            }
            $authParam['username'] = $tmpAuth['username'];
            $authParam['password'] = $tmpAuth['password'];
            $notFromAuth = false;
        }else if (!isset($authParam['username']) || !isset($authParam['password'])) {
            $this->setError(__('alert.incorect_parameter'));
            // login api failed
            UserLog::addLog(UserAuth::user('id'), 'user_auth', 'api_login_failed', [
                'ip'=>request()->ip(),
                'error'=>'incorect_parameter',
                'params'=>$authParam,
            ]);
            return $this->done();
        }

        $authParam['password'] = UserAuth::decryptCredential($authParam['password']);

        $tenantId = config('tenant.id');
        if (config('AppConfig.system.multitenant.autodetect_login') == 1)
            $tenantId = null;

        // jika selain auto login maka cek blocking system
        if($notFromAuth){
            // system user login check
            $userLogin = UserRepo::systemUserLoginCheck($authParam['username']);
            // jika sudah overlimit maka block
            if ($userLogin['count'] > 5 || $userLogin['status'] == 1) {
                // update system user login
                $countUserLogin = $userLogin['count'] + 1;
                $descriptionUserLogin = $userLogin['description'];
                $descriptionUserLogin['message_'.$countUserLogin] = __('auth.login.alert.login_blocked');
                $userLogin->where('status', 0)->update([
                    'status' => 1,
                    'count' => $countUserLogin,
                    'description' => $descriptionUserLogin
                ]);
                // login api failed
                UserLog::addLog(UserAuth::user('id'), 'user_auth', 'api_login_failed', [
                    'ip'=>request()->ip(),
                    'error'=>'ip_blocked',
                    'params'=> $authParam,
                ]);
                $this->setError(__('auth.login.alert.login_blocked'));
                return $this->done();
            }
        }

        if ($user = UserRepo::loginCheck($authParam['username'], $authParam['password'], $tenantId)) {
            $pushParam = false;
            if ($request->input('pushNotifToken')) {
                $pushParam = [
                    'token' => $request->input('pushNotifToken'),
                    'type' => $request->input('pushType', 1)
                ];
            }
            $this->output['message'] = __('alert.auth_success');
            $this->output['data'] = UserAuth::getCurTimeStamp();

            $this->output['data']['user'] = $user;

            if (isset($user['tenant']))
                $this->output['data']['tenant'] = $user['tenant'];

            $this->output['data']['role'] = UserRepo::listUserRole($user['id']);
            $this->output['data']['role_group'] = [];
            $this->output['data']['role_group_code'] = '';
            // get role code utama
            foreach ($this->output['data']['role'] as $key => $val) {
                if($val['role_group']){
                    $this->output['data']['role_group'][$val['role_group']['code']] = $val['role_group'];
                }

                if ($val['is_main_role']) {
                    $this->output['data']['role_code'] = $key;
                }
            }

            // jika set role code
            if(!empty($authParam['role_code']) && isset($this->output['data']['role'][$authParam['role_code']])){
                $this->output['data']['role_code'] = $authParam['role_code'];
            }

            // jika main role tidak ada berarti ada yang salah di insert user ke databasenya
            if (!isset($this->output['data']['role_code'])) {
                $this->setError(__('alert.auth_failed') . '<br><i>Main Role</i> user tidak terdeteksi.');
                // login api failed
                UserLog::addLog(UserAuth::user('id'), 'user_auth', 'api_login_failed', [
                    'ip'=>request()->ip(),
                    'error'=>'main_role_not_defined',
                    'params'=>$authParam,
                ]);
                return $this->done();
            }

            // set aktif role code
            if($this->output['data']['role'][$this->output['data']['role_code']]['role_group'])
                $this->output['data']['role_group_code'] = $this->output['data']['role'][$this->output['data']['role_code']]['role_group']['code'];

            // $this->output['data']['role'] = UserRepo::listUserRole($user['id']);
            // generate token
            $token = UserRepo::generateToken($user['id'], $this->output['data']['role_code'], 0, $request->input('deviceId', ''), $pushParam);
            $this->output['data']['token'] = $token['api_token'];

            $this->output['data']['role_level_group'] = UserRepo::getRoleLevelGroup(
                ['level'=>$this->output['data']['role'][$this->output['data']['role_code']]['level']]
            );

            //subscribekan ke channel/topic berdasarkan user role nya
            if ($request->input('pushNotifToken')) {
                $notifChannel[] = 'all';
                // if($response['data']['userData']['is_admin'])$notifChannel[] = 'admin';

                UserNotifRepo::subscribeToChannel($notifChannel, $pushParam['token']);
            }
            // UserAuth::setUser($user['id'],$token['api_token']);
            // jika selain auto login maka cek blocking system
            if($notFromAuth){
                // update system user login
                $countUserLogin = $userLogin['count'] + 1;
                $descriptionUserLogin = $userLogin['description'];
                $descriptionUserLogin['message_'.$countUserLogin] = __('alert.auth_success');
                $userLogin->update([
                    'status' => 2,
                    'description' => $descriptionUserLogin
                ]);
            }
            // api success
            UserLog::addLog($user['id'], 'user_auth', 'api_login_success', [
                'ip' => request()->ip(),
                'system_user_login_id' => $userLogin['id']
            ]);
            return $this->done();
        }

        // jika selain auto login maka cek blocking system
        if($notFromAuth){
            // update system user login
            $countUserLogin = $userLogin['count'] + 1;
            $descriptionUserLogin = $userLogin['description'];
            $descriptionUserLogin['message_'.$countUserLogin] = UserRepo::errorFull();
            $userLogin->update([
                'count' => $countUserLogin,
                'description' => $descriptionUserLogin
            ]);
        }

        UserLog::addLog(UserAuth::user('id'), 'user_auth', 'api_login_failed', [
            'ip'=>request()->ip(),
            'error'=>'credentials_failed',
            'error_message'=>UserRepo::errorFull(),
            'params'=>$authParam,
        ]);

        // $this->setError(__('alert.auth_failed'));
        $this->setError(UserRepo::errorFull());
        return $this->done();
    }

    /**
     * 
     * @param Request $request
     */
    public function apiLogout(Request $request)
    {
    }
}
