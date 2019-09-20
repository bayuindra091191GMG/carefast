<?php

/**
 * Created by Reliese Model.
 * Date: Fri, 20 Sep 2019 13:49:33 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Attendance
 * 
 * @property int $id
 * @property int $employee_id
 * @property int $schedule_id
 * @property \Carbon\Carbon $date
 * @property int $status_id
 * @property string $image_path
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 * 
 * @property \App\Models\Employee $employee
 * @property \App\Models\Schedule $schedule
 * @property \App\Models\Status $status
 * @property \Illuminate\Database\Eloquent\Collection $attendance_details
 *
 * @package App\Models
 */
class Attendance extends Eloquent
{
	protected $casts = [
		'employee_id' => 'int',
		'schedule_id' => 'int',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $dates = [
		'date'
	];

	protected $fillable = [
		'employee_id',
		'schedule_id',
		'date',
		'status_id',
		'image_path',
		'created_by',
		'updated_by'
	];

	public function employee()
	{
		return $this->belongsTo(\App\Models\Employee::class);
	}

	public function schedule()
	{
		return $this->belongsTo(\App\Models\Schedule::class);
	}

	public function status()
	{
		return $this->belongsTo(\App\Models\Status::class);
	}

	public function attendance_details()
	{
		return $this->hasMany(\App\Models\AttendanceDetail::class);
	}
}
