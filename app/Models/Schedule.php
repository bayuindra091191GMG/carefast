<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 04 Sep 2019 15:09:10 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Schedule
 * 
 * @property int $id
 * @property int $employee_id
 * @property int $place_id
 * @property \Carbon\Carbon $start
 * @property \Carbon\Carbon $finish
 * @property int $status_id
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 * 
 * @property \App\Models\Status $status
 * @property \App\Models\Employee $employee
 * @property \App\Models\Place $place
 * @property \Illuminate\Database\Eloquent\Collection $attendances
 * @property \Illuminate\Database\Eloquent\Collection $schedule_details
 *
 * @package App\Models
 */
class Schedule extends Eloquent
{
	protected $casts = [
		'employee_id' => 'int',
		'place_id' => 'int',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $dates = [
		'start',
		'finish'
	];

	protected $fillable = [
		'employee_id',
		'place_id',
		'start',
		'finish',
		'status_id',
		'created_by',
		'updated_by'
	];

	public function status()
	{
		return $this->belongsTo(\App\Models\Status::class);
	}

	public function employee()
	{
		return $this->belongsTo(\App\Models\Employee::class);
	}

	public function place()
	{
		return $this->belongsTo(\App\Models\Place::class);
	}

	public function attendances()
	{
		return $this->hasMany(\App\Models\Attendance::class);
	}

	public function schedule_details()
	{
		return $this->hasMany(\App\Models\ScheduleDetail::class);
	}
}
