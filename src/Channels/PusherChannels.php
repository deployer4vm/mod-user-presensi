<?php

namespace hpsynapse\moduser\Channels;

use Illuminate\Notifications\Notification;

use Pusher\Pusher;

class PusherChannels
{
    /**
     * Send the given notification. 
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     *
     * @throws missingRecipient
     */
    public function send($notifiable, Notification $notification)
    {
        $notifMessage = $notification->toPusher($notifiable);
        
        $pusher = new Pusher(
            config('broadcasting.connections.pusher.key'), 
            config('broadcasting.connections.pusher.secret'), 
            config('broadcasting.connections.pusher.app_id'), 
            ['cluster' => config('broadcasting.connections.pusher.options.cluster')]
        );
        
        $channel = $notifMessage['channel'];
        unset($notifMessage['channel']);
        $event = $notifMessage['event'];
        unset($notifMessage['event']);

        $pusher->trigger(
            $channel,//channel 
            $event, //event
            $notifMessage//data
        );

        return true;
    }
    
    /**
     * di class Notification-nya harus ada method ini
     */
    public function toPusher($notifiable)
    {
        return [
            'channel' => 'STRING',
            'event' => 'STRING',
            'notification' => [
                'title' => 'STRING',
                'body' => 'STRING',
            ],
            'data' => [
                'description' => 'STRING',
                'from' => [
                    'name' => 'STRING',
                    'icon' => 'STRING',
                ],
                'link_web' => [
                    'link' => '',
                    'route' => 'notification.detail',
                    'parameter' => [
                        'notifId' => 'STRING',
                    ]
                ],
                'link_apps' => 'STRING',
            ]  
        ];
    }

}