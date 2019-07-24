<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 24 Jul 2019 14:22:03 +0700.
 */

namespace App\Models;

use Carbon\Carbon;
use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Order
 * 
 * @property int $id
 * @property int $user_id
 * @property string $order_number
 * @property string $shipping_option
 * @property string $payment_option
 * @property string $sales_name
 * @property int $status_id
 * @property float $total_price
 * @property float $discount
 * @property float $grand_total
 * @property string $notes
 * @property int $prepared_by
 * @property int $checked_by
 * @property int $approved_by
 * @property string $cancel_reason
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property \App\Models\Status $status
 * @property \App\Models\User $user
 * @property \Illuminate\Database\Eloquent\Collection $products
 * @property \Illuminate\Database\Eloquent\Collection $order_return_requests
 *
 * @package App\Models
 */
class Order extends Eloquent
{
	protected $casts = [
		'user_id' => 'int',
		'status_id' => 'int',
		'total_price' => 'float',
		'discount' => 'float',
		'grand_total' => 'float',
		'prepared_by' => 'int',
		'checked_by' => 'int',
		'approved_by' => 'int'
	];

	protected $fillable = [
		'user_id',
		'order_number',
		'shipping_option',
		'payment_option',
		'sales_name',
		'status_id',
		'total_price',
		'discount',
		'grand_total',
		'notes',
		'prepared_by',
		'checked_by',
		'approved_by',
		'cancel_reason'
	];

    protected $appends = [
        'created_at_string',
        'total_price_string',
        'grand_total_string'
    ];

    public function getCreatedAtStringAttribute(){
        return Carbon::parse($this->attributes['created_at'])->format('d M Y');
    }

    public function getTotalPriceStringAttribute(){
        return number_format($this->attributes['total_price'], 0, ",", ".");
    }

    public function getGrandTotalStringAttribute(){
        return number_format($this->attributes['grand_total'], 0, ",", ".");
    }


    public function status()
	{
		return $this->belongsTo(\App\Models\Status::class);
	}

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
