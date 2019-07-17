<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 17 Jul 2019 13:40:24 +0700.
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
 * @property int $province
 * @property int $city
 * @property int $disctrict
 * @property string $postal_code
 * @property string $recipient_name
 * @property string $recipient_phone
 * @property string $name
 * @property string $latitude
 * @property string $longitude
 * @property \Carbon\Carbon $created_at
 * @property string $notes
 * 
 * @property \App\Models\AddressCity $address_city
 * @property \App\Models\AddressProvince $address_province
 * @property \App\Models\User $user
 *
 * @package App\Models
 */
class Address extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'user_id' => 'int',
		'primary' => 'int',
		'province' => 'int',
		'city' => 'int',
		'disctrict' => 'int'
	];

	protected $fillable = [
		'user_id',
		'description',
		'primary',
		'province',
		'city',
		'disctrict',
		'postal_code',
		'recipient_name',
		'recipient_phone',
		'name',
		'latitude',
		'longitude',
		'notes'
	];

	public function address_city()
	{
		return $this->belongsTo(\App\Models\AddressCity::class, 'city');
	}

	public function address_province()
	{
		return $this->belongsTo(\App\Models\AddressProvince::class, 'province');
	}

	public function user()
	{
		return $this->belongsTo(\App\Models\User::class);
	}
}
