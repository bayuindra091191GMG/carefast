<?php
/**
 * Created by PhpStorm.
 * User: GMG-Developer
 * Date: 13/02/2018
 * Time: 11:34
 */

namespace App\Transformer;


use App\Models\MenuHeader;
use League\Fractal\TransformerAbstract;

class MenuHeaderTransformer extends TransformerAbstract
{
    public function transform(MenuHeader $menuHeader){
        try{
            $action = "<a class='btn btn-xs btn-info' href='menu-headers/edit/".$menuHeader->id."' data-toggle='tooltip' data-placement='top'><i class='fas fa-edit'></i></a>";
            $action .= "<a class='delete-modal btn btn-xs btn-danger' data-id='". $menuHeader->id ."' ><i class='fas fa-trash-alt'></i></a>";

            return[
                'name'              => $menuHeader->name,
                'action'            => $action
            ];
        }
        catch (\Exception $exception){
            error_log($exception);
        }
    }
}