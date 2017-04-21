<?php

namespace App\Helpers;

use App\Notificacion;
use GuzzleHttp\Psr7\Request;

/**
 * Created by PhpStorm.
 * User: EnmanuelPc
 * Date: 03/11/2015
 * Time: 3:39
 */
class PushHandler
{

    const API_KEY = 'AIzaSyDP6saC9NuquBTxp-7fAsgOTLsKBRbQ6V4';

    public function generatePush($user, $data)
    {
        $regId = $user->id_push;
        if ($regId != "") {
            $body = json_encode([
                'data' => $data,
                'to' => $regId
            ]);
            $headers = ['Authorization: ' . 'key=' . self::API_KEY,
                'Content-Type: ' . 'application/json'];
            $ch = curl_init('https://gcm-http.googleapis.com/gcm/send');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);
            return $result;
        } else {
            $notif = new Notificacion();
            $notif->user_id = $user->id;
            $notif->data = json_encode($data);
            $notif->save();
        }
    }

    public function checkNotif($user)
    {
        $notificaciones = $user->notificaciones;
        foreach ($notificaciones as $notif) {
            $this->generatePush($user, json_decode($notif->data));
            $notif->delete();
        }
    }

}