<?php

namespace hpsynapse\moduser\Services;

use App\Facades\DbConfig;

class AuthConfig
{
    /**
     * REGISTRATION
     * =========================================================================
     */

    public function isSelfRegistrationEnabled()
    {
        $enableRegistrasi = config('AppConfig.packageLocal.moduser.registration.enable', 0);
        if($enableRegistrasi)
            $enableRegistrasi = DbConfig::getConfig('moduser_registration','enable',$enableRegistrasi);
        return $enableRegistrasi==1?true:false;
    }

    public function isSelfRegistrationAutoActivate()
    {
        $autoActivate = config('AppConfig.packageLocal.moduser.registration.auto_activate', 1);
        if($autoActivate)
            $autoActivate = DbConfig::getConfig('moduser_registration','auto_activate',$autoActivate);

        return $autoActivate==1?true:false;
    }
    
    public function isSelfRegistrationTosConfirm()
    {
        $needTosConfirm = config('AppConfig.packageLocal.moduser.registration.tos_confirm', 0);
        if($needTosConfirm)
            $needTosConfirm = DbConfig::getConfig('moduser_registration','tos_confirm',$needTosConfirm);

        return $needTosConfirm==1?true:false;
    }
    
    public function isEmailActivationEnabled()
    {
        $sendActivationEmail = config('AppConfig.packageLocal.moduser.registration.admin_send_activation_email', 1);
        if($sendActivationEmail)
            $sendActivationEmail = DbConfig::getConfig('moduser_registration','admin_send_activation_email',$sendActivationEmail);

        return $sendActivationEmail==1?true:false;
    }

    
    public function getRegistrationDefaultRoleCode()
    {
        return DbConfig::getConfig('moduser_registration','default_role_code',config('AppConfig.packageLocal.moduser.registration.default_role_code', 'admin'));
    }
    

    /**
     * LOGIN
     * =========================================================================
     */

    /**
     * apakah fitur remember me saat login aktif
     */
    public function isLoginRemembermeEnabled()
    {
        $access = config('AppConfig.packageLocal.moduser.auth.login.rememberme', 0);
        if($access)
            $access = DbConfig::getConfig('moduser_auth','login_rememberme',$access);

        return $access==1?true:false;
    }

    /**
     * apakah fitur remember me saat login aktif
     */
    public function isLoginForgotPasswordEnabled()
    {
        $access = config('AppConfig.packageLocal.moduser.auth.login.forgotpassword', 0);
        if($access)
            $access = DbConfig::getConfig('moduser_auth','login_forgotpassword',$access);

        return $access==1?true:false;
    }

    /**
     * PASSWORD
     * =========================================================================
     */

    public function passwordMinlength()
    {
        return DbConfig::getConfig('moduser_auth','password_minlength',8);
    }

    /**
     * OTP
     * =========================================================================
     */

    /**
     * apakah fitur OTP aktif
     */
    public function isOTPEnabled()
    {
        $access = config('AppConfig.packageLocal.moduser.auth.otp.enable', 1);
        if($access)
            $access = DbConfig::getConfig('moduser_auth','otp',$access);

        return $access==1?true:false;
    }

    /**
     * Get jumlah digit OTP
     */
    public function OTPDigit()
    {
        return DbConfig::getConfig('moduser_auth','otp_digit',8);
    }

    
    /**
     * Masa aktif otp (dalam menit)
     */
    public function OTPTimeout()
    {
        return DbConfig::getConfig('moduser_auth','otp_timeout',5);
    }
    
    public function isOTPEmailChannelEnable()
    {
        $access = config('AppConfig.packageLocal.moduser.auth.otp.enable_channel.email', 1);
        if($access)
            $access = DbConfig::getConfig('moduser_auth','otp_channel_email',$access);

        return $access==1?true:false;
    }
    
    public function isOTPSmsChannelEnable()
    {
        $access = config('AppConfig.packageLocal.moduser.auth.otp.enable_channel.sms', 0);
        if($access)
            $access = DbConfig::getConfig('moduser_auth','otp_channel_sms',$access);

        return $access==1?true:false;
    }

    public function isOTPWaChannelEnable()
    {
        $access = config('AppConfig.packageLocal.moduser.auth.otp.enable_channel.wa', 0);
        if($access)
            $access = DbConfig::getConfig('moduser_auth','otp_channel_wa',$access);

        return $access==1?true:false;
    }

    /**
     * PIN
     * =========================================================================
     */

    /**
     * apakah fitur PIN aktif
     */
    public function isPINEnabled()
    {
        $pin = config('AppConfig.packageLocal.moduser.auth.pin.enable', 1);
        if($pin)
            $pin = DbConfig::getConfig('moduser_auth','pin',$pin);

        return $pin==1?true:false;
    }
    
    /**
     * Get jumlah digit PIN
     */
    public function PINDigit()
    {
        return DbConfig::getConfig('moduser_auth','pin_digit',6);
    }
}