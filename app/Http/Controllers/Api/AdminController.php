<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Configuration;
use App\Models\PointHistory;
use App\Models\TransactionHeader;
use App\Models\User;
use App\Notifications\FCMNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class AdminController extends Controller
{
    /**
     * Function to confirm transaction Antar Sendiri by Admin Wastebank.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirmTransactionAntarSendiri(Request $request)
    {
        try{
            $header = TransactionHeader::where('transaction_no', $request->input('transaction_no'))->first();
            $header->status_id = 11;
            $header->save();

            //add point to user
            $configuration = Configuration::where('configuration_key', 'point_amount_user')->first();
            $amount = $configuration->configuration_value;
            $userDB = $header->user;
            $newSaldo = $userDB->point + $amount;
            $userDB->point = $newSaldo;
            $userDB->save();

            $point = PointHistory::create([
                'user_id'  => $header->user_id,
                'type'   => $header->transaction_type_id,
                'transaction_id'    => $header->id,
                'type_transaction'   => "Kredit",
                'amount'    => $amount,
                'saldo'    => $newSaldo,
                'description'    => "Point dari transaksi nomor ".$header->transaction_no,
                'created_at'    => Carbon::now('Asia/Jakarta'),
            ]);

            //send notification
            $userName = $header->user->first_name." ".$header->user->last_name;
            $title = "Digital Waste Solution";
            $body = "Wastebank Mengkonfirmasi Transaksi Antar Sendiri";
            $data = array(
                'type_id' => '2',
                'transaction_no' => $header->transaction_no,
                'name' => $userName
            );
            $isSuccess = FCMNotification::SendNotification($header->created_by_admin, 'app', $title, $body, $data);

            return Response::json("Transaction Confirmed!", 200);
        }
        catch (\Exception $ex){
            return Response::json("Sorry Something went Wrong!", 500);
        }
    }

    /**
     * Used for Antar Sendiri Transaction when Admin Scan the User QR Code
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setTransactionToUser(Request $request)
    {
        $rules = array(
            'transaction_no'    => 'required',
            'email'             => 'required'
        );

        $data = $request->json()->all();

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        }

        $user = User::where('email', $data['email'])->first();
        $header = TransactionHeader::where('transaction_no', $data['transaction_no'])->first();
        $header->user_id = $user->id;
        $header->save();

        //send notification
        $userName = $header->user->first_name." ".$header->user->last_name;
        $title = "Digital Waste Solution";
        $body = "Admin Scan QR Code User";
        $data = array(
            'type_id' => '2',
            'transaction_no' => $header->transaction_no,
            'name' => $userName
        );
        $isSuccess = FCMNotification::SendNotification($header->created_by_admin, 'browser', $title, $body, $data);

        return Response::json([
            'message' => "Success Set " . $user->email . " to " . $header->transaction_no . "!",
        ], 200);
    }

    /**
     * Function to return Transaction Data Related to the Admin Wastebank.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTransactionList(Request $request)
    {
        try{
            $admin = AdminUser::where('email', $request->input('email'));
            $header = TransactionHeader::where('transaction_type_id', 1)->where('waste_bank_id', $admin->waste_bank_id1)->get();

            return Response::json($header, 200);
        }
        catch (\Exception $ex){
            return Response::json("Sorry Something went Wrong!", 500);
        }
    }
}