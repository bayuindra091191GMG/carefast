<?php

/**
 * Created by Reliese Model.
 * Date: Sat, 13 Feb 2021 10:28:46 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class EmployeeSchedule
 * 
 * @property int $id
 * @property int $employee_id
 * @property string $employee_code
 * @property string $day_status
 * @property int $created_by
 * @property \Carbon\Carbon $created_at
 * @property int $updated_by
 * @property \Carbon\Carbon $updated_at
 * 
 * @property \App\Models\Employee $employee
 *
 * @package App\Models
 */
class EmployeeSchedule extends Eloquent
{
	protected $casts = [
		'employee_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'employee_id',
		'employee_code',
		'day_status',
		'created_by',
		'updated_by'
	];

	public function employee()
	{
		return $this->belongsTo(\App\Models\Employee::class);
	}
}
