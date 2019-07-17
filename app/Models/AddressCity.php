<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 17 Jul 2019 13:35:15 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class AddressCity
 * 
 * @property int $id
 * @property string $name
 * @property int $province_id
 * 
 * @property \Illuminate\Database\Eloquent\Collection $addresses
 *
 * @package App\Models
 */
class AddressCity extends Eloquent
{
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'id' => 'int',
		'province_id' => 'int'
	];

	protected $fillable = [
		'name',
		'province_id'
	];

	public function addresses()
	{
		return $this->hasMany(\App\Models\Address::class, 'city');
	}
}
