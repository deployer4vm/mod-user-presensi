<?php

namespace hpsynapse\moduser\Controllers;

use Illuminate\Http\Request;
use hpsynapse\moduser\Facades\UserRepo;
use hpsynapse\moduser\Facades\RoleRepo;
use hpsynapse\moduser\Facades\UserAuth;

use hpsynapse\moduser\Jobs\BroadcastNotif;

use App\Base\BaseController;

use App\Events\SendData;
use hpsynapse\moduser\Channels\FirebaseChannels;
use hpsynapse\moduser\Channels\PusherChannels;
use hpsynapse\moduser\Channels\WhatsAppChannels;
use hpsynapse\moduser\Models\NotificationChannel;

use Pusher\Pusher;
use GuzzleHttp\Client;
use hpsynapse\moduser\Facades\UserNotifRepo;

class BroadcastController extends BaseController
{
    protected $accessRuleKey = 'moduser.broadcast';

    public function __construct()
    {
        // $this->forceApiOutput();
    }

    public function index(Request $request)
    {
        // 
    }

    public function sendBroadcast(Request $request)
    {
        if (!UserAuth::hasAccess($this->accessRuleKey, 'c')) {
            $this->setError(__('alert.access_denied', false, 403));
            return $this->done();
        }

        $input = $request->only(['title', 'description', 'message']);

        $topic = 'webdev.notif.0';
        UserNotifRepo::broadcast(
            channel: $topic,
            notification: new \hpsynapse\moduser\Notifications\WebdevMessage(
                title: $input['title'],
                body: $input['description'],
                data: [
                    'topic' => $topic,
                    'message' => $input['message']
                ]
            ),
        );
    }
}
