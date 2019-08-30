<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\libs\Utilities;
use App\Models\Place;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlaceController extends Controller
{
    /**
     * Function to generate Qr Code for Places
     * @param Request $request
     * @return JsonResponse
     */
    public function qrCode(Request $request){
        try{
            $rules = array(
                'place_id' => 'required'
            );

            $data = $request->json()->all();
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return response()->json($validator->messages(), 400);
            }

            $place = Place::find($request->input('place_id'));
            //Generate Random Number
            $number = Utilities::generateBarcodeNumber();
            $place->qr_code = $number;
            $codeEncrypted = Crypt::encryptString($number);

            return Response::json([
                'message'   => 'Berhasil mengambil data Qr Code',
                'model'     => $codeEncrypted
            ]);
        }
        catch (\Exception $exception){
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
}
