<?php

/**
 * Created by Reliese Model.
 * Date: Fri, 28 Feb 2020 13:47:15 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class TempInsysEmploye
 *
 * @property int $id
 * @property string $code
 * @property string $first_name
 * @property string $last_name
 * @property string $phone
 * @property string $dob
 * @property string $nik
 * @property string $address
 * @property string $role
 *
 * @package App\Models
 */
class TempInsysEmploye extends Eloquent
{
	public $timestamps = false;

	protected $fillable = [
		'id',
		'code',
		'first_name',
		'last_name',
		'phone',
		'dob',
		'nik',
		'address',
		'role'
	];
}
