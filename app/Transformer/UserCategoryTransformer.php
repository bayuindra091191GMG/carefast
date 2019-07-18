<?php
/**
 * Created by PhpStorm.
 * User: GMG-Developer
 * Date: 13/02/2018
 * Time: 11:34
 */

namespace App\Transformer;


use App\Models\Category;
use App\Models\UserCategory;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class UserCategoryTransformer extends TransformerAbstract
{
    public function transform(UserCategory $category){

        try{
            $createdDate = Carbon::parse($category->created_at)->format('d M Y');
            $updatedDate = Carbon::parse($category->updated_at)->format('d M Y');

            $action = "<a class='btn btn-xs btn-info' href='categories/edit/".$category->id."' data-toggle='tooltip' data-placement='top'><i class='icon-mode_edit'></i></a>";
            $action .= "<a class='delete-modal btn btn-xs btn-danger' data-id='". $category->id ."' ><i class='icon-delete'></i></a>";

            //Check if got any parent
            if($category->parent_id != null){
                $temp = UserCategory::find($category->parent_id);
                $parent = $temp->name;
            }
            else{
                $parent = "-";
            }

            return[
                'name'              => $category->name,
                'slug'              => $category->slug,
                'parent'            => $parent,
                'meta_title'        => $category->meta_title,
                'meta_description'  => $category->meta_description,
                'created_at'        => $createdDate,
                'updated_at'        => $updatedDate,
                'action'            => $action
            ];
        }
        catch (\Exception $exception){
            error_log($exception);
        }
    }
}