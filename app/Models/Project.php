<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 22 Aug 2019 18:01:35 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Project
 * 
 * @property int $id
 * @property string $name
 * @property string $latitude
 * @property string $longitude
 * @property int $customer_id
 * @property string $phone
 * @property string $address
 * @property string $description
 * @property int $status_id
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 * 
 * @property \App\Models\Customer $customer
 * @property \App\Models\Status $status
 * @property \App\Models\AdminUser $admin_user
 *
 * @package App\Models
 */
class Project extends Eloquent
{
	protected $casts = [
		'customer_id' => 'int',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'name',
		'latitude',
		'longitude',
		'customer_id',
		'phone',
		'address',
		'description',
		'status_id',
		'created_by',
		'updated_by'
	];

	public function customer()
	{
		return $this->belongsTo(\App\Models\Customer::class);
	}

	public function status()
	{
		return $this->belongsTo(\App\Models\Status::class);
	}

	public function admin_user()
	{
		return $this->belongsTo(\App\Models\AdminUser::class, 'updated_by');
	}
}
