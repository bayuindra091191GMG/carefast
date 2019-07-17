<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 17 Jul 2019 13:40:48 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Order
 * 
 * @property int $id
 * @property string $shipping_option
 * @property string $payment_option
 * @property int $order_status_id
 * @property string $currency_code
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property int $user_id
 * @property int $shipping_address_id
 * @property int $billing_address_id
 * @property string $track_code
 * @property string $zoho_sales_order_id
 * 
 * @property \App\Models\User $user
 * @property \Illuminate\Database\Eloquent\Collection $products
 * @property \Illuminate\Database\Eloquent\Collection $order_return_requests
 *
 * @package App\Models
 */
class Order extends Eloquent
{
	protected $casts = [
		'order_status_id' => 'int',
		'user_id' => 'int',
		'shipping_address_id' => 'int',
		'billing_address_id' => 'int'
	];

	protected $fillable = [
		'shipping_option',
		'payment_option',
		'order_status_id',
		'currency_code',
		'user_id',
		'shipping_address_id',
		'billing_address_id',
		'track_code',
		'zoho_sales_order_id'
	];

	public function user()
	{
		return $this->belongsTo(\App\Models\User::class);
	}

	public function products()
	{
		return $this->belongsToMany(\App\Models\Product::class, 'order_products')
					->withPivot('id', 'qty', 'price', 'tax_amount', 'product_info')
					->withTimestamps();
	}

	public function order_return_requests()
	{
		return $this->hasMany(\App\Models\OrderReturnRequest::class);
	}
}
