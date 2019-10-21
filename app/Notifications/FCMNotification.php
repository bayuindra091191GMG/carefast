<?php
/**
 * Created by PhpStorm.
 * User: YANSEN
 * Date: 2/13/2019
 * Time: 21:26
 */

namespace App\Notifications;


use App\Models\FcmTokenAdmin;
use App\Models\FcmTokenApp;
use App\Models\FcmTokenBrowser;
use App\Models\FcmTokenCollector;
use App\Models\FcmTokenCustomer;
use App\Models\FcmTokenUser;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;


class FCMNotification
{
    public static function SaveToken($userId, $token, $type){
        if($type == 'user'){
            $isExistToken = FcmTokenUser::where('user_id', $userId)->first();
            if(!empty($isExistToken)){
                $isExistToken->token = $token;
                $isExistToken->save();
            }
            else{
                $fcmToken = FcmTokenUser::create([
                    'user_id' => $userId,
                    'token' => $token
                ]);
            }
        }
        else if($type == 'customer'){
            $isExistToken = FcmTokenCustomer::where('customer_id', $userId)->first();
            if(!empty($isExistToken)){
                $isExistToken->token = $token;
                $isExistToken->save();
            }
            else{
                $fcmToken = FcmTokenCustomer::create([
                    'customer_id' => $userId,
                    'token' => $token
                ]);
            }
        }
        else{
            $isExistToken = FcmTokenAdmin::where('user_admin_id', $userId)->first();
            if(!empty($isExistToken)){
                $isExistToken->token = $token;
                $isExistToken->save();
            }
            else{
                $fcmToken = FcmTokenAdmin::create([
                    'user_admin_id' => $userId,
                    'token' => $token
                ]);
            }
        }
    }

    public static function SendNotification($userId, $type, $title, $body, $notifData){
        try{
            if($type == 'user'){
                $user  = FcmTokenUser::where('user_id', $userId)->first();
            }
            else if($type == 'customer'){
                $user  = FcmTokenCustomer::where('customer_id', $userId)->first();
            }
            else{
                $user  = FcmTokenAdmin::where('user_admin_id', $userId)->first();
            }

            if(empty($user)){
                Log::error("FCMNotification - SendNotification Error: FCM Token Null, userId = ".$userId);
                return "";
            }
            else{
                $token = $user->token;
                $data = array(
                    "to" => $token,
                    "notification" => [
                        "title"=> $title,
                        "body"=> $body,
                    ],
                    "data" => $notifData,
                );
                $data_string = json_encode($data);
                $client = new Client([
                    'base_uri' => "https://fcm.googleapis.com/fcm/send",
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'key=' .env('FCM_SERVER_KEY'),
                    ],
                ]);
//            dd($data_string);
                $response = $client->request('POST', 'https://fcm.googleapis.com/fcm/send', [
                    'body' => $data_string
                ]);
                $responseJSON = json_decode($response->getBody());
                //dd($responseJSON);

                return $responseJSON->results[0]->message_id;
            }
        }
        catch (\Exception $exception){
//            dd($exception);
            Log::error("FCMNotification - SendNotification Error: ". $exception);
            return "";
        }
    }
}
