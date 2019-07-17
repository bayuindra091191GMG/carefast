<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 17 Jul 2019 13:36:03 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class AddressProvince
 * 
 * @property int $id
 * @property string $name
 * 
 * @property \Illuminate\Database\Eloquent\Collection $addresses
 *
 * @package App\Models
 */
class AddressProvince extends Eloquent
{
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'id' => 'int'
	];

	protected $fillable = [
		'name'
	];

	public function addresses()
	{
		return $this->hasMany(\App\Models\Address::class, 'province');
	}
}
