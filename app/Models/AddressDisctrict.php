<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 17 Jul 2019 13:35:51 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class AddressDisctrict
 * 
 * @property int $id
 * @property string $name
 *
 * @package App\Models
 */
class AddressDisctrict extends Eloquent
{
	public $timestamps = false;

	protected $fillable = [
		'name'
	];
}
