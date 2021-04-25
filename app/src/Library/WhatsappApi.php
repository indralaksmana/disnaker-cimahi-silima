<?php

namespace App\Library;

use App\Library\Settings;
use App\Model\ConfigAppModel;

class WhatsappApi extends Library
{
    public function sendMessage($phone, $message)
    {
        // Get app config
        $whatsappTokenAPI = ConfigAppModel::where('nama', 'whatsapp-token')->first();
        $whatsappDomainAPI = ConfigAppModel::where('nama', 'whatsapp-domain-api')->first();
        
        // Request to API
        $curl = curl_init();
        $token = $whatsappTokenAPI->val;
        $data = [
            'phone' => $phone,
            'message' => $message,
            'secret' => false, // or true
            'priority' => false, // or true
        ];

        curl_setopt($curl, CURLOPT_HTTPHEADER,
            array(
                "Authorization: $token",
            )
        );
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_URL, $whatsappDomainAPI->val."/api/send-message");
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }
}