<?php

namespace hpsynapse\moduser\Channels;

class WhatsAppChannels
{
    public function send($phone)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://graph.facebook.com/v17.0/127106333824838/messages',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
                "messaging_product": "whatsapp",
                "to": ' . $phone . ',
                "type": "template",
                "template": {
                    "name": "hello_world",
                    "language": {
                        "code": "en_US"
                    }
                }
            }',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer EAAOZAq5RZBgiUBO85YkI93BktJRBUPOc5Lw5gyYae4ae4PnP6odOyenZAaqN3ARFwvIpIOREY3ZBrC63ZA7Q52CQF2wlZAvaIBHpUoLDSUzo2WHZBSrvIDU1UTQh17eOVz1DnS5cEIbsNlX9xzAa6tqVONeB7UbQSGUNt8yFlCMGooSZBjhmWhfHz2gQrfaZB6KTpMM3EsPAFm6qhpDmIRyHE',
                'Content-Type: application/json'
            ),
        ));

        curl_exec($curl);

        curl_close($curl);
    }
}
