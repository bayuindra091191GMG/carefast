<?php

/**
 * Created by Reliese Model.
 * Date: Mon, 29 Jul 2019 15:25:26 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class SalesOrderHeader
 * 
 * @property int $id
 * @property string $code
 * @property int $order_id
 * @property int $user_id
 * @property \Carbon\Carbon $date
 * @property string $user_address
 * @property string $user_phone
 * @property int $term
 * @property float $limit
 * @property string $sales
 * @property float $total_price
 * @property float $total_discount
 * @property int $discount_percentage
 * @property float $grand_total
 * @property int $is_ko_created
 * @property string $notes
 * @property int $status_id
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 * 
 * @property \App\Models\AdminUser $admin_user
 * @property \App\Models\Order $order
 * @property \App\Models\Status $status
 * @property \App\Models\User $user
 * @property \Illuminate\Database\Eloquent\Collection $sales_order_details
 *
 * @package App\Models
 */
class SalesOrderHeader extends Eloquent
{
	protected $casts = [
		'order_id' => 'int',
		'user_id' => 'int',
		'term' => 'int',
		'limit' => 'float',
		'total_price' => 'float',
		'total_discount' => 'float',
		'discount_percentage' => 'int',
		'grand_total' => 'float',
		'is_ko_created' => 'int',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $dates = [
		'date'
	];

	protected $fillable = [
		'code',
		'order_id',
		'user_id',
		'date',
		'user_address',
		'user_phone',
		'term',
		'limit',
		'sales',
		'total_price',
		'total_discount',
		'discount_percentage',
		'grand_total',
		'is_ko_created',
		'notes',
		'status_id',
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

	public function order()
	{
		return $this->belongsTo(\App\Models\Order::class);
	}

	public function status()
	{
		return $this->belongsTo(\App\Models\Status::class);
	}

	public function user()
	{
		return $this->belongsTo(\App\Models\User::class);
	}

	public function sales_order_details()
	{
		return $this->hasMany(\App\Models\SalesOrderDetail::class, 'header_id');
	}
}
