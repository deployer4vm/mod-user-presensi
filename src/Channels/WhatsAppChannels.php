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
                'Authorization: Bearer EAAOZAq5RZBgiUBO2xwzbpYWKdGsAgBeSd83nh6uCwsTrkZBA7h6sy69PIiDbmVstKn3gChErzy9l2yXO8I5iPDsK2sjRGuS8jsWivhZBwmhnxRo1uZB6R5cGuyCkqTgq71IX8Heb5DRiPY6p6qTgbBL02Q5vX2bSU0Ix0FFJcPO9ZCI6lZCZCrKXcDXLlfqEZC0qHPpwGnc3XYt1LBGa3SpkZD',
                'Content-Type: application/json'
            ),
        ));

        curl_exec($curl);

        curl_close($curl);
    }
}
