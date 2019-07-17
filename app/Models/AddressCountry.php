<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 17 Jul 2019 13:35:27 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class AddressCountry
 * 
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $phone_code
 * @property string $currency_code
 * @property string $currency_symbol
 * @property string $lang_code
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property \Illuminate\Database\Eloquent\Collection $address_states
 *
 * @package App\Models
 */
class AddressCountry extends Eloquent
{
	protected $fillable = [
		'code',
		'name',
		'phone_code',
		'currency_code',
		'currency_symbol',
		'lang_code'
	];

	public function address_states()
	{
		return $this->hasMany(\App\Models\AddressState::class, 'country_id');
	}
}
