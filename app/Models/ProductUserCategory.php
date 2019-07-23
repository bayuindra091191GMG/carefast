<?php

/**
 * Created by Reliese Model.
 * Date: Tue, 23 Jul 2019 11:04:24 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class ProductUserCategory
 * 
 * @property int $id
 * @property int $product_id
 * @property int $user_category_id
 * @property float $price
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 * 
 * @property \App\Models\AdminUser $admin_user
 * @property \App\Models\Product $product
 * @property \App\Models\UserCategory $user_category
 *
 * @package App\Models
 */
class ProductUserCategory extends Eloquent
{
	protected $casts = [
		'product_id' => 'int',
		'user_category_id' => 'int',
		'price' => 'float',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'product_id',
		'user_category_id',
		'price',
		'created_by',
		'updated_by'
	];

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\AdminUser::class, 'created_by');
    }

	public function updatedBy()
	{
		return $this->belongsTo(\App\Models\AdminUser::class, 'updated_by');
	}

	public function product()
	{
		return $this->belongsTo(\App\Models\Product::class);
	}

	public function user_category()
	{
		return $this->belongsTo(\App\Models\UserCategory::class);
	}
}
