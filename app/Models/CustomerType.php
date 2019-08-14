<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 14 Aug 2019 11:18:17 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class CustomerType
 * 
 * @property int $id
 * @property string $name
 * @property string $description
 *
 * @package App\Models
 */
class CustomerType extends Eloquent
{
	public $timestamps = false;

	protected $fillable = [
		'name',
		'description'
	];
}
