<?php

/**
 * Created by Reliese Model.
 * Date: Fri, 28 Feb 2020 13:47:32 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class TempInsysProject
 * 
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $description
 * @property string $phone
 * @property string $start_date
 * @property string $finish_date
 * @property string $address
 *
 * @package App\Models
 */
class TempInsysProject extends Eloquent
{
	public $timestamps = false;

	protected $fillable = [
		'code',
		'name',
		'description',
		'phone',
		'start_date',
		'finish_date',
		'address'
	];
}
