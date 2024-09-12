<?php

namespace hpsynapse\moduser\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

use hpsynapse\moduser\Channels\FirebaseChannels;
use hpsynapse\moduser\Channels\AwsSNSChannels;
use hpsynapse\moduser\Channels\PusherChannels;
use hpsynapse\moduser\Channels\SmsChannels;
use hpsynapse\moduser\Channels\DbChannels;
use hpsynapse\moduser\Facades\UserAuth;

/**
 * broad cast message dari admin ke member
 */
class WebdevMessage extends Notification implements ShouldQueue
{
    use Queueable;
    public $title, $body, $data, $tenantId;

    /**
     * Create a new notification instance.
     *
     * @return void
     * 
     * Data :
     * Event terbaru
     * Link event
     * 
     */
    public function __construct(string $title, string $body, array $data = [])
    {
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;        
        $this->tenantId = config('tenant.id',0);        
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $config = [DbChannels::class];

        if(config('AppConfig.packageLocal.moduser.notification.services.mail.enable',1))
            $config[] = 'mail';
        
        if(config('AppConfig.packageLocal.moduser.notification.services.sms.enable',1))
            $config[] = SmsChannels::class;

        if(config('AppConfig.packageLocal.moduser.notification.services.firebase.enable',1)){
            $config[] = FirebaseChannels::class;
        }else if(config('AppConfig.packageLocal.moduser.notification.services.pusher.enable',1)){
            $config[] = PusherChannels::class;
        }else if(config('AppConfig.packageLocal.moduser.notification.services.aws_sns.enable',0)){
            $config[] = AwsSNSChannels::class;
        }

        return $config;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $email = new \hpsynapse\moduser\Mail\AdminMessage($this->title, $this->body);
        return $email->to($notifiable->email);
    }

    public function toFirebase($notifiable)
    {
        $toFirebase = [
            'notification' => [
                'title' => $this->title,
                'body' => $this->body
            ],
            'data' => $this->data
        ];

        if (isset($this->data['token'])) {
            $toFirebase['token'] = $this->data['token'];
        }

        if (isset($this->data['topic'])) {
            $toFirebase['topic'] = $this->data['topic'];
        }

        return $toFirebase;
    }
    
    public function toPusher($notifiable)
    {
        return [
            'channel' => 'Notification.User.'. UserAuth::user('id'),
            'event' => 'notification',
            'notification' => [
                'title' => $this->title,
                'body' => $this->body
            ],
            'data' => [
                'description' => isset($this->data['description'])?$this->data['description']:'',
                'from' => [
                    'name' => isset($this->data['from']['name'])?$this->data['from']['name']:'',
                    'icon' => isset($this->data['from']['icon'])?$this->data['from']['icon']:''
                ],
                'link_web' => [
                    'link' => '',
                    'route' => 'notification.detail',
                    'parameter' => [
                        'notifId' => $this->id
                    ]
                ],
                'link_apps' => isset($this->data['link_apps'])?$this->data['link_apps']:'',
            ]  
        ];
    }

    /**
     * Get the database array format representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return $this->toArray($notifiable);
    }
    
    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'subject' => $this->title,
            'description' => isset($this->data['description'])?$this->data['description']:'',
            'body' => $this->body,
            'from' => [
                'name' => isset($this->data['from']['name'])?$this->data['from']['name']:'',
                'icon' => isset($this->data['from']['icon'])?$this->data['from']['icon']:''
            ],
            'link_web' => [
                'link' => '',
                'route' => 'notification.detail',
                'parameter' => [
                    'notifId' => $this->id
                ]
            ],
            'link_apps' => isset($this->data['link_apps'])?$this->data['link_apps']:''
        ];
    }
}
