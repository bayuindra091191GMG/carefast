<?php
/**
 * Created by PhpStorm.
 * User: yanse
 * Date: 14-Sep-17
 * Time: 2:38 PM
 */

namespace App\libs;

use App\Models\AutoNumber;
use App\Models\Place;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;


class Utilities
{
    public static function convertIntToDay($days){
        $newDay = str_replace('1', 'Senin', $days);
        $newDay = str_replace('2', 'Selasa', $newDay);
        $newDay = str_replace('3', 'Rabu', $newDay);
        $newDay = str_replace('4', 'Kamis', $newDay);
        $newDay = str_replace('5', 'Jumat', $newDay);
        $newDay = str_replace('6', 'Sabtu', $newDay);
        $newDay = str_replace('7', 'Minggu', $newDay);
        $newDay = str_replace('#', ', ', $newDay);
        return $newDay;

    }
    public static function convertIntToWeek($weeks){
        $newWeek = str_replace('1', 'Minggu I', $weeks);
        $newWeek = str_replace('2', 'Minggu II', $newWeek);
        $newWeek = str_replace('3', 'Minggu III', $newWeek);
        $newWeek = str_replace('4', 'Minggu IV', $newWeek);
        $newWeek = str_replace('#', ', ', $newWeek);
        return $newWeek;

    }

    public static function barcodeNumberExists($number) {
        // query the database and return a boolean
        // for instance, it might look like this in Laravel
        return Place::where('qr_code', $number)->exists();
    }

    public static function generateBarcodeNumber() {
        $number = mt_rand(1000000000, mt_getrandmax()); // better than rand()

        // call the same function if the barcode exists already
        if (Utilities::barcodeNumberExists($number)) {
            return Utilities::generateBarcodeNumber();
        }

        // otherwise, it's valid and can be used
        return $number;
    }

    public static function checkingQrCode($qrCode){
        $decryptString = Crypt::decryptString($qrCode);
        $decryptArr = explode('-', $decryptString);

        $place = Place::find($decryptArr[1]);

        if($place->id != $decryptArr[1]){
            return false;
        }
        else{
            return true;
        }

    }

    public static function ExceptionLog($ex){
        $logContent = ['id' => 1,
            'description' => $ex];

        $log = new Logger('exception');
        $log->pushHandler(new StreamHandler(storage_path('logs/error.log')), Logger::ALERT);
        $log->info('exception', $logContent);
    }

    public static function CreateProductSlug($string){
        try{
            $string = strtolower($string);
            $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
            $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

            return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
        }catch(\Exception $ex){
//            dd($ex);
            error_log($ex);
        }
    }

    public static function arrayIsUnique($array){
        return array_unique($array) == $array;
    }

    //  Get next incremental number of transaction number
    /**
     * @param $prepend
     * @return int
     * @throws \Exception
     */
    public static function GetNextTransactionNumber($prepend){
        try{
            $nextNo = 1;
            $orderNumber = AutoNumber::find($prepend);
            if(empty($orderNumber)){
                AutoNumber::create([
                    'id'        => $prepend,
                    'next_no'   => 1
                ]);
            }
            else{
                $nextNo = $orderNumber->next_no;
            }

            return $nextNo;
        }
        catch (\Exception $ex){
            throw $ex;
        }
    }

    // Update incremental number of transaction number
    /**
     * @param $prepend
     * @throws \Exception
     */
    public static function UpdateTransactionNumber($prepend){
        try{
            $orderNumber = AutoNumber::find($prepend);
            $orderNumber->next_no++;
            $orderNumber->save();
        }
        catch (\Exception $ex){
            throw $ex;
        }
    }

    // Generate full transaction number
    /**
     * @param $prepend
     * @param $nextNumber
     * @return string
     * @throws \Exception
     */
    public static function GenerateAutoNumber($prepend, $nextNumber){
        try{
            $modulus = "";
            $nxt = $nextNumber. '';

            switch (strlen($nxt))
            {
                case 1:
                    $modulus = "00000";
                    break;
                case 2:
                    $modulus = "0000";
                    break;
                case 3:
                    $modulus = "000";
                    break;
                case 4:
                    $modulus = "00";
                    break;
                case 5:
                    $modulus = "0";
                    break;
            }

            $month = Carbon::today('Asia/Jakarta')->month;

            return $prepend. '/'. $month. '/'. $modulus. $nextNumber;
        }
        catch (\Exception $ex){
            throw $ex;
        }
    }

    public static function toFloat($raw){
        $valueStr1 = str_replace('.','', $raw);
        $valueStr2 = str_replace(',', '.', $valueStr1);

        return (double) $valueStr2;
    }
}
