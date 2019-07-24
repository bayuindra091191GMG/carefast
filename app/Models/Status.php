<?php

/**
 * Created by Reliese Model.
 * Date: Mon, 22 Jul 2019 13:38:40 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Status
 * 
 * @property int $id
 * @property string $description
 * 
 * @property \Illuminate\Database\Eloquent\Collection $admin_users
 * @property \Illuminate\Database\Eloquent\Collection $product_categories
 * @property \Illuminate\Database\Eloquent\Collection $products
 * @property \Illuminate\Database\Eloquent\Collection $users
 * @property \Illuminate\Database\Eloquent\Collection $orders
 *
 * @package App\Models
 */
class Status extends Eloquent
{
	public $timestamps = false;

	protected $fillable = [
		'description'
	];

	public function admin_users()
	{
		return $this->hasMany(\App\Models\AdminUser::class);
	}

	public function product_categories()
	{
		return $this->hasMany(\App\Models\ProductCategory::class);
	}

	public function products()
	{
		return $this->hasMany(\App\Models\Product::class);
	}

	public function users()
	{
		return $this->hasMany(\App\Models\User::class);
	}

	public function orders()
	{
		return $this->hasMany(\App\Models\Order::class);
	}
}
