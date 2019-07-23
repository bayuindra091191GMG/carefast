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
            $createdDate = Carbon::parse($category->created_at)->toIso8601String();
            $updatedDate = Carbon::parse($category->updated_at)->toIso8601String();

            $routeEditUrl = route('admin.user_categories.edit', ['id' => $category->id]);

            $action = "<a class='btn btn-xs btn-info' href='". $routeEditUrl. "' data-toggle='tooltip' data-placement='top'><i class='fas fa-pencil-alt'></i></a>";
            $action .= "<a class='delete-modal btn btn-xs btn-danger' data-id='". $category->id ."' ><i class='fas fa-trash-alt text-white'></i></a>";

            //Check if got any parent
//            if($category->parent_id != null){
//                $temp = UserCategory::find($category->parent_id);
//                $parent = $temp->name;
//            }
//            else{
//                $parent = "-";
//            }

            return[
                'name'              => $category->name,
//                'slug'              => $category->slug,
//                'parent'            => $parent,
//                'meta_title'        => $category->meta_title,
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