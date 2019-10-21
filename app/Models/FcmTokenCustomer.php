<?php

/**
 * Created by Reliese Model.
 * Date: Mon, 21 Oct 2019 14:52:15 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class FcmTokenCustomer
 * 
 * @property int $id
 * @property int $customer_id
 * @property string $token
 * 
 * @property \App\Models\Customer $customer
 *
 * @package App\Models
 */
class FcmTokenCustomer extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'customer_id' => 'int'
	];

	protected $hidden = [
		'token'
	];

	protected $fillable = [
		'customer_id',
		'token'
	];

	public function customer()
	{
		return $this->belongsTo(\App\Models\Customer::class);
	}
}
