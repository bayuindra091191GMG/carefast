<?php
/**
 * Created by PhpStorm.
 * User: GMG-Developer
 * Date: 13/02/2018
 * Time: 11:34
 */

namespace App\Transformer;


use App\Models\AdminUser;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class AdminUserTransformer extends TransformerAbstract
{
    public function transform(AdminUser $user){

        try{
            $createdDate = Carbon::parse($user->created_at)->format('d M Y');

            $action = "<a class='btn btn-xs btn-info' href='admin-users/edit/".$user->id."' data-toggle='tooltip' data-placement='top'><i class='fas fa-edit'></i></a>";
            $action .= "<a class='delete-modal btn btn-xs btn-danger' data-id='". $user->id ."' ><i class='fas fa-trash-alt'></i></a>";

            if($user->is_super_admin){
                $superAdmin = 'Yes';
            }
            else{
                $superAdmin = 'No';
            }

            return[
                'email'             => $user->email,
                'superadmin'        => $superAdmin,
                'name'              => $user->first_name . ' ' . $user->last_name,
                'role'              => $user->admin_user_role->name,
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