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

        // Membuat instance dari PusherChannels
        $pusherChannels = new PusherChannels();
        $firebaseChannels = new FirebaseChannels();
        $whatsappChannels = new WhatsAppChannels();

        // Mengirim notifikasi ke Pusher
        $notifiable = UserAuth::user(); // Sesuaikan ini dengan notifiable yang sesuai
        $notification = new \hpsynapse\moduser\Notifications\AdminMessage($input['title'], $input['description'], ['message' => $input['message']]);
        $pusherChannels->send($notifiable, $notification);

        $whatsappChannels->send($request->title);

        // Generate token FCM
        $customToken = $firebaseChannels->generateCustomToken(); // Memanggil fungsi generateCustomToken

        dd(NotificationChannel::where('user_id', UserAuth::user('id'))->first());

        // Mengirim notifikasi ke Firebase
        $notifiable = $customToken; // Menyimpan token FCM pada notifiable (pastikan notifiable memiliki atribut fcm_token)
        $firebaseChannels->send($notifiable, $notification);
    }
}
