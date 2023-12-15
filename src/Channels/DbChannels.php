<?php

namespace hpsynapse\moduser\Channels;

use Illuminate\Notifications\Notification;

//use hpsynapse\moduser\Service\FirebaseAPI;

class DbChannels
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        $data = $notification->toDatabase($notifiable);

        $link_web = '';
        $link_apps = '';
        
        if (isset($data['link_web'])) {
            $link_web = $data['link_web'];
            // if ($link_web['link']) {
            //     $link_web['link'] = $link_web['link'];
            // } else if ($link_web['route'] && $link_web['parameter']) {
            //     $link_web['link'] = route($link_web['route'], $link_web['parameter']);
            // } else {
            //     $link_web['link'] = route('member.notification.detail', ['notificationId' => $notification->id]);
            // }
            unset($data['link_web']);
        }

        if (isset($data['link_apps'])) {
            $link_apps = $data['link_apps'];
            unset($data['link_apps']);
        }

        $tenantId = config('tenant.id', 0);
        if ($notification->tenantId && !$tenantId) {
            // $model = $notifiable->routeNotificationFor('database')->setTenantId($notification->tenantId);
            // $model = (new \hpsynapse\moduser\Models\Notification())->setTenantId($notification->tenantId);
            \App\Facades\Tenant::setActiveTenantById($notification->tenantId);
        } else {
            // $model = $notifiable->routeNotificationFor('database');
        }
        
        // $model = $notifiable->routeNotificationFor('database');
        $model = (new \hpsynapse\moduser\Models\Notification());

        return $model->create([
            'id' => $notification->id,
            'tenant_id' => $notification->tenantId,

            'link_web' => $link_web,
            'link_apps' => $link_apps,
            
            'type' => get_class($notification),
            'data' => $data,
            'read_at' => null,
        ]);
    }

    /**
     * di class Notification-nya harus ada method ini
     */
    public function toDatabase($notifiable)
    {
        return [
            'subject' => 'STRING',
            'description' => 'STRING',
            'body' => 'STRING',
            'from' => [
                'name' => 'STRING',
                'icon' => 'STRING',
            ],
            'link_web' => [
                'link' => 'STRING',
                'route' => 'STRING',
                'parameter' => [
                    'notifId' => 'STRING',
                ]
            ],
            'link_apps' => 'STRING'
        ];
    }
}
