<?php
/**
 * Created by PhpStorm.
 * User: GMG-Developer
 * Date: 13/02/2018
 * Time: 11:34
 */

namespace App\Transformer;


use App\Models\User;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    public function transform(User $user){

        try{
            $createdDate = Carbon::parse($user->created_at)->toIso8601String();

            $routeEditUrl = route('admin.users.edit', ['id' => $user->id]);

            $action = "<a class='btn btn-xs btn-info' href='".$routeEditUrl."' data-toggle='tooltip' data-placement='top'><i class='fas fa-edit'></i></a>";
            $action .= "<a class='delete-modal btn btn-xs btn-danger' data-id='". $user->id ."' ><i class='fas fa-trash-alt'></i></a>";

            return[
                'email'             => $user->email,
                'name'              => $user->name,
                'phone'             => $user->phone,
                'status'            => $user->status->description,
                'created_at'        => $createdDate,
                'action'            => $action
            ];
        }
        catch (\Exception $exception){
            error_log($exception);
        }
    }
}