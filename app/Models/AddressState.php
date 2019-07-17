<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 17 Jul 2019 13:26:52 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class AddressState
 * 
 * @property int $id
 * @property int $country_id
 * @property string $code
 * @property string $name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property \App\Models\AddressCountry $address_country
 *
 * @package App\Models
 */
class AddressState extends Eloquent
{
	protected $casts = [
		'country_id' => 'int'
	];

	protected $fillable = [
		'country_id',
		'code',
		'name'
	];

	public function address_country()
	{
		return $this->belongsTo(\App\Models\AddressCountry::class, 'country_id');
	}
}
