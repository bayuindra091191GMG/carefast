<?php

/**
 * Created by Reliese Model.
 * Date: Mon, 18 Nov 2019 15:41:29 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class AttendanceAbsent
 * 
 * @property int $id
 * @property int $employee_id
 * @property int $place_id
 * @property \Carbon\Carbon $date
 * @property int $status_id
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 * 
 * @property \App\Models\Employee $employee
 *
 * @package App\Models
 */
class AttendanceAbsent extends Eloquent
{
	protected $casts = [
		'employee_id' => 'int',
		'place_id' => 'int',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $dates = [
		'date'
	];

	protected $fillable = [
		'employee_id',
		'place_id',
		'date',
		'status_id',
		'created_by',
		'updated_by'
	];

	public function employee()
	{
		return $this->belongsTo(\App\Models\Employee::class);
	}
}
