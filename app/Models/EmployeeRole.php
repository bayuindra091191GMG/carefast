<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 15 Aug 2019 12:28:03 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class EmployeeRole
 * 
 * @property int $id
 * @property string $name
 * @property string $description
 * 
 * @property \Illuminate\Database\Eloquent\Collection $employees
 *
 * @package App\Models
 */
class EmployeeRole extends Eloquent
{
	public $timestamps = false;

	protected $fillable = [
		'name',
		'description'
	];

	public function employees()
	{
		return $this->hasMany(\App\Models\Employee::class);
	}
}
