<?php

// KODE ORIGINAL ==============================================
// namespace hpsynapse\moduser\Channels;

// use Illuminate\Notifications\Notification;

// use hpsynapse\moduser\Services\FirebaseApi;

// class FirebaseChannels
// {
//     /**
//      * Send the given notification. 
//      *
//      * @param mixed $notifiable
//      * @param \Illuminate\Notifications\Notification $notification
//      *
//      * @throws missingRecipient
//      */

//     public function send($notifiable, Notification $notification)
//     {
//         $notifMessage = $notification->toFirebase($notifiable);

//         if(isset($notifMessage['topic'])){
//             FirebaseApi::sendMessageToTopic($notifMessage['topic'],$notifMessage);
//         }else if(isset($notifMessage['token'])){
//             FirebaseApi::sendMessageToToken($notifMessage['token'],$notifMessage);            
//         }else{

//         }

// //        $token1 = 'dbQCIUiid1w:APA91bE3ZH3ZZlP_9hL9lw-pL3SDjRdQ7q8pXhq4f-K4zkxpqaLn-HZiCf8BOvqgrlYGHccwKYI658oslg4Rd0UY4kjr8hBf23-593Lxd8UyenEg3Ls-YKQ9z4fcz3CSzRwvxSs3zBTx';
// //        $token2 = 'doQA4MMaaOw:APA91bGdgPbPFxwa3ZY3XEm1jDUEqAwTS0s4sh4m5GF6ptsI8Anp-YWSUrh4vcZUGosOgZrthcOqzbF_FilevIUVuHIbxow_KCsLadaGf2yP3z_vj90a6PQrXrZbSIjcYZl9ts5xB9QA';
// //        FirebaseApi::subscribeToTopic(
// //            'reseller',[$token1,$token2]
// //            );
// //        

//         return true;
//     }

// }
// ===============================================================

namespace hpsynapse\moduser\Channels;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\ServiceAccount;
use hpsynapse\moduser\Facades\UserAuth;

class FirebaseChannels
{
    // mengirim data kefirebase sesuai data yang di masukan di broadcast contoller
    public function send($notifiable, $notification)
    {
        // Inisialisasi Firebase
        $serviceAccountPath = base_path('firebase_credentials.json'); // Sesuaikan dengan lokasi Anda
        $serviceAccount = ServiceAccount::fromValue($serviceAccountPath);
        $factory = (new Factory)
            ->withServiceAccount($serviceAccount);

        $messaging = $factory->createMessaging();

        // Kirim Pesan
        // $deviceToken = $notifiable->fcm_token; // Sesuaikan dengan atribut token pada model notifiable Anda
        $message = CloudMessage::withTarget('token', 'fgn5PYWbgUojYhLmSiYui6:APA91bF846TBjJ3_zJO0mHYjfdm51aNU8Hf89wrMHOLiC2IZPU-rPp2CYhdSk9Yyb9rHsV3VkwXTSMGGcerpMWxHkpZ2NjmrL-d0iz6q57k-IqtDDSaKca782rQ0iVFiEznt-mrLIqO8')
            ->withNotification([
                'title' => $notification->title,
                'body' => $notification->body,
            ])
            ->withData($notification->data);

        $messaging->send($message);
    }

    // fungsi untuk meng generate token fcm
    public function generateCustomToken()
    {
        // Inisialisasi Firebase
        $serviceAccountPath = base_path('firebase_credentials.json'); // Sesuaikan dengan lokasi Anda
        $serviceAccount = ServiceAccount::fromValue($serviceAccountPath);
        $factory = (new Factory)
            ->withServiceAccount($serviceAccount);

        $auth = $factory->createAuth();

        // ID pengguna untuk token yang akan digenerate
        $uid = (string) UserAuth::user('id'); // Sesuaikan dengan ID pengguna Anda

        // Generate custom token
        $customToken = $auth->createCustomToken($uid);

        return $customToken;
    }
}
