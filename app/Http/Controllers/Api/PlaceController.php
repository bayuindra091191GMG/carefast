<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\libs\Utilities;
use App\Models\Place;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
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
            if(empty($request->input('place_id'))){
                return response()->json("Bad Request", 400);
            }


            $place = Place::find($request->input('place_id'));
            //Generate Random Number
            $number = Utilities::generateBarcodeNumber();
            $place->qr_code = $number;
            $place->save();

            $codeEncrypted = Crypt::encryptString($number);
//            $codeEncrypted = Crypt::encryptString($number.'-'.$place->id);

            return Response::json($codeEncrypted, 200);
        }
        catch (\Exception $exception){
            Log::error('Api/PlaceController - qrCode error EX: '. $exception);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    /**
     * Function to get place information by qr code
     * @param Request $request
     * @return JsonResponse
     */
    public function getPlaceByQr(Request $request){
        try{
//            $rules = array(
//                'qr_code' => 'required'
//            );
//
//            $data = $request->json()->all();
//            $validator = Validator::make($data, $rules);
//            if ($validator->fails()) {
//                return response()->json($validator->messages(), 400);
//            }
            if(empty($request->input('qr_code'))){
                return response()->json("Bad Request", 400);
            }
//            Log::info("hasil decrypt dr apps  = ".Crypt::decryptString($request->input('qr_code')));

            $place = Place::where('qr_code', $request->input('qr_code'))->first();
//            $place = Place::where('qr_code', Crypt::decryptString($request->input('qr_code')))->first();

            if(empty($place)){
                return Response::json("Place Tidak ditemukan!", 482);
            }
            else{
                $placeModel = collect([
                    'id'                => $place->id,
                    'place_name'        => $place->name,
                    'project_name'      => "",
                ]);
                return Response::json($placeModel, 200);
            }
        }
        catch (\Exception $ex){
            Log::error('Api/PlaceController - getPlaceByQr error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
}
