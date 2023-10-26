<?php

namespace hpsynapse\moduser\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use hpsynapse\moduser\Facades\UserRepo;

class OTP extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    
    protected $otp,$appName;
    
    
    /**
     * Create a new message instance.
     * 
     * @param type $otp
     * @param type $isSecondary
     */
    public function __construct($otp,$appName)
    {
        $this->otp = $otp;
        $this->appName = $appName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(__('auth.profile.otp_email.subject',['appName'=>$this->appName]))
            ->view('user.emails.otp')
            ->with(['otp' => $this->otp]);
    }
}
