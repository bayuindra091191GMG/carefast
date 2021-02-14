<?php

/**
 * Created by Reliese Model.
 * Date: Sat, 13 Feb 2021 15:48:41 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class AttendanceOvertime
 * 
 * @property int $id
 * @property int $employee_id
 * @property int $project_id
 * @property int $attendance_sick_id
 * @property string $type
 * @property \Carbon\Carbon $date
 * @property string $time_start
 * @property string $time_end
 * @property string $description
 * @property string $image_path
 * @property int $is_approve
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 * 
 * @property \App\Models\Employee $employee
 * @property \App\Models\Project $project
 *
 * @package App\Models
 */
class AttendanceOvertime extends Eloquent
{
	protected $casts = [
		'employee_id' => 'int',
		'project_id' => 'int',
		'attendance_sick_id' => 'int',
		'is_approve' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $dates = [
		'date'
	];

	protected $fillable = [
		'employee_id',
		'project_id',
		'attendance_sick_id',
		'type',
		'date',
		'time_start',
		'time_end',
		'description',
		'image_path',
		'is_approve',
		'created_by',
		'updated_by'
	];

	public function employee()
	{
		return $this->belongsTo(\App\Models\Employee::class);
	}

	public function project()
	{
		return $this->belongsTo(\App\Models\Project::class);
	}
}
