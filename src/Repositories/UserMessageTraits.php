<?php
namespace hpsynapse\moduser\Repositories;

use hpsynapse\moduser\Facades\AuthConfig;
use hpsynapse\moduser\Models\NotificationChannel;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use hpsynapse\moduser\Models\User;
use hpsynapse\moduser\Models\UserOTP;
use hpsynapse\moduser\Models\PasswordReset;
use hpsynapse\moduser\Models\UserProfile;

trait UserMessageTraits     
{
    public function __construct(User $model)
    {
        $this->model = $model;        
    }

    /**
     * kirim email mengenai error atau waring ke seluruh super admin
     * 
     * @param string $title
     * @param mix $content
     * @return type
     */
    public function notifyWarning($title,$content)
    {
        //pilih semua level user system admin
        $users = User::where('role','LIKE',"%;1:%")->get();
        foreach ($users as $user) {
            $user->notify($user['id'], new \hpsynapse\moduser\Notifications\WarningReport($title,$content));
        }
        return true;
    }
    
    public function sendEmail($userId,$email)
    {        
        $user = User::find($userId);
        if(!$user)return false;
        Mail::to($user->email)->send($email);
        return true;
    }
    
    public function isMainEmail($userId,$email)
    {
        $user = User::find($userId);
        if(!$user)return false;        
        if($user->email == $email)return true;
        return false;
    }
    /**
     * =========================================================================
     */
    /**
     * 
     * @param type $userId
     * @param type $isSecondary
     * @return type
     */
    public function sendVerificationEmail($userId,$isSecondary=false)
    {
        return $this->notify(
            $userId,
            new \hpsynapse\moduser\Notifications\EmailVerification(
                $userId,
                $isSecondary,
                config('AppConfig.client.app_name'),
                Request::getHost(),
                // url(config('AppConfig.endpoint.home'))
                url('/')
            )
        );
    }    
    
    
    public function sendUserActivationEmail($userId)
    {
        return $this->notify(
            $userId,
            new \hpsynapse\moduser\Notifications\UserActivation(
                $userId,
                false,
                config('AppConfig.client.app_name'),
                Request::getHost(),
                // url(config('AppConfig.endpoint.home'))
                url('/')
            )
        );
    }

    /**
     * format data yg diperlukan untuk email verifikasi dan activasi
     * 
     * @param type $userId
     * @param type $isSecondary
     * 
     * @return array $userData array record data user
     *      name
     *      email
     *      verifyCode
     */
    public function verificationEmailDataFormat($userId,$isSecondary=false)
    {
        $user = User::find($userId);
        if(!$user)return false; 
        $userData = $user->toArray();
                
        if($isSecondary){
            $userProfile = UserProfile::where('user_id',$userId)->first();
            if($userProfile)return false; 
            $userData['email'] = $userProfile->email2;
        }
        
        $userData['verifyCode'] = Str::random(64);
        $userData['verifyUrl'] = route('auth.emailVerification',[
            'email' => $userData['email'],
            'verifyCode' => $userData['verifyCode']
            ]);
        
        return $userData;
    }
    
    public function sendUserResetPasswordEmail($userId)
    {
        return $this->notify(
            $userId,
            new \hpsynapse\moduser\Notifications\UserResetPassword(
                $userId,
                config('AppConfig.client.app_name'),
                Request::getHost(),
                // url(config('AppConfig.endpoint.home'))
                url('/')
            ) 
        );
    }
    
    public function resetPasswordEmailDataFormat($userId)
    {        
        $user = User::find($userId);
        if(!$user)return false; 
        $userData = $user->toArray();
        
        $userData['verifyCode'] = $this->generateEmailVerfifyCode($userData['email']);
        
        $userData['resetPasswordUrl'] = route('resetPassword',[
            'email' => $userData['email'],
            'verifyCode' => $userData['verifyCode']
        ]);

        $modelEmail = PasswordReset::where('tenant_id',config('tenant.id',0))
            ->where('email',$userData['email']);

        if($modelEmail->exists()){
            $modelEmail->update([
                'token' => hash('sha256', $userData['verifyCode']),
                'created_at' => now()         
            ]);
        }else{
            PasswordReset::create([
                'tenant_id' => config('tenant.id',0),
                'email' => $userData['email'],
                'token' => hash('sha256', $userData['verifyCode'])
            ]);

        }

        return $userData;
    }

    public function generateEmailVerfifyCode($email)
    {
        return hash_hmac('sha256', strtolower($email), (string) config('app.key'));
    }
    
    /**
     * OTP 
     * =========================================================================
     */
    
    /**
     * generate dan kirim kode OTP ke not
     * 
     * @param int $userId
     * @param int $digit jumlah digit
     * @param int $tenantId
     * @return boolean
     */
    public function sendOTP($userId,int $digit=0,$tenantId=0)
    {        
        $tenantId = $tenantId?$tenantId:config('tenant.id',0);
        $digit = empty($digit)?AuthConfig::OTPDigit():$digit;

        $user = User::find($userId);
        if(!$user){
            $this->error = 'User not found';
            return false;
        }

        if(empty($user->otp_channel)){
            $this->error = 'Channel OTP belum dipilih, silahkan set terlebih dahulu dari menu Profile.';
            return false;
        }else if($user->otp_channel==1 && empty($user->email)){
            $this->error = 'Email belum diset.';
            return false;
        }else if(($user->otp_channel==2||$user->otp_channel==3) && empty($user->phone)){
            $this->error = 'Nomor telepon belum diset.';
            return false;
        }

        if(!($otp = $this->generateOTP($user->id,$digit,$tenantId))){
            return false;
        }
        
        //notifyNow
        $user->notifyNow(new \hpsynapse\moduser\Notifications\SendOTP($otp['otp'],config('AppConfig.client.app_name','')));
        
        return $otp['timeout'];
    }
    
    /**
     * generate dan save kode OTP table, jika sebelum telah ada maka akan dihapus
     * terlebih dahulu
     * 
     * @param int $digit 1-8
     * @param int $userId
     * @param string $phone
     * @return type
     */
    public function generateOTP($userId,int $digit=0,$tenantId=0)
    {
        $tenantId = $tenantId?$tenantId:config('tenant.id',0);
        $digit = empty($digit)?AuthConfig::OTPDigit():$digit;

        $user = User::find($userId);
        if(!$user){
            $this->error = 'User not found';
            return false;
        }

        if($user->otp_channel==1) {
            if(empty($user->email)){
                $this->error = 'Email belum diset';
                return false;
            }else{
                $recipient = $user->email;
            }
        }else if($user->otp_channel==2 || $user->otp_channel==3){
            if(empty($user->phone)){
                $this->error = 'Nomor telepon belum diset';
                return false;
            }else{
                $recipient = $user->phone;
            }
        }else{
            $this->error = 'Channel pengiriman OTP belum diset';
            return false;
        }

        // delete otp lama jika ada
        UserOTP::where('user_id',$userId)->delete();

        $maxOtp = (int) str_repeat('9', $digit);
        $otpCode = str_pad((string) random_int(0, $maxOtp), $digit, '0', STR_PAD_LEFT);
        $timeout = now()->addMinutes(AuthConfig::OTPTimeout());

        UserOTP::create([
            'tenant_id' => config('tenant.id',0),
            'user_id' => $userId,
            'token' => Hash::make($otpCode),
            'channel' => $user->otp_channel,
            'recipient' => $recipient,
            'timeout' => $timeout
        ]);

        return [
            'otp'=>$otpCode,
            'timeout'=>$timeout
        ];
    }

    /**
     * Cek apakah otp valid, jika valid true dan kode otp langsung dihapus
     * 
     * @param type $userId
     * @param type $otpCode
     * @return boolean
     */
    public function isOTPValid($userId,$otpCode,$tenantId=0)
    {
        $tenantId = $tenantId?$tenantId:config('tenant.id',0);
    
        $otpData = UserOTP::where('user_id',$userId)
                ->where('tenant_id',$tenantId)
                ->latest('created_at')
                ->first();
        
        //jika otp valid
        if($otpData){
            UserOTP::where('user_id',$userId) 
                ->where('tenant_id',$tenantId)->delete();
            // cek apakah expired
            if(now()->format('Y-m-d H:i:s') > $otpData->timeout){
                $this->error = 'Invalid OTP';
                return false;
            }
            return Hash::check((string) $otpCode, $otpData->token);
        }

        $this->error = 'Invalid OTP';
        return false;
    }
    
    /**
     * cek apakah telepon merupakan telepon utama dan ada
     * 
     * @param type $userId
     * @param type $phone
     * @return boolean
     */
    // public function isMainPhone($userId,$phone)
    // {
    //     $phone = UserRepo::phoneFormat($phone);
    //     $user = User::find($userId);
    //     if(!$user)return false;        
    //     if($user->phone == $phone)return true;
    //     return false;
    // }
    
    /**
     * Notifikasi
     * =========================================================================
     */
    
    /**
     * send notifkasi 
     * 
     * @param type $userId
     * @param Notification $notification
     * @return type
     */
    public function notify($userId,Notification $notification)
    {
        $user = User::find($userId);
        if($user)return $user->notify($notification);
        return false;
    }

    public function sendAdminMessage($userId,$title,$message,$data)
    {
        return $this->notify(
            $userId, 
            new \hpsynapse\moduser\Notifications\AdminMessage($title,$message,$data)
        );
    }
    
    /**
     * broadcast notifikasi ke channel / topic
     * 
     * @param string $channel / topic
     * @param Notification $notification
     */
    public function broadcast(string $channel, Notification $notification)
    {
        try {
            $listNotificationChannel = NotificationChannel::where('channel', $channel)->get();

            if ($listNotificationChannel->isEmpty()) {
                return false;
            }

            foreach ($listNotificationChannel as $notifChannel) {
                $user = User::find($notifChannel->user_id);
                if ($user) {
                    $user->notify($notification);
                }
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
}
