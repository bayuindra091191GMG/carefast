<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 30 Oct 2019 09:26:48 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class AutoNumber
 * 
 * @property string $id
 * @property int $next_no
 *
 * @package App\Models
 */
class AutoNumber extends Eloquent
{
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'next_no' => 'int'
	];

	protected $fillable = [
		'next_no'
	];
}
