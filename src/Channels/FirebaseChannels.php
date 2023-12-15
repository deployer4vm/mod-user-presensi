<?php

namespace hpsynapse\moduser\Channels;

use hpsynapse\moduser\Facades\FirebaseApi;

class FirebaseChannels
{
    /**
     * Send the given notification. 
     * 
     * @param mixed $notifiable
     * @param mixed $notification
     * 
     * @return bool
     */
    public function send($notifiable, $notification)
    {
        $notifMessage = $notification->toFirebase($notifiable);

        if (isset($notifMessage['topic'])) {
            FirebaseApi::sendMessageToTopic($notifMessage['topic'], $notifMessage);
        } elseif (isset($notifMessage['token'])) {
            FirebaseApi::sendMessageToToken($notifMessage['token'], $notifMessage);            
        } else {
            
        }

        return true;
    }
}