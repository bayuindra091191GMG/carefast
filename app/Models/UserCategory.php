<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 24 Jul 2019 15:03:58 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class UserCategory
 * 
 * @property int $id
 * @property int $parent_id
 * @property string $name
 * @property string $slug
 * @property string $meta_title
 * @property string $meta_description
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property \Illuminate\Database\Eloquent\Collection $product_user_categories
 *
 * @package App\Models
 */
class UserCategory extends Eloquent
{
	protected $casts = [
		'parent_id' => 'int'
	];

	protected $fillable = [
		'parent_id',
		'name',
		'slug',
		'meta_title',
		'meta_description'
	];

	public function product_user_categories()
	{
		return $this->hasMany(\App\Models\ProductUserCategory::class);
	}
}
