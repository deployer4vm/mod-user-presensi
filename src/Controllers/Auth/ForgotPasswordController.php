<?php

namespace hpsynapse\moduser\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use hpsynapse\moduser\Facades\UserRepo;

use App\Base\BaseController;

class ForgotPasswordController extends BaseController
{

    use AuthResponseTraits;
    
    // protected $responsableName = 'App\Modules\Auth\Responses\AuthResponse';

    public function __construct()
    {
        $this->middleware('guest');//->except('logout');
    }

    /**
     * WEB - halaman form forgot password
     */
    public function forgotPassword(Request $request, $apps_code = '')
    {
        $data['backlink'] = $request->query('backlink');
        return view('auth.forgotpassword', $data);
    }

    /**
     * WEB - POST - \auth\forgotpassword
     * API - POST - \api\auth\forgotpassword
     * 
     * submit email
     * 
     * @param Request $request
     * @param type $apps_code
     * @return type
     */
    public function doForgotPassword(Request $request, $apps_code = '')
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255', 'not_regex:/[\r\n]/'],
        ]);
        $this->output['data']['email'] = $validated['email'];
        $this->response = 'auth.forgotpasswordsuccess';
        
        $user = UserRepo::getUser(['email',$this->output['data']['email']]);
        
        if($user && $user['status'] == 1){
            UserRepo::sendUserResetPasswordEmail($user['id']);
        }

        $this->output['message'] = __('auth.forgotpassword.alert.forgot_password_success');
        return $this->done();
    }

}
