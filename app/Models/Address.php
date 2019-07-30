<?php

/**
 * Created by Reliese Model.
 * Date: Tue, 30 Jul 2019 11:09:13 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Address
 *
 * @property int $id
 * @property int $user_id
 * @property string $description
 * @property int $primary
 * @property int $province_id
 * @property int $city_id
 * @property string $postal_code
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 *
 * @property \App\Models\AddressCity $address_city
 * @property \App\Models\AdminUser $createdBy
 * @property \App\Models\AdminUser $updatedBy
 * @property \App\Models\AddressProvince $address_province
 * @property \App\Models\User $user
 *
 * @package App\Models
 */
class Address extends Eloquent
{
	protected $casts = [
		'user_id' => 'int',
		'primary' => 'int',
		'province_id' => 'int',
		'city_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'user_id',
		'description',
		'primary',
		'province_id',
		'city_id',
		'postal_code',
        'created_at',
		'created_by',
        'updated_at',
		'updated_by'
	];

	public function address_city()
	{
		return $this->belongsTo(\App\Models\AddressCity::class, 'city_id');
	}

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\AdminUser::class, 'created_by');
    }

	public function updatedBy()
	{
		return $this->belongsTo(\App\Models\AdminUser::class, 'updated_by');
	}

	public function address_province()
	{
		return $this->belongsTo(\App\Models\AddressProvince::class, 'province_id');
	}

	public function user()
	{
		return $this->belongsTo(\App\Models\User::class);
	}
}
