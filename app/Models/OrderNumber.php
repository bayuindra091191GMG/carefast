<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 17 Jul 2019 13:36:35 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class OrderNumber
 * 
 * @property string $id
 * @property int $next_no
 *
 * @package App\Models
 */
class OrderNumber extends Eloquent
{
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'next_no' => 'int'
	];

	protected $fillable = [
		'id',
		'next_no'
	];
}
