<?php

namespace hpsynapse\moduser\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SAMPLEMAIL extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;
    
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(config('mail.mailers.smtp.username'), env('APP_NAME'))
            ->subject('SMARTLMS')
            ->view('user.emails.testemail');
    }

}
