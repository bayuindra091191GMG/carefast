<?php

/**
 * Created by Reliese Model.
 * Date: Fri, 19 Jul 2019 11:20:18 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Cart
 * 
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property string $voucher_code
 * @property string $description
 * @property int $qty
 * @property float $price
 * @property float $total_price
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property \App\Models\User $user
 * @property \App\Models\Product $product
 *
 * @package App\Models
 */
class Cart extends Eloquent
{
	protected $casts = [
		'user_id' => 'int',
		'product_id' => 'int',
		'qty' => 'int',
		'price' => 'float',
		'total_price' => 'float'
	];

	protected $fillable = [
		'user_id',
		'product_id',
		'voucher_code',
		'description',
		'qty',
		'price',
		'total_price'
	];

	public function user()
	{
		return $this->belongsTo(\App\Models\User::class);
	}

	public function product()
	{
		return $this->belongsTo(\App\Models\Product::class);
	}
}
