<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 17 Jul 2019 13:37:59 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class ProductBrand
 * 
 * @property int $id
 * @property string $name
 * @property string $img_path
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 *
 * @package App\Models
 */
class ProductBrand extends Eloquent
{
	protected $casts = [
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'name',
		'img_path',
		'created_by',
		'updated_by'
	];
}
