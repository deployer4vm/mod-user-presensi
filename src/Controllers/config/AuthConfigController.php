<?php

namespace hpsynapse\moduser\Controllers\config;

// 1. Import level PHP

// 2. Import level Package Composer

// 3. Import level Laravel Core
use Illuminate\Http\Request;

// 4. Import level Synapse Core
use App\Base\BaseController;

// 5. Import level Synapse Module Package

// 6. Import level Synapse MainApp & Module MainApp

// 7. Import level Synapse - Current Module
use hpsynapse\moduser\Facades\AuthConfig;

/**
 * Untuk return config-config user yg editable, diread via API
 * 
 * @SuppressWarnings(PHPMD.ShortMethodNames)
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class AuthConfigController extends BaseController
{

    /**
     * GET - /api/user/auth-config/
     * 
     * get semua auth config
     *
     */
    public function index()
    {

        $this->setData([
            'registration'=>$this->getRegistrationConfig(),
            'login'=>$this->getLoginConfig(),
            'password'=>$this->getPasswordConfig(),
            'otp'=>$this->getOTPConfig(),
            'pin'=>$this->getPINConfig()
        ]);

        return $this->done();
    }

    /**
     * GET - /api/user/auth-config/registration
     * 
     * get config login
     *
     */
    public function registrationConfig()
    {
        $this->setData($this->getRegistrationConfig());
        return $this->done();
    }

    /**
     * GET - /api/user/auth-config/login
     * 
     * get config login
     *
     */
    public function loginConfig()
    {
        $this->setData($this->getLoginConfig());
        return $this->done();
    }

    /**
     * GET - /api/user/auth-config/password
     * 
     * get config password
     *
     */
    public function passwordConfig()
    {
        $this->setData($this->getPasswordConfig());
        return $this->done();
    }
    /**
     * GET - /api/user/auth-config/otp
     * 
     * get config otp
     *
     */
    public function otpConfig()
    {
        $this->setData($this->getOTPConfig());
        return $this->done();
    }
    

    /**
     * GET - /api/user/auth-config/pin
     * 
     * get config otp
     *
     */
    public function pinConfig()
    {
        $this->setData($this->getPINConfig());
        return $this->done();
    }

    private function getRegistrationConfig()
    {
        return [
            'enable'=>AuthConfig::isSelfRegistrationEnabled(),
            'auth_activate'=>AuthConfig::isSelfRegistrationAutoActivate(),
            'admin_send_activation_email'=>AuthConfig::isEmailActivationEnabled(),
            'tos_confirm'=>AuthConfig::isSelfRegistrationTosConfirm(),
            'default_role_code'=>AuthConfig::getRegistrationDefaultRoleCode(),
        ];
    }
    
    private function getLoginConfig()
    {
        return [
            'rememberme'=>AuthConfig::isLoginRemembermeEnabled(),
            'forgotpassword'=>AuthConfig::isLoginForgotPasswordEnabled(),
        ];
    }

    private function getPasswordConfig()
    {
        return [
            'minlength'=>AuthConfig::passwordMinlength()
        ];
    }
    private function getOTPConfig()
    {
        return [
            'enable'=>AuthConfig::isOTPEnabled(),
            'digit'=>AuthConfig::OTPDigit(),
            'timeout'=>AuthConfig::OTPTimeout(),
            'enable_channel'=>[
                'email'=>AuthConfig::isOTPEmailChannelEnable(),
                'sms'=>AuthConfig::isOTPSmsChannelEnable(),
                'wa'=>AuthConfig::isOTPWaChannelEnable(),
            ]
        ];
    }

    private function getPINConfig()
    {
        return [
            'enable'=>AuthConfig::isPINEnabled(),
            'digit'=>AuthConfig::PINDigit()
        ];
    }
}
