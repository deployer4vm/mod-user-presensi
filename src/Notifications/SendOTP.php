<?php

namespace hpsynapse\moduser\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;

use hpsynapse\moduser\Channels\SmsChannels;
use hpsynapse\moduser\Channels\WhatsAppChannels;

class SendOTP extends Notification implements ShouldQueue
{
    use Queueable;

    protected $otpcode,$appName;

    protected $primaryKey = 'user_id';
    public $incrementing = false;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($otpcode,$appName)
    {
        $this->otpcode = $otpcode;
        $this->appName = $appName;
        
        $this->queue = 'verification';
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {        
        // Log::info(['via channel',$notifiable->otp_channel,$notifiable]);
        // echo 'channel : '.$notifiable->otp_channel."\n\n";
        //1 email, 2 sms, 3 wa
        return [$notifiable->otp_channel==1?'mail':($notifiable->otp_channel==2?SmsChannels::class:WhatsAppChannels::class)];
    }

    /**
     *
     * @param  mixed  $notifiable
     */
    public function toSms($notifiable)
    {
        return [
            'message' => __('auth.profile.otp_sms.message', ['otp' => $this->otpcode,'appName' => $this->appName]),
            'phone' => $notifiable->phone
        ];
    }

    /**
     *
     * @param  mixed  $notifiable
     */
    public function toWhatsApp($notifiable)
    {
        return [
            'message' => __('auth.profile.otp_sms.message', ['otp' => $this->otpcode,'appName' => $this->appName]),
            'phone' => $notifiable->phone
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $email = new \hpsynapse\moduser\Mail\OTP(
            $this->otpcode,
            $this->appName
        );
        // echo 'email : '.$notifiable->email."\n\n";
        // Log::info(['toMail',$notifiable->email,$notifiable]);
        return $email->to($notifiable->email);
    }
}
