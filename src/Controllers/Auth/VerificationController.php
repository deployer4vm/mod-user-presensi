<?php

namespace hpsynapse\moduser\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use hpsynapse\moduser\Facades\UserRepo;
use App\Base\BaseController;

class VerificationController extends BaseController
{

    /**
     * WEB - GET /auth/emailverify
     * Link verifikasi email
     */
    public function verify(Request $request)
    {
        $data['verifyCode'] = $request->query('verifyCode');
        $data['email'] = $request->query('email');
                        
        //jika verified
        if(!UserRepo::varifyEmail($data['email'],$data['verifyCode'])){
            return redirect()->route('auth.emailVerification.fail', ['error_message'=>UserRepo::error()]);
        }
        
        return redirect()->route('auth.emailVerification.success');        
    }
    
    /**
     * WEB - GET /auth/emailverify/success
     * redirect saat verifikasi berhasil
     */
    public function verifySuccess(Request $request)
    {
        return view('user.auth.emailvalidatesuccess', $request->all());
    }
    
    /**
     * WEB - GET /auth/emailverify/fail
     * redirect saat verifikasi gagal
     */
    public function verifyFail(Request $request)
    {
        return view('user.auth.emailvalidatefail', $request->all());
    }
    

}
