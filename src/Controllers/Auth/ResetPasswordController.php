<?php

namespace hpsynapse\moduser\Controllers\Auth;

use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;

use hpsynapse\moduser\Facades\UserRepo;
use Illuminate\Support\Facades\Storage;
use App\Facades\Tenant;

use App\Base\BaseController;

class ResetPasswordController extends BaseController
{

    use AuthResponseTraits;

    public function __construct()
    {
        $this->middleware('guest');//->except('logout');
    }

    /**
     * WEB - GET /auth/resetpassword
     * 
     * Halaman / Form reset password (dari link reset password)
     */
    public function resetPassword(Request $request, $apps_code = '')
    {
        $data['email'] = $request->query('email');
        $data['verifyCode'] = $request->query('verifyCode');
        $data['setNewPassword'] = $request->query('setNewPassword',false);
        $data['coop_name'] = config('tenant.name',config('AppConfig.system.template.frontend.title','Synapse'));
        $data['failed'] = false;
        $data['expired'] = false;

        $data['logo'] = asset('assets/images/koperasi_logo.png');
        if(($logo = config('tenant.instance_data.logo')) && isset($logo[0])){
            $data['logo'] = Tenant::storage()->url($logo[0]['filepath']);
        }

        // pastikan kode verifikasi sesuai, jika tidak maka tolak
        if(UserRepo::varifyResetPasswordToken($data['email'],$data['verifyCode'])){
            $data['user'] = UserRepo::getUser(['email',$data['email']]);
        }else{
            $data['failed'] = true;
        }

        return view('auth.resetpassword', $data);
    }

    /**
     * WEB - POST - /auth/resetpassword
     * post data password baru dari form reset password
     */
    public function doResetPassword(Request $request, $apps_code = '')
    {
        $data['verifyCode'] = $request->input('verifyCode');
        $data['email'] = $request->input('email');  
        $data['password'] = $request->input('password');  
        $data['password_confirmation'] = $request->input('password_confirmation');  
        $data['is_success'] = true;
        $data['coop_name'] = config('tenant.name',config('AppConfig.system.template.frontend.title','Synapse'));
        
        $data['logo'] = asset('assets/images/koperasi_logo.png');
        if(($logo = config('tenant.instance_data.logo')) && isset($logo[0])){
            $data['logo'] = Tenant::storage()->url($logo[0]['filepath']);
        }

        // if(!$request->validate([
        //     'email' => 'required|email|max:255',
        //     'password' => 'required|min:5|max:255',
        //     'password_confirmation' => 'required|min:5|max:255|same:password',
        // ])){
        //     $this->setAlert('Akses ditolak','danger'); 
        //     // return redirect()->route('resetPassword.fail', ['error_message'=>UserRepo::error()]);
        // }
        
        $validator = \Validator::make($data, [
            'email' => ['required', 'email:rfc', 'max:255', 'not_regex:/[\r\n]/'],
            'password' => 'required|min:8|max:255',
            'password_confirmation' => 'required|min:8|max:255|same:password',
        ]);

        if ($validator->fails()) {
            $this->setError('Reset password failed : ', $validator->messages()->messages());
            return redirect()->route('resetPassword', ['verifyCode'=>$data['verifyCode'],'email'=>$data['email']]);
        }
        
        //jika verified
        if(UserRepo::varifyResetPasswordToken($data['email'],$data['verifyCode'],true)){
            $user = UserRepo::getUser(['email',$data['email']]);
            
            //jika banned
            if($user['status']==2){
                // return redirect()->route('login')->with('alert', ['type' => 'danger', 'message' => 'Login Failed. Account Banned.']);
                $data['is_success'] = false;
                $data['message'] = 'Login Failed. Account Banned.';
            }else {
                //jika aktifasi pertama kali
                if($user['status']==0)
                    UserRepo::updateUser($user['id'],['status'=>1]);
                
                UserRepo::resetPassword($user['id'],$request->input('password'));
            }
        }else{
            // return redirect()->route('resetPassword.fail', ['error_message'=>UserRepo::error()]);
            $data['is_success'] = false;
            $data['message'] = UserRepo::error();
        }
        
        return view('auth.resetpasswordalert', $data);
        // return redirect()->route('resetPassword.fail', ['error_message'=>UserRepo::error()]);
        // return redirect()->route('login');
    }
    
    public function verifyFail(Request $request, $apps_code = '')
    {
        return view('auth.resetpasswordfail', $request->all());
    }

}
